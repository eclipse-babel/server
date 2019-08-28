<?php
/*******************************************************************************
 * Copyright (c) 2007-2019 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation 
 *    Matthew Mazaika <mmazaik  us.ibm.com> - bug 242011
 *    Paul Pazderski - bug 463293: load user info from Eclipse account api
*******************************************************************************/

require_once(dirname(__FILE__) . "/backend_functions.php");

class User {
  public $errStrs;
  
  public $userid              = 0;
  public $username            = '';
  public $first_name          = '';
  public $last_name           = '';
  public $email               = '';
  public $primary_language_id = 0;
  public $hours_per_week      = 0;
  public $is_committer		  = 0;
  public $updated_on          = '';
  public $updated_at          = '';
  public $created_on          = '';
  public $created_at          = '';

	function load($email, $password) {
		if($email != "" && $password != "") {
		    if (preg_match('^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z.]{2,5}$', $email)) {
				global $addon;
				$addon->callHook('user_authentication', array(&$this, $email, $password));
			}
		}
		
		if($this->userid > 0) {
			$Event = new EventLog("users", "userid", $this->userid, "__auth_success");
			$Event->add();
		}
		else {
			$Event = new EventLog("users", "userid", $_SERVER['REMOTE_ADDR'] . ":" . $email, "__auth_failure");
			$Event->add();
		}
		return $this->userid;
	}
	
	// Update user information in database by requesting account api with authorized oauth token. Return user id.
	function updateUser($access_token) {
		$this->userid = $this->doUpdateUser($access_token);
		if ($this->userid > 0) {
			$Event = new EventLog("users", "userid", $this->userid, "__auth_success");
			$Event->add();
		} else {
			$Event = new EventLog("users", "userid", $_SERVER['REMOTE_ADDR'], "__auth_failure");
			$Event->add();
		}
		return $this->userid;
	}
	
	function doUpdateUser($access_token) {
		$eclipse_profile_url = "https://accounts.eclipse.org/oauth2/UserInfo";

		$options = array(
			'http' => array(
				'header' => array(
					"Authorization: Bearer $access_token"
				)
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($eclipse_profile_url, false, $context);
		if ($result === false) {
			$GLOBALS['g_ERRSTRS'][1] = error_get_last()["message"];
			return 0;
		}

		$profile = json_decode($result, true, 10);
		if ($profile === null) {
			$GLOBALS['g_ERRSTRS'][1] = error_get_last()["message"];
			return 0;
		}

		$_sub = $profile["sub"];
		$_username = $profile["name"];
		$_first_name = $profile["given_name"];
		$_last_name = $profile["family_name"];
		$_is_committer = $profile["is_committer"] ? 1 : 0;

		// check if user already exist or logged in for the first time
		global $dbh;
		$sql = "SELECT userid FROM users WHERE sub = '" . sqlSanitize($_sub, $dbh) . "'";
		$result = mysqli_query($dbh, $sql);
		if ($result === false) {
			$GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
			return 0;
		}
		$row = mysqli_fetch_array($result);
		$_userid = $row !== null ? $row[0] : 0;
		$first_login = ! $_userid;

		if ($first_login) {
			// try to match existing username to OpenID subject
			$sql = "UPDATE users SET sub = '" . sqlSanitize($_sub, $dbh) . "' WHERE username = '" . sqlSanitize($_username, $dbh) . "' AND userid > 3 LIMIT 1"; 
			$result = mysqli_query($dbh, $sql);
			if ($result === false) {
				$GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
				return 0;
			}
			if (mysqli_affected_rows($dbh)) {
				$sql = "SELECT userid FROM users WHERE sub = '" . sqlSanitize($_sub, $dbh) . "'";
				$result = mysqli_query($dbh, $sql);
				if ($result === false) {
					$GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
					return 0;
				}
				$row = mysqli_fetch_array($result);
				$_userid = $row !== null ? $row[0] : 0;
				$first_login = ! $_userid;
			}
		}

		$sql = ($first_login ? "INSERT INTO " : "UPDATE ");
		$sql .= "users SET ";
		$sql .= "username = '" . sqlSanitize($_username, $dbh) . "', ";
		$sql .= "first_name = '" . sqlSanitize($_first_name, $dbh) . "', ";
		$sql .= "last_name = '" . sqlSanitize($_last_name, $dbh) . "', ";
		$sql .= "is_committer = $_is_committer, ";
		$sql .= "updated_on = NOW(), ";
		$sql .= "updated_at = NOW()";
		if ($first_login) {
			$sql .= ", created_on = NOW(), ";
			$sql .= "created_at = NOW(), ";
			$sql .= "sub = '" . sqlSanitize($_sub, $dbh) . "'";
		} else {
			$sql .= " WHERE sub = '" . sqlSanitize($_sub, $dbh) . "'";
		}
		$result = mysqli_query($dbh, $sql);
		if ($result === false) {
			$GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
			return 0;
		}
		return $first_login ? mysqli_insert_id($dbh) : $_userid;
	}
	
	function loadFromID($_userid) {
		$rValue = false;
		if($_userid != "") {
			global $dbh;
			
			$_userid	= sqlSanitize($_userid, $dbh);

			$sql = "SELECT *
				FROM 
					users 
				WHERE userid = $_userid";
			$result = mysqli_query($dbh, $sql);
			if($result && mysqli_num_rows($result) > 0) {
				$rValue = true;
				$myrow = mysqli_fetch_assoc($result);
				
				$this->userid              = $myrow['userid'];
				$this->username            = $myrow['username'];
				$this->first_name          = $myrow['first_name'];
				$this->last_name           = $myrow['last_name'];
				$this->email               = $myrow['email'];
				$this->primary_language_id = $myrow['primary_language_id'];
				$this->is_committer			= $myrow['is_committer'];
				$this->hours_per_week      = $myrow['hours_per_week'];
				$this->updated_on          = $myrow['updated_on'];
				$this->updated_at          = $myrow['updated_at'];
				$this->created_on          = $myrow['created_on'];
				$this->created_at			= $myrow['created_at'];
			}
			else {
				$GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
			}
		}
		return $rValue;
	}
}
?>