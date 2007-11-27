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
include("global.php");
InitPage("login");

require_once(BABEL_BASE_DIR . "classes/system/user.class.php");
require_once(BABEL_BASE_DIR . "classes/system/session.class.php");

$pageTitle 		= "Babel Login";
$pageKeywords 	= "translation,language,nlpack,pack,eclipse,babel";

$USERNAME 	= $App->getHTTPParameter("username", "POST");
$PASSWORD 	= $App->getHTTPParameter("password", "POST");
$REMEMBER 	= $App->getHTTPParameter("remember", "POST");
$SUBMIT 	= $App->getHTTPParameter("submit", "POST");

if($SUBMIT == "Login") {
	if($USERNAME != "" && $PASSWORD != ""){
		$User = new User();
		if(!$User->load($USERNAME, $PASSWORD)) {
			$GLOBALS['g_ERRSTRS'][0] = "Authentication failed.  Please verify your username and/or password are correct.";
		}
		else {
			# create session
			$Session = new Session();
			$Session->create($User->userid, $REMEMBER);
			SetSessionVar('User', $User);
			$Crap = GetSessionVar('User');
			echo "User class: ". get_class($Crap); exit;
			exitTo(".");
		}
	}
	else {
		$GLOBALS['g_ERRSTRS'][0] = "Your username and password must not be empty.";
	}
}

# TODO: finish the intro text


include("head.php");

include("content/en_login.php");

include("foot.php");  
?>