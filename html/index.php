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
InitPage("login");

$pageTitle 		= "Babel Project";
$pageKeywords 	= "translation,language,nlpack,pack,eclipse,babel";

include("head.php");

//echo $User->userid;
//echo $User->first_name;

//$_SESSION['language'] = "";
//$_SESSION['project'] = "";
//$_SESSION['version'] = "";
//session_destroy();
//unset($_SESSION);
//exit();
	
//print "<pre>";
//print_r($_SESSION);
//print "</pre>";
?>

<h1 id="page-message">Welcome to the Babel Project</h1>
<div id="contentArea">

	<h2>Languages / Projects / Versions</h2>
	<div id="language" class="side-component">
		<h4 id="language-selection">Langues</h4>
		<ul id="language-area"class="scrollable-area"></ul>
	</div>

	<div id="project" class="side-component">
		<h4 id="project-selection">Projects</h4>
		<ul id="project-area" class="scrollable-area"></ul>
	</div>
	
	<div id="project" class="side-component">
		<h4 id="version-selection">Versions</h4>
		<ul id="version-area" class="scrollable-area"></ul>
	</div>
	
	<div class="clearing"></div>
	
	
<!--  	
<script type="text/javascript"> 
	var myTabs = new YAHOO.widget.TabView("string-area"); 
</script>  
-->


	
	<div id="string-area" class="yui-navset full-component">
	<h2>Translatable Strings</h2>
<!--
	    <ul class="yui-nav"> 
        <li class="selected"><a href="#tab1"><em>Untranslated</em></a></li> 
	        <li><a href="#tab2"><em>Flagged Incorrect</em></a></li> 
	        <li><a href="#tab3"><em>Awaiting Rating</em></a></li> 
	    </ul>             
  	  	<div class="yui-content" style="clear: both;"> 
			<div id="not-translated">
			</div>
			
			<ul id="flagged-incorrect">
			</ul>
			
			<ul id="awaiting-ratings">
			</ul>
		</div>
	    
-->	    
  	  	<div id="projecs-strings-area" class="yui-content"> </div>
		
		<div id="not-translated"></div>
	</div>
	
	
	<div id="translation-area" class="full-component">
	   <h2>String Translation</h2>
	   <div id="translation-form-container"></div>
	</div>
	
	<div class="clearing"></div>
	
</div>

<?php
	if(!$_SESSION['language']){
		//NO LANGUAGE SELECT FOR EDITING
		?><script>YAHOO.languageManager.getAjaxLanguages();</script><?php
	}else{
		//SHOW CURRENT LANGUAGE
		?><script>YAHOO.languageManager.getAjaxLanguages("<?=$_SESSION['language'];?>");</script><?php
		
		//LIST PROJECTS TO EDIT
		if(!$_SESSION['project']){
			?><script>YAHOO.projectManager.getAjaxProject("<?=$_SESSION['project'];?>");</script><?php
		}else{
			?><script>YAHOO.projectManager.getAjaxProject("<?=$_SESSION['project'];?>");</script><?php
			if(!$_SESSION['version']){
				?><script>YAHOO.versionManager.getAjaxVersions("<?=$_SESSION['version'];?>");</script><?php
			}else{
				?>
				<script>YAHOO.versionManager.getAjaxVersions("<?=$_SESSION['version'];?>");</script>
				<script>YAHOO.projectStringsManager.getAjaxProjectStrings();</script>
				<?php
			}
		}
	}	
	include("foot.php");
?>