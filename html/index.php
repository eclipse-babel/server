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

echo $User->userid;
echo $User->first_name;

?>

<div id="contentArea">
	<h1 id="page-message">Welcome to the Babel Project</h1>
	<div id="language-area">lang</div>
	<div id="project-area">proj</div>
	<div id="string-area">strings</div>
	<div id="translation-area">translation</div>
</div>

<?php
//$_SESSION['language'] = "";

if(!$_SESSION['language']){
	//NO LANGUAGE SELECT FOR EDITING
	?><script>getAjaxLanguages();</script><?php
}else{
	//SHOW CURRENT LANGUAGE
	?><script>showCurrentLanguage("<?=getLanguagebyID($_SESSION['language']);?>");</script><?php
	
	//LIST PROJECTS TO EDIT
	if(!$_SESSION['project']){
		?><script>getAjaxProjects();</script><?php
	}else{
		?><script>showCurrentProject("<?=$_SESSION['project'];?>");</script><?php
	}
}
?>

<?php 
include("foot.php");
?>