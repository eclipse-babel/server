<?php
/*******************************************************************************
 * Copyright (c) 2007-2012 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

global $pageTitle, $pageAuthor, $pageKeywords;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title><?= $pageTitle ?></title><meta name="author" content="<?= $pageAuthor ?>" />
<meta name="keywords" content="<?= $pageKeywords ?>" />
<link rel="stylesheet" type="text/css" href="babel.css" media="screen" />
<link rel="stylesheet" type="text/css" href="babel_print.css" media="print" />
<?php if( isset($extraHtmlHeaders) ) echo $extraHtmlHeaders; ?></head>
<body>
<div id="header">
    <div id="header-graphic" class="babel">
        <a href="/"><img src="images/babel_header.jpg" alt="" /></a><h1>Babel</h1>  
    </div>
    <div id="header-global-holder" class="babel-global">
        <div id="header-global-links">
            <ul>
                <li><a href="http://www.eclipse.org/org/foundation/contact.php" class="first_one">Contact</a></li>
                <li><a href="http://www.eclipse.org/legal/">Legal</a></li>
            </ul>
        </div>
        <div id="header-icons">
            <a href="http://www.eclipse.org"><img src="images/Icon_Org.png" width="28" height="28" alt="eclipse.org" title="Eclipse.org" /></a>
            <a href="http://live.eclipse.org"><img src="images/Icon_Live.png" width="28" height="28" alt="Eclipse Live" title="Eclipse Live" /></a>
            <a href="http://www.eclipseplugincentral.com"><img src="images/Icon_plugin.png" width="28" height="28" alt="Eclipse Plugin Central" title="Eclipse Plugin Central" /></a>
            <a href="http://www.planeteclipse.org"><img src="images/Icon_planet.png" width="28" height="28" alt="Planet Eclipse" title="Planet Eclipse" /></a>
        </div>
    </div>
</div>

<div id="header-menu">
    <div id="header-nav">
    <ul>
        <li><a class="first_one" href="./" target="_self">Home</a></li> 
        <li><a  href="map_files.php" target="_self">For committers</a></li> 
        <li><a  href="recent.php" target="_self">Recent Translations</a></li> 
        <li><a  href="http://www.eclipse.org/babel" target="_self">About Babel</a></li> 
    </ul>
    </div>
    <div id="header-utils"></div>
</div>
<?php

global $User;

$LoginString = "LOGOUT";
$LoginAction = "?submit=Logout";
if(!$User) {
	$LoginString = "LOGIN";
	$LoginAction = "";
}

?>


<script src='js/yui2.3.1/yahoo/yahoo.js' type='text/javascript'></script>
<script src='js/yui2.3.1/dom/dom.js' type='text/javascript'></script>
<script src='js/yui2.3.1/event/event.js' type='text/javascript'></script>
<script src='js/yui2.3.1/connection/connection.js' type='text/javascript'></script>
<script src='js/yui2.3.1/logger/logger.js' type='text/javascript'></script>

<!--  
<script src='js/yui2.3.1/element/element-beta.js' type='text/javascript'></script>
<script src='js/yui2.3.1/tabview/tabview.js' type='text/javascript'></script>
-->

<script src='js/global.js' type='text/javascript'></script>
<script src='js/projectString.js' type='text/javascript'></script>
<script src='js/jumpto.js' type='text/javascript'></script>
<script src='js/language.js' type='text/javascript'></script>
<script src='js/project.js' type='text/javascript'></script>
<script src='js/version.js' type='text/javascript'></script>
<script src='js/files.js' type='text/javascript'></script>
<script src='js/search.js' type='text/javascript'></script>
<script src='js/translation.js' type='text/javascript'></script>
<script src='js/translationHint.js' type='text/javascript'></script>

<script language="javascript">
	document.getElementById("header-utils").innerHTML = "<ul><li><a href='login.php<?= $LoginAction ?>'><?= $LoginString ?></a></li></ul>";
</script>
