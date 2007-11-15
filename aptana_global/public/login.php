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
$pageKeywords = "translation,language,nlpack,pack,eclipse,babel";

require_once("../aptana.inc.php");
require_once(BABEL_BASE_DIR . 'code/login.code.php');
require_once(BABEL_BASE_DIR . 'head.php');

# TODO: finish the intro text

echo <<< toTheEnd
<div id="maincontent">
<div id="midcolumn">

<h1>$pageTitle</h1>
<p>Welcome to Babel - the Eclipse translation tool.  TODO: Explain what this is and how to use.  Link to Privacy/Terms?</p>

<p>Use your Bugzilla e-mail address and 
password to login. If you don't have a Bugzilla account, you can <a href="https://bugs.eclipse.org/bugs/createaccount.cgi">create one here</a></p>
<form method="post">

<table cellspacing=4 cellpadding=0 border=0>
<tr><td></td><td id="formErr" colspan=2 style="color:red;">{$GLOBALS['g_ERRSTRS'][0]}&nbsp;</td></tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Email:</td><td style='text-align:left;'><input type="text" name="username" value="$post_username" size="42" maxlength="255" /></td>
  <td id="formErr" style='width:100px; color:red;'>{$GLOBALS['g_ERRSTRS'][1]}&nbsp;</td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Password:</td><td style='text-align:left;'><input type="password" name="password" value="$post_password" size="42" maxlength="255"/> <a href="https://bugs.eclipse.org/bugs/index.cgi?GoAheadAndLogIn=1#forgot">Forgot my password</a></td>
  <td id="formErr" style='width:100px; color:red;'>{$GLOBALS['g_ERRSTRS'][2]}&nbsp;</td>
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
</div><div id="rightcolumn">

<div class="sideitem">
	<h6>Related Linkx</h6>
	<ul>
		<li><a href="//www.eclipse.org/babel/">Babel project home</a></li>
	</ul>
</div>
</div>
</div>
toTheEnd;
require_once(BABEL_BASE_DIR.'foot.php');    
?>