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


// ------...------...------...------...------...------...------...------...------...------...------
require_once(BABEL_BASE_DIR."aptana.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------

extract(LoadVars());

// ------...------...------...------...------...------...------...------...------...------...------

function LoadVars() {
  InitPage("confirm");
  $errStrs = $GLOBALS['g_ERRSTRS'];
  
  $dat = array();
  $dat['key']     = (isset($_POST['key'])?$_POST['key']:(isset($_GET['key'])?$_GET['key']:""));
  $dat['codeStr'] = (isset($_POST['code'])?$_POST['code']:"");

  if (!empty($dat['key'])) { //}isset($_POST['postIT'])) {
    //if (!strlen($dat['codeStr']))
    //  $errStrs[1] .= "&nbsp;required";
    //else if (!validateCode(GetSessionVar('s_code'),$dat['codeStr'])) {
    //  $errStrs[1]    .= "please enter in the string below";
    //  $dat['codeStr'] = ClearSessionVar('s_code');
    //}
    
    if (!strlen($dat['key']))
      $errStrs[0] .= "&nbsp;required";
    else {
      $user = new users_iu(0);
			$rec = $user->sqlRec("SELECT * FROM {SELF} WHERE code='{$dat['key']}'");
      if (empty($rec)) 
        $errStrs[0] .= "&nbsp;invalid code";
      else if (strlen(!$errStrs[1])) {
        if (!$user->sqlLoad($rec->id))
          exitTo("error.pp","e_code","1012");
        $user->_type   = 1;
        $user->_status = 1;
				$user->_code   = '';
        $user->selfPost();
        exitTo("registration_done.php");
      }
    }
  }
  $GLOBALS['g_ERRSTRS'] = $errStrs;
  $dat['codePng'] = '';//getRegCodePict();
  return $dat;
}

// ------...------...------...------...------...------...------...------...------...------...------
?>