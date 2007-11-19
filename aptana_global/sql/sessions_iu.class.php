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

class sessions_iu extends sessions_ix {
	
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
	
	function create($_userid) {
		$this->_userid 	= $_userid;
		$this->_gid 	= guidNbr();
		$this->_subnet 	= $this->getSubnet();
		$this->_updated_at = "NOW()";
		
		$this->selfPost();
	}
	
	function maintenance() {
		# Delete sessions older than 14 days
		$this->sqlCmd("DELETE FROM {SELF} WHERE updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)");
	}
		
	function getSubnet() {
		# return class-c subnet
		return substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], ".")) . ".0";
	}
	
    function encode_remember() {
      $code = ($this->_userid+111) . ":" . $this->_gid;
      return $code;
    }
    
    function decode_remember($remember) {
      list($nbr,$gid) = split(":",$remember);
      $nbr  = $nbr-111;
      return array($nbr,$gid);
    }
}
?>