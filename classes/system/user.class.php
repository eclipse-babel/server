<?php
/*******************************************************************************
 * Copyright (c) 2007-2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation 
 *    Matthew Mazaika <mmazaik  us.ibm.com> - bug 242011
*******************************************************************************/

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
			if (eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z.]{2,5}$', $email)) {
				
				global $App, $dbh;
				
				$email 		= $App->sqlSanitize($email, $dbh);
				$password 	= $App->sqlSanitize($password, $dbh);
	
				// since MySQL ENCRYPT is not supported on windows we have to move encryption
				// from the database layer out to the application layer
				//  https://bugs.eclipse.org/bugs/show_bug.cgi?id=242011
				 
				$hash_query = "SELECT users.password_hash FROM users WHERE email = '$email'";
				$hash_result = mysql_query($hash_query, $dbh);
				
				if ($hash_result && mysql_num_rows($hash_result) > 0) {
					
					$hash_row = mysql_fetch_assoc($hash_result);
					$hash = $hash_row['password_hash'];
					
					$sql = "SELECT *
						FROM 
							users 
						WHERE email = '$email' 
							AND password_hash = '" . crypt($password, $hash) . "'";
							
					$result = mysql_query($sql, $dbh);
					if($result && mysql_num_rows($result) > 0) {
						$rValue = true;
						$myrow = mysql_fetch_assoc($result);
						
						$this->userid				= $myrow['userid'];
						$this->username				= $myrow['username'];
						$this->first_name			= $myrow['first_name'];
						$this->last_name			= $myrow['last_name'];
						$this->email				= $myrow['email'];
						$this->primary_language_id	= $myrow['primary_language_id'];
						$this->is_committer			= $myrow['is_committer'];
						$this->hours_per_week		= $myrow['hours_per_week'];
						$this->updated_on			= $myrow['updated_on'];
						$this->updated_at			= $myrow['updated_at'];
						$this->created_on			= $myrow['created_on'];
						$this->created_at			= $myrow['created_at'];
	
					} else {
						// password failed
						$GLOBALS['g_ERRSTRS'][1] = mysql_error();
					}
				} else {
					// username failed
					$GLOBALS['g_ERRSTRS'][1] = mysql_error();
				}			
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
	
	function loadFromID($_userid) {
		$rValue = false;
		if($_userid != "") {
			global $App, $dbh;
			
			$_userid	= $App->sqlSanitize($_userid, $dbh);

			$sql = "SELECT *
				FROM 
					users 
				WHERE userid = $_userid";
			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$rValue = true;
				$myrow = mysql_fetch_assoc($result);
				
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
				$GLOBALS['g_ERRSTRS'][1] = mysql_error();
			}
		}
		return $rValue;
	}
}
?>