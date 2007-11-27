<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

class Session {
	public $_id         = '';
	public $_userid     = '';
	public $_gid		  = '';
	public $_subnet     = '';
	public $_updated_at = '';

	function validate() {
	  $cookie = (isset($_COOKIE[COOKIE_REMEMBER])?$_COOKIE[COOKIE_REMEMBER]:"");
      $rValue = 1;
	  
      if (strpos($cookie,":")) {
        // Check for remember cookie and get user info if set
        list($nbr,$gid) = $this->decode_remember($cookie);
        if ( (!$this->sqlLoad("gid", $gid)) 
        	|| $gid != $this->_gid
        	|| $this->getSubnet() != $this->_subnet) {
        	# Failed - no such session, or session no match.  Need to relogin
        	setcookie(COOKIE_REMEMBER, "", -36000, "/");
        	$rValue = 0;
        }
        else {
        	# Update the session updated_at
        	$this->sqlTouch("updated_at");
        	$this->maintenance();
        }
        SetSessionVar('s_userAcct', $this->_userid);
        return $rValue;
      }
	}

	function destroy() {
	  $cookie = (isset($_COOKIE[COOKIE_REMEMBER])?$_COOKIE[COOKIE_REMEMBER]:"");
      $rValue = 1;
	  
      if (strpos($cookie,":")) {
        // Check for remember cookie and get user info if set
        list($nbr,$gid) = $this->decode_remember($cookie);
        if($nbr) {
        	# TODO: untaint
        	$sql = "DELETE FROM sessions WHERE userid = " . $nbr;
        	sqlQuery($sql);
        	unset($_SESSION['s_userAcct']);
  			unset($_SESSION['s_userName']);
  			unset($_SESSION['s_userType']);
        }
      }
	}
	
	function create($_userid, $_remember) {
		global $dbh, $App;
		$this->_userid 	= $App->sqlSanitize($_userid, $dbh);
		$this->_gid 	= $this->guidNbr();
		$this->_subnet 	= $this->getSubnet();
		$this->_updated_at = $App->getCURDATE();

		$sql = "INSERT INTO sessions (
				id,
				userid,
				gid,
				subnet,
				updated_at) VALUES (
				NULL,
				" . $this->_userid . ",
				" . $App->returnQuotedString($this->_gid) . ",
				" . $App->returnQuotedString($this->_subnet) . ",
				NOW())";
		mysql_query($sql, $dbh);
		$cookieTime = 0;
		if($_remember) {
			$cookieTime = time()+3600*24*365;
		}
		setcookie(COOKIE_REMEMBER, $this->_gid, $cookieTime, "/");
		
		$this->maintenance();
	}
	
	function maintenance() {
		# Delete sessions older than 14 days
		global $dbh, $App;
		mysql_query("DELETE FROM sessions WHERE updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)", $dbh);
	}
		
	function getSubnet() {
		# return class-c subnet
		return substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], ".")) . ".0";
	}
	
	function guidNbr() {
  		return md5(uniqid(rand(),true));
	}
}
?>