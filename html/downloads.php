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

?>

<h1 id="page-message">Download Language Packs for Eclipse</h1>
<div id="index-page">
	
	 <a href="http://download.eclipse.org/technology/babel/"><img src="http://dev.eclipse.org/large_icons/apps/accessories-text-editor.png"><h2>Language Pack Download Site</h2></a>
      <br style='clear: both;'>
	  <p>Before you <a href="http://download.eclipse.org/technology/babel/">download</a> and use any of the language packs, please read these known problems:</p>
		
	  <ol id="known-issues" style='margin-left: 10px;'>
	  	<li>The language packs contain translated string for all the Eclipse Foundation Projects.
	  	 Unfortunately, you do not have all the Eclipse Foundation Projects installed in your IDE.
	  	 Thus, when you start Eclipse, the error log will accumulate warnings like these:
	  		<ul style='margin-left: 25px;'>
				<li class="stop">Bundle ... was not resolved.
				<li>And below that, a sub-message of:
				<li class="stop">Missing host ...
				<li>e.g., Missing host org.eclipse.wst.core_2.1.0
				<li>There will also be an error:
				<li class="stop">One or more bundles are not resolved because the following root constraints are not resolved:
				<li>with the same sub-messages	  		
	  		</ul>
	  	<li>Not all of the existing Eclipse Foundation Projects are included in Babel yet.  
	  	If you encounter a project that you would like to help translate, <a href="importing.php">tell that project's leaders</a>.
	  	<li>Not all languages are included in Babel yet.
	  	Request an additional language through <a href="https://bugs.eclipse.org/bugs/enter_bug.cgi?bug_file_loc=http%3A%2F%2F&bug_severity=normal&bug_status=NEW&comment=&contenttypeentry=&contenttypemethod=autodetect&contenttypeselection=text%2Fplain&data=&description=&flag_type-1=X&flag_type-2=X&flag_type-4=X&flag_type-6=X&form_name=enter_bug&maketemplate=Remember%20values%20as%20bookmarkable%20template&op_sys=Linux&priority=P3&product=Babel&rep_platform=PC&short_desc=Please%20add%20a%20new%20language%20to%20Babel&version=unspecified">Bugzill</a>.
	  </ol>
	
</div>

<script>YAHOO.languageManager.getAjaxLanguages();</script>

<?php
	include("foot.php");
?>