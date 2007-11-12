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
// if not logged in redirect to login page
// otherwise show choices

require_once("../aptana.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("get_entry");

$langID  = $_GET['lang'];
$projID  = $_GET['proj'];
$entryN  = $_GET['entry'];
$nextId  = 0;
$nextIdx = 1;
$entr  = new entries_iu(0);




$entr->sqlList("select name from entries where project_id=$projID AND language_id=1 AND value>''"); // group by name order by name");
$x = 1;
$idIdx = array();
while ($entr->sqlNext()) {
  $id    = (empty($entr->_id)?"0":$entr->_id);
  $idIdx[$x] = $id;
  if ($nextIdx == $x)
    $nextId  = $id;
  $x++;
  if ($entr->_name == $entryN) {
    $nextIdx = $x;
  }
}

$entr->sqlLoad("name='$entryN' AND project_id=$projID AND language_id=$langID");

if ($entr->_language_id && ($entr->_language_id!=$langID))
exit;
if ($entr->_project_id && ($entr->_project_id!=$projID))
exit;

$entr->_language_id = $langID;
$entr->_project_id  = $projID;

$english = new entries_iu(0);
$english->sqlLoad("name='$entryN' AND project_id=$projID AND language_id=1");

$isEmpty = "no";
if ($entr->_id == 0) {
  $entr->_name        = $entryN;
  $entr->_language_id = $langID;
  $entr->_project_id  = $projID;
$isEmpty = "yes";
  
}


$proj = new projects_iu($projID);
$pname  = trim(strlen($proj->_name)?$proj->_name:$proj->_package_name);

$sel0 = ($entr->_rating==0?"selected":"");
$sel1 = ($entr->_rating==1?"selected":"");
$sel2 = ($entr->_rating==2?"selected":"");
$sel3 = ($entr->_rating==3?"selected":"");
$sel4 = ($entr->_rating==4?"selected":"");
$sel5 = ($entr->_rating==5?"selected":"");

echo <<< toTheEnd


<table cellspacing=10 cellpadding=2 border=0>
<tr>
<td id="formLblSmall">Project</td>
<td>$pname</td>
</tr>  
  
<tr>
<td id="formLblSmall">Entry&nbsp;Name</td>
<td style='border:solid 1px #333377;color:#000000;'>$english->_name</td>
</tr>  

<tr>
<td id="formLblSmall"></td>
<td style='font-size:7pt;border:solid 1px #bbb;color:#666666;background:#efefef;'>The entry name is split into two parts. The part before the underscore '_' is the name of the class in Aptana. Use this to help guide you where this item is used in code. The part ofter the underscore is an abbreviated name to help find this item easily in a list.</td>
</tr>  



<tr>
<td id="formLblSmall">English&nbsp;Text</td>
<td style='border:solid 1px #333377;color:#000099;'>$english->_value</td>
</tr>  

<tr>
<td id="formLblSmall">Translation</td>
<td><textarea id=ctrl1 rows='4' style='width:100%;' onKeyPress="dirty();">$entr->_value</textarea></td>
</tr>  

<tr>
<td id="formLblSmall"></td>
<td style='font-size:7pt;border:solid 1px #bbb;color:#666666;background:#efefef;'>If the original text has tokens in it '{0}', make sure that the translated text contains the same tokens.</td>
</tr>  

<tr>
<td id="formLblSmall">Quality&nbsp;rating</td>
<td style='font-size:7pt;border:solid 1px #bbb;background:#ffffff;'> 
<select id=ctrl2 onchange="dirty();">
<option value='0' $sel0> </option>
<option value='1' $sel1>1</option>
<option value='2' $sel2>2</option>
<option value='3' $sel3>3</option>
<option value='4' $sel4>4</option>
<option value='5' $sel5>5</option>
</select>
  Please give this entry a 1-5 stars rating.</td>
</tr>  

<tr>
<td id="formLblSmall"></td>
<td><input type='button' name='save' value='Save Translation' onclick="PostEntry();">&nbsp;&nbsp;
<input type='button' name='next' value='Next' onclick="selEntry($nextId,$nextIdx);">
</td>
</tr>  

  
</table>  


toTheEnd;

// ------...------...------...------...------...------...------...------...------...------...------

?>