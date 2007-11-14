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


<script type='text/javascript'>
var req;
var reqID;
var lang;
var projID   = 0;
var projIdx  = 0;
var projWid  = 0;
var entryID  = 0;
var entryIdx = 0;
var isDirty  = false;

function ratover(idx) {
  divItem = document.getElementById('myPdiv' + idx);
  if (divItem.className != "projSel")
    divItem.className = "graphOut";
}

function ratout(idx) {
  divItem = document.getElementById('myPdiv' + idx);
  if (divItem.className != "projSel")
    divItem.className = "graph";
  
}

function ratoverE(idx) {
  divItem = document.getElementById('myEdiv' + idx);
  if (divItem.className == "entryDone")
    divItem.className = "entryHi1";
  else if (divItem.className == "entryUnDone")
    divItem.className = "entryHi2";
}

function ratoutE(idx) {
  divItem = document.getElementById('myEdiv' + idx);
  if (divItem.className == "entryHi1")
    divItem.className = "entryDone";
  else if (divItem.className == "entryHi2")
    divItem.className = "entryUnDone";
}



function langChange(l) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  lang = l;
  resetEntryEdit(lang,0);
  resetEntryList(lang,0);
  resetProjectList(lang);
}


function selProj(id,idx) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  if (projIdx) {
    if (divItem = document.getElementById('myPdiv' + projIdx)) {
      divItem.className = "graph";
      divItem.style.width = projWid;
    }
  }
  projIdx = idx;
  projID  = id
  if (divItem = document.getElementById('myPdiv' + projIdx)) {
    projWid = divItem.style.width;
    divItem.className = "projSel";
    divItem.style.width = "220px";
  }
  resetEntryList(lang,projID);
  resetEntryEdit(lang,0);
}

function selEntry(id,idx) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  if (entryIdx) {
    if (divItem = document.getElementById('myEdiv' + entryIdx)) {
      if ((divItem.className == "entrySel1") || (divItem.className == "entryDone")) 
        divItem.className = "entryDone";
      else 
        divItem.className = "entryUnDone";
    }
  }
  entryIdx = idx;
  if (divItem = document.getElementById('myEdiv' + entryIdx)) {
    if ((divItem.className == "entryDone") || (divItem.className == "entryHi1"))
      divItem.className = "entrySel1";
    else 
      divItem.className = "entrySel2";
    entryID = divItem.innerHTML;
  }
  resetEntryEdit(lang,1);    

}

function PostEntry() {
  ctrl1 = "";
  ctrl2 = "";
  if (divItem = document.getElementById('ctrl1'))
    ctrl1 = divItem.value;
  if (divItem = document.getElementById('ctrl2')) 
    ctrl2 = divItem.value;
  isDirty = false;
  loadXMLDoc("post_entry.php?lang=" + lang + "&proj=" + projID + "&entry=" + entryID + "&ctrl2=" + ctrl2 + "&ctrl1=" + ctrl1,4);
}



function resetEntryEdit(l,id) {
  if (id) {
    if (lang && projID && entryIdx && (divItem = document.getElementById('entryEdit')))
      divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
    loadXMLDoc("get_entry.php?lang=" + lang + "&proj=" + projID + "&entry=" + entryID,3);
  } 
  else {
    if (divItem = document.getElementById('entryEdit'))
      divItem.innerHTML = "";    
  }
  isDirty = false;
}


function resetEntryList(l,p) {
  entryID  = 0;
  entryIdx = 0;
  if (p == 0) {
    divItem = document.getElementById('entryDiv');
    divItem.innerHTML = "";
  }
  else {
    if (divItem = document.getElementById('entryDiv'))
      divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
    loadXMLDoc("get_entries.php?lang=" + l + "&proj=" + p,2);
  }
}





function resetProjectList(l) {
  projID = 0;
  if (divItem = document.getElementById('projDiv'))
    divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
  loadXMLDoc("get_projects.php?lang=" + l,1);
}





function processReqChange() {
  if (req.readyState == 4) {
    if (req.status == 200) {
      if (reqID == 1) {
        if (divItem = document.getElementById('projDiv'))
        divItem.innerHTML = req.responseText;    
        
      }
      else if (reqID == 2) {
        if (divItem = document.getElementById('entryDiv'))
        divItem.innerHTML = req.responseText;    
      }
      else if (reqID == 3) {
        if (divItem = document.getElementById('entryEdit'))
        divItem.innerHTML = req.responseText;    
        
      }
      else if (reqID == 4) {
        
        
      }
    }
    else {
      ; //
    }
  }
}


function loadXMLDoc(url,id) {
  reqID = id;
 
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest();
    req.onreadystatechange = processReqChange;
    req.open('GET', url, true);
    req.send(null);
  }
  else if (window.ActiveXObject) {
    req = new ActiveXObject('Msxml2.XMLHTTP');
    if (req) {
      req.onreadystatechange = processReqChange;
      req.open('GET', url, true);
      req.send();
    }
  }
}


function dirty() {
  isDirty = true;
}

</script>


<div id="maincontent">
<div id="midcolumn">

<h1>$pageTitle</h1>

<!--   huh?   -->
<body onload="langChange($langSel);">
<div style='padding-right:20px'>


<table cellspacing=0 cellpadding=0 border=0>
<tr>
  <td style='width:200px'><img src='/p.gif' width=200 height=1px></td>
  <td style='width:325px'><img src='/p.gif' width=325 height=1px></td>
  <td style='width:100%' ></td>
</tr>

<tr>  

  <td style='width:200px;border:solid 1px $borderC;'>
    <table cellspacing=0 cellpadding=0 border=0>
    <tr><td style='padding:4px 4px 0px 4px;'>&nbsp;<b>Translate from English to:</b></td><tr>
    <tr><td style='padding:4px;'>
        <select id="language_id" name="language_id" style='width:210px;' onchange="langChange(this.value);">
        $langCombo
        </select>
    </td></tr>
    </table>
    
    
    <table cellspacing=0 cellpadding=0 border=0>
    <tr><td style='padding:4px;border-top:solid 1px $borderC;'>&nbsp;<b>Select a Project</b></td></tr>
    <tr><td style='width:220px;padding:0px;'>


    <div id=projDiv name=projDiv style='width:220px;overflow:hidden;border-top:solid 1px #bbb;background-color:#fff;'>
    </div>


    </td></tr>
    </table>
  </td>


  <td style='width:325px;border-top:solid 1px $borderC;border-bottom:solid 1px $borderC;'>

    <table cellspacing=0 cellpadding=0 border=0>
    <tr><td style='width:2px'><img src='/p.gif' width=2 height=47px></td></tr>
    <tr><td style='padding:5px;'>&nbsp;<b>Select an Entry</b></td></tr>
    <tr><td style='width:325px;'>

    <div id=entryDiv name=entryDiv style='$overFlow;width:325px;height:500px;border-top:solid 1px #bbb;background-color:#fff;'>    

    </div>


    </td></tr>
    </table>

  </td>  

  
  <td style='width:100%;border:solid 1px $borderC;padding:10px;' >
  <div id=entryEdit name=entryEdit >
    <div style='width:300px;'></div>

  </div>
</div></div>


toTheEnd;
require_once(BABEL_BASE_DIR.'foot.php');
?>