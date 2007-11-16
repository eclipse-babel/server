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
 * 	  Eclipse Foundation
*******************************************************************************/
error_reporting(E_ALL);
require_once(BABEL_BASE_DIR."aptana.inc.php");


extract(LoadVars());

function LoadVars() {

  InitPage("login");
  $dat = array();

  $post_username = (isset($_POST['username'])?$_POST['username']:"");
  $post_password = (isset($_POST['password'])?$_POST['password']:"");
  $post_remember = (isset($_POST['remember'])?$_POST['remember']:"");
  
  if (!empty($_POST['postIT'])) {
    loginUser($post_username,$post_password,$post_remember);
  }

  $dat['post_username'] = $post_username;
  $dat['post_password'] = $post_password;
  $dat['post_remember'] = ($post_remember?"checked":"");
  return $dat;
}

function loginUser($username,$password,$remember) {
  unset($_SESSION['s_userAcct']);
  unset($_SESSION['s_userName']);
  unset($_SESSION['s_userType']);

  $errStrs = $GLOBALS['g_ERRSTRS'];
  if (empty($username))
    $errStrs[1] = "&nbsp;required";
  if (empty($password))
    $errStrs[2] = "&nbsp;required";

  if (!$errStrs[1] && !$errStrs[2]) {
    $errStrs[0] = "Invalid username/password";
    $user = new users_iu(0);
    if ($user->findUser($username,$password)) {
    
      //switch ($user->_status) {
      //  case 0: // not yet confirmed
      //    $errStrs[0] = "your account has not yet been confirmed";
      //    break;
      //  case 1:
          if ($remember) {
          	$session = new sessions_iu(0);
          	$session->createSession($user->_id);

            $cookieName  = COOKIE_REMEMBER;
            $cookieValue = $session->encode_remember();
            setcookie($cookieName,$cookieValue,time()+3600*24*365,"/");
          }
          SetSessionVar("s_userAcct" ,"$user->_id");
          SetSessionVar("s_userName","$user->_username");
          //SetSessionVar("s_userType","$user->_type");
          $errStrs[0] = "";
          $lastURL    = GetSessionVar('s_pageLast');
          exitTo("intro.php");
      //    break;
      //  default:
      //    $errStrs[0] = "unknown status";
      //}
    }		
  }
  $GLOBALS['g_ERRSTRS'] = $errStrs;
}



?>
