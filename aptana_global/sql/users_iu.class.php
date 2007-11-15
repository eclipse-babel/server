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

class users_iu extends users_ix {
  public $errStrs;


function PostDATA($post=NULL) {
  $dat = cXSQL::formBuildData($post);
  for ($i=0;$i<max(6,count($this->errStrs));$i++)
    $dat["errStr$i"] = (isset($this->errStrs[$i])?$this->errStrs[$i]:"&nbsp");
  return $dat;
}


function formRegValidate($_POST,$pass1,$pass2) {
  $this->formGatherData($_POST);
  // username
  if (!strlen($this->_username))
    $this->errStrs[0] = "required";
  else if (strlen($this->_username) < 6)
    $this->errStrs[0] = "must be at least 6 chars long";
  else if (strlen($this->_username) > 16)
    $this->errStrs[0] = "must be less than 16 chars long";
  else if (eregi('[^a-z0-9]',$this->_username,$regs))
    $this->errStrs[0] = "invalid character(s) - [" . (($regs[0]==' ')?"space":$regs[0]) . "]";
  else if ($rec = $this->sqlRec("SELECT * FROM {SELF} WHERE username='$this->_username'"))
    $this->errStrs[0] = "&lsquo;$this->_username&rsquo; is unavailable";

  // email
  $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
  if (!strlen($this->_email))
    $this->errStrs[1] = "required";
  else if (!eregi($regexp,$this->_email))
    $this->errStrs[1] = "invalid email address";

  // password
  if (!strlen($pass1))
    $this->errStrs[2] = "required";
  else if (strlen($pass1) < 6)
    $this->errStrs[2] = "must be at least 6 chars long";
  else if ($pass2 != $pass2)
    $this->errStrs[2] = "passwords do not match";

  if (!strlen($this->_first_name))
    $this->errStrs[3] = "required";
  if (!strlen($this->_last_name))
    $this->errStrs[4] = "required";

  if ($this->_primary_language_id <= 0)
    $this->errStrs[5] = "required";


  if (count($this->errStrs))
    return false;
  return true;
}


function findUser($str,$password) {
  $name = $this->addSET("","username",$str);
  $mail = $this->addSET("","email",$str);
  //$pass = (!empty($password)?"AND (PASSWORD('$password')=password OR password='$password')":"");
  // using a different DB that hashes the password differently
  $this->sqlLoad("($name OR $mail)");
  if ($this->sql_Cnt != 1) {
    $this->_id = 0;
  }
  
  # we don't use the username, so replace it with the email address
  # same algorithm here as the wiki
  $this->_username = str_replace("@", ".", $this->_email);

  # Typical Bugzilla algorithm
  if ($this->_password_hash != $password) {
    if (!(crypt($password, $this->_password_hash) == $this->_password_hash)) {
      $this->_id = 0;
    }
  }
  
  debugLog("Found user: " . $this->_username . ":" . "********");
  return $this->_id;
}
 

}
?>