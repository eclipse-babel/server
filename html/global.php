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
 *    Scott Reynen scott at randomchaos com - toescapedunicode
*******************************************************************************/
if(!defined('BABEL_BASE_DIR')){
	define('BABEL_BASE_DIR', "../");
}
if(!defined('USE_PHOENIX')) {
	define('USE_PHOENIX', 		true);
}
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
	$MenuItemList = array();
	$MenuItemList[0] = new MenuItem("Home", "./", "_self", 0);
	$MenuItemList[1] = new MenuItem("For committers", "map_files.php", "_self", 0);
	$MenuItemList[2] = new MenuItem("Recent Translations", "recent.php", "_self", 0);
	$MenuItemList[3] = new MenuItem("About Babel", "http://www.eclipse.org/babel", "_self", 0);
	$Menu->setMenuItemList($MenuItemList);
	
	# set Phoenix defaults to prevent errors. These can be overridden on the page.
	$pageTitle		= "";
	$pageAuthor		= "";
	$pageKeywords	= "";
}
$GLOBALS['g_LOADTIME'] = microtime();
require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
require(BABEL_BASE_DIR . "classes/system/event_log.class.php");
require_once(BABEL_BASE_DIR . "classes/system/user.class.php");

# get context
if (!($ini = @parse_ini_file(BABEL_BASE_DIR . 'classes/base.conf'))) {
	errorLog("Failed to find/read database conf file - aborting.");
	exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
}

$context = "";
if(isset($ini['context'])) 
	$context = $ini['context'];

if($context == "") {
	$context = "staging";
}

$image_root = "";
# get the image root
if(isset($ini['image_root'])) 
        $image_root = $ini['image_root'];

$genie_id = "";
#get the genie id
if(isset($ini['genie_id'])) 
        $genie_id = $ini['genie_id'];

$syncup_id = "";
#get the syncup id
if(isset($ini['syncup_id'])) 
        $syncup_id = $ini['syncup_id'];
        
global $context;

session_name(COOKIE_SESSION);
session_start();
extract($_SESSION);


function InitPage($login) {
  $page = $login;
  $lastPage = GetSessionVar('s_pageName');
  $User = GetSessionVar('User');
  
  if (empty($GLOBALS['page']))
	  $GLOBALS['page'] = '';
	
  if((strpos($_SERVER['REQUEST_URI'], "login.php") == FALSE) &&
	 (strpos($_SERVER['REQUEST_URI'], "callback") == FALSE)) {
	  	SetSessionVar('s_pageLast', $_SERVER['REQUEST_URI']);
  }
  
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

/**
 * Converts string to escaped unicode format
 * Based on work by Scott Reynen - CQ 2498
 *
 * @param string $str
 * @return string
 * @since 2008-07-18
 */
if(!function_exists('toescapedunicode')) {
function toescapedunicode($str) {
	$unicode = array();       
	$values = array();
	$lookingFor = 1;
       
	for ($i = 0; $i < strlen( $str ); $i++ ) {
		$thisValue = ord( $str[ $i ] );
		if ( $thisValue < 128)
			$unicode[] = $str[ $i ];
		else {
			if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;               
				$values[] = $thisValue;               
			if ( count( $values ) == $lookingFor ) {
				$number = ( $lookingFor == 3 ) ?
					( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
					( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
				$number = dechex($number);
				
				if(strlen($number) == 3) {
					$unicode[] = "\u0" . $number;
				}
				elseif(strlen($number) == 2) {
					$unicode[] = "\u00" . $number;
				}
				else {
					$unicode[] = "\u" . $number;
				}
				$values = array();
				$lookingFor = 1;
			}
		}
	}
	return implode("",$unicode);
}
}

/**
* Returns the genie user to be used for headless applications.
* The user is found by looking for genie_id in the base.conf file.
*/
function getGenieUser() {
  global $genie_id;
  $User = new User();
  $User->loadFromID($genie_id); 
  return $User;
}
/**
* Returns the syncup user to be used for headless applications.
* The user is found by looking for syncup_id in the base.conf file.
*/
function getSyncupUser() {
  global $syncup_id;
  $User = new User();
  $User->loadFromID($syncup_id); 
  return $User;
}

/**
* Returns the folder in which the images may be found.
* The folder may very well be an other server url.
*/
function imageRoot() {
	global $image_root;
	return $image_root;
}

?>