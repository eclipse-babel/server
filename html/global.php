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
define('BABEL_BASE_DIR', 	"../");
define('USE_PHOENIX', 		true);
define("COOKIE_REMEMBER",	"cBABEL");
define("COOKIE_SESSION" ,	"sBABEL");


# Load up Phoenix classes
$App;
if(USE_PHOENIX) {
	require_once('eclipse.org-common/system/app.class.php');
	require_once("eclipse.org-common/system/nav.class.php");
	require_once("eclipse.org-common/system/menu.class.php");
	$App = new App(); 	
	$Nav	= new Nav();	
	$Menu 	= new Menu();
}
$GLOBALS['g_LOADTIME'] = microtime();
// require("utils.inc.php");
session_name(COOKIE_SESSION);
session_start();
extract($_SESSION);


function InitPage($page) {
  $lastPage = GetSessionVar('s_pageName');
  $userName = GetSessionVar('s_userName');
  
  if (empty($GLOBALS['page']))
	  $GLOBALS['page'] = '';
		
  if (($lastPage != $_SERVER['PHP_SELF']) AND ($lastPage != "login"))
    SetSessionVar('s_pageLast',$lastPage);
  SetSessionVar('s_pageName',$GLOBALS['page']);
  
  sqlOpen(NULL);
  if (!$userName && isset($_COOKIE[COOKIE_REMEMBER])) {
  	# Try to fetch username from session
  	$session = new sessions_iu(0);

  	if(!$session->validate()) {
    	SetSessionVar('s_pageLast',$GLOBALS['page']);
    	exitTo("login.php");
  	}
  	else {
  		$user = new users_iu(0);
  		$user->sqlLoad($session->_userid);
  		# hack! Not every one has a username
  		SetSessionVar("s_userName",  str_replace("@", ".", $user->_email));
  	}
  }
  
  $GLOBALS['g_PHPSELF']  = $GLOBALS['page'];
  $GLOBALS['g_PAGE']     = $page;
  $GLOBALS['g_SITEURL']  = $_SERVER['HTTP_HOST'];
  $GLOBALS['g_SITENAME'] = substr($GLOBALS['g_SITEURL'],0,strlen($GLOBALS['g_SITEURL'])-4);
  $GLOBALS['g_TITLE']    = $GLOBALS['g_SITENAME'];
  $GLOBALS['g_ERRSTRS']  = array("","","","","","","","","","","",);
  // $GLOBALS['g_MAINMENU'] = buildMainMenu($page,$userName);
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


?>