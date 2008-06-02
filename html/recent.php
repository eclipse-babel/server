<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
*******************************************************************************/
include("global.php");

InitPage("");

global $User;
global $App, $dbh;

$pageTitle 		= "Babel - Recent Translations";
$pageKeywords 	= "";
$incfile 		= "content/en_recent_html.php";

$PROJECT_VERSION = $App->getHTTPParameter("project_version");
$PROJECT_ID = "";
$VERSION	= "";

if($PROJECT_VERSION != "") {

	$items = explode("|", $PROJECT_VERSION);
	$PROJECT_ID = $items[0];
	$VERSION	= $items[1];
}
$LANGUAGE_ID= $App->getHTTPParameter("language_id");
if($LANGUAGE_ID == "") {
	$LANGUAGE_ID = $_SESSION["language"];
}
if($LANGUAGE_ID == "All") {
	$LANGUAGE_ID = "";
}
$LIMIT 		= $App->getHTTPParameter("limit");
if($LIMIT == "" || $LIMIT <= 0 || $LIMIT > 1000) {
	$LIMIT = 200;
}
$FORMAT		= $App->getHTTPParameter("format");
if($FORMAT == "rss") {
	$incfile 		= "content/en_recent_rss.php";
}
$SUBMIT 	= $App->getHTTPParameter("submit");

$sql = "SELECT DISTINCT pv.project_id, pv.version FROM project_versions AS pv INNER JOIN map_files as m ON pv.project_id = m.project_id AND pv.version = m.version WHERE pv.is_active ORDER BY pv.project_id ASC, pv.version DESC";
$rs_p_list = mysql_query($sql, $dbh);

$sql = "SELECT language_id, name FROM languages WHERE is_active ORDER BY name";
$rs_l_list = mysql_query($sql, $dbh);

$where = " t.is_active ";

if($PROJECT_ID != "") {
	$where = $App->addAndIfNotNull($where) . " f.project_id = ";
	$where .= $App->returnQuotedString($App->sqlSanitize($PROJECT_ID, $dbh));
}
if($LANGUAGE_ID != "") {
	$where = $App->addAndIfNotNull($where) . " t.language_id = ";
	$where .= $App->returnQuotedString($App->sqlSanitize($LANGUAGE_ID, $dbh));
}
if($VERSION != "") {
	$where = $App->addAndIfNotNull($where) . "f.version = ";
	$where .= $App->returnQuotedString($App->sqlSanitize($VERSION, $dbh));
}

if($where != "") {
	$where = " WHERE " . $where;
}


$sql = "SELECT 
  s.name AS String_Key, s.value AS English_Value, 
  t.value AS Translation, 
  CONCAT(CONCAT(first_name, ' '), u.last_name) AS Who, 
  t.created_on AS Created_on,
  f.project_id AS Project, f.version AS Version, f.name AS File_Name
FROM 
  translations as t 
  LEFT JOIN strings as s on s.string_id = t.string_id 
  LEFT JOIN files as f on s.file_id = f.file_id 
  LEFT JOIN users as u on u.userid = t.userid 
$where
ORDER BY t.created_on desc 
LIMIT $LIMIT";
$rs_p_stat = mysql_query($sql, $dbh);
include("head.php");
include($incfile);
include("foot.php");  



?>