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
require_once("../aptana.inc.php");
error_reporting(E_ALL);

require_once(BABEL_BASE_DIR."code/register.code.php");

// ------...------...------...------...------...------...------...------...------...------...------
include(BABEL_BASE_DIR."head.php");


$users_first_name = stripslashes($users_first_name);
$users_last_name = stripslashes($users_last_name);

echo <<< toTheEnd

<center>
<form method="post">
<div id="title">Create Your Aptana Global Account - It's Free!</div>

<table cellspacing=4 cellpadding=0>

<tr>
  <td id="formLbl">first name:</td>
  <td><input type="edit" name='{$users_first_name_name}' id='{$users_first_name_name}' value="{$users_first_name}"/></td>
  <td id="formErr" colspan=2>{$errStr3}&nbsp;</td>
<tr>
  <td id="formLbl">last name:</td>
  <td><input type="edit" name='{$users_last_name_name}' id='{$users_last_name_name}' value="{$users_last_name}"/></td>
  <td id="formErr" colspan=2>{$errStr4}&nbsp;</td>
</tr>

<tr>
  <td id="formLbl">username:</td>
  <td id="formEdt"><input type="text" name='{$users_username_name}' id='{$users_username_name}' value="{$users_username}"/></td>
  <td id="formErr">{$errStr0}&nbsp;</td>
</tr>
<tr><td></td><td id="formNote" colspan=2>Alphanumeric only. Must be between 6 and 16 characters.</td></tr>

<tr>
  <td id="formLbl">email:</td>
  <td><input type="edit" name='{$users_email_name}' id='{$users_email_name}'value="{$users_email}"/></td>
  <td id="formErr">{$errStr1}&nbsp;</td>
</tr>
<tr><td></td><td id="formNote" colspan=2>Required for account confirmation. We protect your personal<br />information. See our <a href="/privacy.php">privacy policy</a>.</td></tr>


<tr>
  <td id="formLbl">password:</td>
  <td><input type="password" name='password_plain' id='password_plain' value="{$password_plain}"/></td>
  <td id="formErr" colspan=2>{$errStr2}&nbsp;</td>
<tr>
  <td id="formLbl">verify:</td>
  <td><input type="password" name="password_confirm" id="password_confirm" value="{$password_confirm}"/></td>
</tr>
<tr><td></td><td id="formNote" colspan=2>At least 6 characters long..</td></tr>



<tr>
  <td id="formLbl">primary&nbsp;translation&nbsp;language:</td>
  <td>
<select id="{$users_primary_language_id_name}" name="{$users_primary_language_id_name}">
$langCombo
</select>
  </td>
  <td id="formErr" colspan=2>{$errStr5}&nbsp;</td>
<tr>
  <td id="formLbl">hours per week:</td>
  <td>
<select id="{$users_hours_per_week_name}" name="{$users_hours_per_week_name}">
$hoursCombo
</select>  
  </td>
  <td id="formErr" colspan=2>&nbsp;</td>
</tr>



<tr><td></td><td><br><input type="submit" name="postIT" value="create account"/><br /><br /></td></tr>

</table>
</form>
</center>
toTheEnd;
include(BABEL_BASE_DIR."foot.php");

// ------...------...------...------...------...------...------...------...------...------...------
?>