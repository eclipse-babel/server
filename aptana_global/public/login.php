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

$pageTitle = "Babel Login";
$pageKeywords = "";

require_once("../aptana.inc.php");

require_once(BABEL_BASE_DIR.'code/login.code.php');
require_once(BABEL_BASE_DIR.'head.php');
echo <<< toTheEnd
<h1>$pageTitle</h1>
<form method="post">

<table cellspacing=4 cellpadding=0 width=500px; border=0>
<tr><td></td><td id="formErr" colspan=2>{$GLOBALS['g_ERRSTRS'][0]}&nbsp;</td></tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">User ID:</td><td style='text-align:left;'><input type="text" name="username" value="$post_username"/></td>
  <td id="formErr" style='width:100px;'>{$GLOBALS['g_ERRSTRS'][1]}&nbsp;</td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Password:</td><td style='text-align:left;'><input type="password" name="password" value="$post_password"/></td>
  <td id="formErr" style='width:100px;'>{$GLOBALS['g_ERRSTRS'][2]}&nbsp;</td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="checkbox" name="remember" value="1" $post_remember />remember me </td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="submit" name="postIT" value="Login" style="font-size:14px;" /></td></tr>
</table>
</form>
<p />

toTheEnd;
require_once(BABEL_BASE_DIR.'foot.php');    
?>