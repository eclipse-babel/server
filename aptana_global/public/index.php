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
$pageTitle = "Babel";
$pageKeywords = "";

require_once("../aptana.inc.php");

// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("login");
$welcome = "";
if (isset($_SESSION['s_userName']))
  $welcome = "Welcome back " . $_SESSION['s_userName'];
else {
	header("Location: login.php");
	exit;
}

$user  = new users_iu($s_userAcct);
$langs = new languages_iu(0);
$langs->sqlList("SELECT * FROM {SELF} ORDER BY name");

$langSel = (isset($_POST['language_id'])?$_POST['language_id']:$user->_primary_language_id);
$langCombo = "\n";
while ($langs->sqlNext())
  if ($langs->_id > 1) {
    $selected   = (($langSel==$langs->_id)?"Selected":"");
    $langCombo .= "<option value='$langs->_id' $selected>$langs->_name</option>\n";
  }


$borderC  = "#777799";

$agent = $_SERVER['HTTP_USER_AGENT'];
$overFlow = ((eregi("microsoft internet explorer", $agent))?"":"overflow:auto");

/*
<p />
$welcome
<p />

<a href='logout.php'>Logout</a>
*/  
// ------...------...------...------...------...------...------...------...------...------...------

# TODO: move all this JS to a .js file
require_once(BABEL_BASE_DIR . 'head.php');
echo <<< toTheEnd


<div id="maincontent">

<div id="midcolumn">

<h1>$pageTitle</h1>
<h2 id='logout'><a href="logout.php">logout</a></h2>

<!--   huh?   
-->

<script>window.onload="langChange($langSel)"</script>

<div class='babel-container'>

	<table cellspacing=0 cellpadding=0 border=0>
		<tr>
  			<td style='width:200px'><img src='/p.gif' width=200 height=1px></td>
  			<td style='width:325px'><img src='/p.gif' width=325 height=1px></td>
  			<td style='width:100%' ></td>
		</tr>

		<tr>  
		  <td style='width:200px;border:solid 1px $borderC;'>
		  
		    <table cellspacing=0 cellpadding=0 border=0>
    			<tr>
    				<td style='padding:4px 4px 0px 4px;'>&nbsp;<b>Translate from English to:</b></td>
    			</tr>
    			<tr>
    				<td style='padding:4px;'>
        				<select id="language_id" name="language_id" style='width:210px;' onchange="langChange(this.value);">$langCombo</select>
    				</td>
    			</tr>
    		</table>
    
    		<table cellspacing=0 cellpadding=0 border=0>
    			<tr>
    				<td style='padding:4px;border-top:solid 1px $borderC;'>&nbsp;<b>Select a Project</b></td>
    			</tr>
    			<tr>
    				<td style='width:220px;padding:0px;'>
				    	<div id=projDiv name=projDiv style='width:220px;overflow:hidden;border-top:solid 1px #bbb;background-color:#fff;'></div>
		   	    	</td>
		   	    </tr>
    		</table>
  		  </td>

  		  <td style='width:325px;border-top:solid 1px $borderC;border-bottom:solid 1px $borderC;'>
	    	<table cellspacing=0 cellpadding=0 border=0>
    			<tr><td style='width:2px'><img src='/p.gif' width=2 height=47px></td></tr>
    			<tr><td style='padding:5px;'>&nbsp;<b>Select an Entry</b></td></tr>
    			<tr>
    				<td style='width:325px;'>
    					<div id=entryDiv name=entryDiv style='$overFlow;width:325px;height:500px;border-top:solid 1px #bbb;background-color:#fff;'>    
    					</div>
    				</td>
    			</tr>
   		 	</table>
  		  </td>  

  		  <td style='width:100%;border:solid 1px $borderC;padding:10px;' >
  				<div id=entryEdit name=entryEdit >
    				<div style='width:300px;'></div>
  				</div>
  		 </td>
  	</tr>
  
</table>
  
</div>

</div> </div>

toTheEnd;
require_once(BABEL_BASE_DIR.'foot.php');
?>