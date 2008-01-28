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
require("global.php");
InitPage("");

$pageTitle 		= "Babel Project";
$pageKeywords 	= "translation,language,nlpack,pack,eclipse,babel";

include("head.php");

//$_SESSION['language'] = "";
//$_SESSION['project'] = "";
//$_SESSION['version'] = "";

$USERNAME 	= $App->getHTTPParameter("username", "POST");
$PASSWORD 	= $App->getHTTPParameter("password", "POST");
$REMEMBER 	= $App->getHTTPParameter("remember", "POST");
$SUBMIT 	= $App->getHTTPParameter("submit", "POST");

?>

<h1 id="page-message">Welcome to the Babel Project</h1>
<div>
	<h2>Eclipse in your language</h2>
	
	<div style='float: right; border: 0px solid black; background-color: lightblue;'>
		<h3 style='margin: 0px; padding: 0px;'>Login to Babel</h3>
		<form name="frmLogin" method="post" action="login.php">
			<table cellspacing=4 cellpadding=0 border=0>
			<tr>
			  <td id="formLbl">Email:</td><td style='text-align:left;'><input type="text" name="username" value="<?= $USERNAME ?>" size="20" maxlength="255" /></td>
			</tr>
			<tr>
			  <td id="formLbl">Password:</td><td style='text-align:left;'><input type="password" name="password" value="<?= $PASSWORD ?>" size="20" maxlength="255" /></td> 
			</tr>
			<tr>
			  <td></td><td style='text-align:left;'><input type="checkbox" name="remember" value="1" <?= $REMEMBER ?> />remember me </td>
			</tr>
			<tr>
			  <td></td><td style='text-align:left;'><input type="submit" name="submit" value="Login" style="font-size:14px;" /></td></tr>
			</table>
		</form>
	</div>
	
	<p>Eclipse is a part of the global community of open source projects. 
	 It is in everyone's interest to ensure that Eclipse is available and translated in as many locales as possible. 
	 The Babel project is a set of open tools to make the job of globalizing Eclipse projects easier for everyone. 
	 The Babel Project provides a way for people world wide, who are interested, to contribute translations in their language of choice.  
	 </p>
	 <p>
	 This effort will involve a wide range of help from the existing Eclipse community and translator who might have little or no coding skills, but do have a desire to help.
	 The existing Eclipse community can help by making all of the Eclipse project available for translation in Babel and promoting the Babel project to attract translators.
	 Translators can help by adding translations that are missing and improving existing translations.
	 </p>
	 
	<div class="clearing"></div>
	<h2>Who can help?</h2>
	<p>Anyone who knows more than one language can become become a star translator for any of the Eclipse projects.  
	You don't need to be a developer to help out on this project, just a desire to contribute to one of the best open source project, Eclipse.
	</p>

	<h2>How do I get started?</h2>

	<h3>Project Leads</h3>
 	<p>The first step to translating a project is for a project lead to import their project using Babel's MAP input script. This will bring in all the externalized string from you project and make the immeditaly avaible for translation.</p>

	<h3>Committers</h3>
 	<p>If you project isn't imported talk to your project leads and help them get it imported.  Once that is finished you can dive right into translating or help recruit translators in the community.</p>
 	
	<h3>Translators</h3>
 	<p>All you need to contribute translations is an active <a href="https://bugs.eclipse.org/bugs/">Eclipse Bugzilla</a> account and some spare time.  
 	So what are you waiting for?
 	</p>
	
 	
	<div class="clearing"></div>
</div>

<script>YAHOO.languageManager.getAjaxLanguages();</script>

<?php
	include("foot.php");
?>