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

*******************************************************************************/

/**
 * @deprecated
 */
exit;

// ------...------...------...------...------...------...------...------...------...------...------
require_once(BABEL_BASE_DIR."aptana.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------

extract(LoadVars());


$hoursCombo = "";
for ($x=1;$x<=40;$x++) {
  $selected    = (($users_hours_per_week==$x)?"Selected":"");
  $hoursCombo .= "<option value='$x' $selected>$x</option>\n";
}


$langs = new languages_iu(0);
$langs->sqlList("SELECT * FROM {SELF} ORDER BY name");
$langCombo = "<option value='0'>Select Language</option>\n";
while ($langs->sqlNext())
  if ($langs->_id > 1) {
    $selected   = (($users_primary_language_id==$langs->_id)?"Selected":"");
    $langCombo .= "<option value='$langs->_id' $selected>$langs->_name</option>\n";
  }

// ------...------...------...------...------...------...------...------...------...------...------

function LoadVars() {
  InitPage("register");
  $user = new users_iu(0);
  $pass1  = (isset($_POST['password_plain'])?$_POST['password_plain']:"");
  $pass2 = (isset($_POST['password_confirm'])?$_POST['password_confirm']:"");

  if (isset($_POST['postIT'])) {
    if ($user->formRegValidate($_POST,$pass1,$pass2)) {
      addUser($user,$pass1);
      exitTo("registration_done.php?code=1");
    }
  }
  $dat = $user->PostDATA();
  $dat['password_plain']   = $pass1;
  $dat['password_confirm'] = $pass2;

  return $dat;
}

// ------...------...------...------...------...------...------...------...------...------...------

function addUser($user,$pass) {
  //require_once("aptana/email.inc.php");
  //$user->_type     = 0;
  //$user->_status   = 0;
  //$user->_code     = guidNbr();
  //$user->_password = $user->sqlGetPassword($user->_password);
  # $user->_password_salt = hash("crc32", $pass);
  $user->_password_salt = crc32($pass);
  # $user->_password_hash = hash("sha256", $pass . $user->_password_salt);
  $user->_password_hash = sha1($pass . $user->_password_salt);
  $user->_updated_on    = "NOW()";
  $user->_created_on    = "NOW()";
  if (!$user->selfPost())
      exitTo("registration_done.php?code=2");
  //  emailUser($user->_email,"reg_confirm",array("code" => $user->_code,"email" => $user->_email));
  //else 
//    exitTo("/error","e_code","1011");
}

// ------...------...------...------...------...------...------...------...------...------...------
?>