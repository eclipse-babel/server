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
*******************************************************************************/
require("global.php");
InitPage("");

$pageTitle 		= "Babel Project - Eclipse translation";
$pageKeywords 	= "translation,language,nlpack,pack,eclipse,babel,english,french,german,chinese,japanese,spanish,arabic,hebrew,hungarian,polish,italian,russian,dutch,finnish,greek,norwegian,sweedish,turkish";

include("head.php");

?>
<h1 id="page-message">Welcome to the Babel Project</h1>
<div id="index-page">
      <? include("fragments/language_progress.php");?>
	  <? include("fragments/top_translators.php");?>
	  <a href="http://www.eclipse.org/babel/downloads.php"><img src="http://dev.eclipse.org/large_icons/apps/internet-web-browser.png"><h2>Eclipse Speaks your Language</h2></a>
      <br style='clear: both;'>
	  <p><a href="http://www.eclipse.org/babel/downloads.php">Download a language pack</a> in one of many different languages.</p>
         
	  <a href="translate.php"><img src="http://dev.eclipse.org/large_icons/apps/accessories-text-editor.png"><h2>Help Translate Eclipse</h2></a>
      <br style='clear: both;'>
	  <p>Eclipse needs help from everyone in the community to <a href="translate.php">speak in many tongues</a>.</p>
      
	  <a href="map_files.php"><img src="http://dev.eclipse.org/large_icons/apps/system-users.png"><h2>Add an Existing Eclipse Project to Babel</h2></a>
      <br style='clear: both;'>
	  <p>Eclipse committers, find out how simple it is to include any existing Eclipse.org project <a href="map_files.php">in Babel</a>.</p>
	  
</div>

<script>YAHOO.languageManager.getAjaxLanguages();</script>

<?php
	include("foot.php");
?>