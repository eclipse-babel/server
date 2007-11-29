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
define('BABEL_BASE_DIR', "../");
define('USE_PHOENIX', 		true);
define("COOKIE_REMEMBER",	"cBABEL");
define("COOKIE_SESSION" ,	"sBABEL");


# Load up Phoenix classes
global $App;
if(USE_PHOENIX) {
	require_once('eclipse.org-common/system/app.class.php');
	require_once("eclipse.org-common/system/nav.class.php");
	require_once("eclipse.org-common/system/menu.class.php");
	$App 	= new App();
	$Nav	= new Nav();
	$Menu 	= new Menu();
}
$GLOBALS['g_LOADTIME'] = microtime();
require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
require(BABEL_BASE_DIR . "classes/system/event_log.class.php");
require_once(BABEL_BASE_DIR . "classes/system/user.class.php");

session_name(COOKIE_SESSION);
session_start();
extract($_SESSION);


function InitPage($login) {
	$page = $login;
  $lastPage = GetSessionVar('s_pageName');
  $User = GetSessionVar('User');
  
  if (empty($GLOBALS['page']))
	  $GLOBALS['page'] = '';
		
  if (($lastPage != $_SERVER['PHP_SELF']) AND ($lastPage != "login"))
		SetSessionVar('s_pageLast',$lastPage);
		SetSessionVar('s_pageName',$GLOBALS['page']);
  
  $dbc = new DBConnection();
  global $dbh;
  $dbh = $dbc->connect();

  if($login == "login" && !$User) {
  	# Login required, but the User object isn't there.
  
  	if(isset($_COOKIE[COOKIE_REMEMBER])) {
  		# Try to fetch username from session
  		require_once(BABEL_BASE_DIR . "classes/system/session.class.php");
  		$Session = new Session();

  		if(!$Session->validate()) {
    		SetSessionVar('s_pageLast', $GLOBALS['page']);
    		exitTo("login.php");
  		}
  		else {
  			$User = new User();
  			$User->loadFromID($Session->_userid);
  			SetSessionVar("User", $User);
  		}
  	}
  	else {
  		exitTo("login.php");
  	}
  }
  
  $GLOBALS['g_PHPSELF']  = $GLOBALS['page'];
  $GLOBALS['g_PAGE']     = $page;
  $GLOBALS['g_SITEURL']  = $_SERVER['HTTP_HOST'];
  $GLOBALS['g_SITENAME'] = substr($GLOBALS['g_SITEURL'],0,strlen($GLOBALS['g_SITEURL'])-4);
  $GLOBALS['g_TITLE']    = $GLOBALS['g_SITENAME'];
  $GLOBALS['g_ERRSTRS']  = array("","","","","","","","","","","",);
  $GLOBALS['DEBUG']      = "";
}

function errorLog($str) {
	
}

function exitTo() {
  # TODO: sqlClose();
  if (func_num_args() == 1) {
    $url = func_get_arg(0);
    header("Location: $url");
    exit;
  }
  else if (func_num_args() == 2) {
    $url  = func_get_arg(0);
    $arg1 = func_get_arg(1);
    SetSessionVar("errStr",$arg1);
    header("Location: $url");
    exit;
  }
  else if (func_num_args() == 3) {
    $url  = func_get_arg(0);
    $arg1 = func_get_arg(1);
    $arg2 = func_get_arg(2);
    SetSessionVar($arg1,$arg2);
    header("Location: $url");
    exit;
  }
}
function GetSessionVar($varName) {
  if (isset($_SESSION[$varName]))
    return $_SESSION[$varName];
  return 0;
}

function SetSessionVar($varName,$varVal) {
  global $_SESSION;

  $GLOBALS[$varName]  = $varVal;
  $_SESSION[$varName] = $varVal;
  return $varVal;
}

function getLanguagebyID($id){
	global $dbh;
	$query = "select name from languages where language_id = '".addslashes($id)."' limit 1";
	$res = mysql_query($query,$dbh);
	$ret = mysql_fetch_array($res, MYSQL_ASSOC);
	return $ret['name'];
}


?>