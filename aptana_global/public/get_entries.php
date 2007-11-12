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
InitPage("get_projects");

$lang  = $_GET['lang'];
$proj  = $_GET['proj'];
$entr  = new entries_iu(0);

$done = array();
$entr->sqlList("select name from entries where project_id=$proj and language_id=$lang");

while ($entr->sqlNext()) {
  $done[$entr->_name] = 1;
}

$entr->sqlList("select name from entries where project_id=$proj AND language_id=1 AND value>''"); // group by name order by name");
echo "<table cellspacing=0 cellpadding=0 border=0 style='width:321px;font-size:8pt;'>\n";
echo "<tr><td></td></tr>";
$x = 1;
while ($entr->sqlNext()) {
  $name  = trim($entr->_name);
  $id    = (empty($entr->_id)?"0":$entr->_id);
  if (isset($done[$entr->_name])) {
    echo "<tr><td style='border-bottom:solid 1px #aaa;'> ";
    echo "    <a class=ss href='#' onmousedown='selEntry($id,$x);' onmouseover='ratoverE($x);' onmouseout='ratoutE($x);'> ";
    echo "    <div id=myEdiv$x class=entryDone>$name</div></a></td></tr>\n";
  }
  else {
    echo "<tr><td style='border-bottom:solid 1px #aaa;'> ";
    echo "    <a class=ss href='#' onmousedown='selEntry($id,$x);' onmouseover='ratoverE($x);' onmouseout='ratoutE($x);'> ";
    echo "    <div id=myEdiv$x class=entryUnDone>$name</div></a> </td></tr>\n";
 }
    $x++;
}
echo "</table>\n";


/*

alert("There was a problem retrieving the XML data:\n" + req.statusText);
*/
// ------...------...------...------...------...------...------...------...------...------...------

?>