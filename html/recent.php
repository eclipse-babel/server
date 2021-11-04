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
 *    Kit Lo (IBM) - [261739] Inconsistent use of language names
 *    Kit Lo (IBM) - [411832] Stats and Recent pages do not show Orion in project dropdown list
 *    Satoru Yoshida - [221181] Search a specific string
*******************************************************************************/
include("global.php");

InitPage("");

global $User;
global $dbh;

$pageTitle 		= "Babel - Recent Translations";
$pageKeywords 	= "";
$incfile 		= "content/en_recent_html_list.php";

$PROJECT_VERSION = getHTTPParameter("project_version");
$PROJECT_ID = "";
$VERSION	= "";

if($PROJECT_VERSION != "") {
	$items = explode("|", $PROJECT_VERSION);
	$PROJECT_ID = $items[0];
	$VERSION	= $items[1];
}
$LANGUAGE_ID= getHTTPParameter("language_id");
if($LANGUAGE_ID == "") {
	$LANGUAGE_ID = $_SESSION["language"];
}

$FUZZY		= getHTTPParameter("fuzzy");
if($FUZZY == "" || $FUZZY != 1) {
	$FUZZY = 0;
}

if($LANGUAGE_ID == "All") {
	$LANGUAGE_ID = "";
}
$LIMIT 		= getHTTPParameter("limit");
if($LIMIT == "" || $LIMIT <= 0 || $LIMIT > 20000) {
	$LIMIT = 25;
}
$LAYOUT 		= getHTTPParameter("layout");
if($LAYOUT == "list" || $LAYOUT == "table") {
	$incfile = "content/en_recent_html_" . $LAYOUT . ".php";
}
$FORMAT		= getHTTPParameter("format");
if($FORMAT == "rss") {
	$incfile 		= "content/en_recent_rss.php";
}
$s_value	= getHTTPParameter("s_value");
$s_value	= htmlspecialchars(trim($s_value));
if ($s_value !== '') {
	$s_value_in_sql = $s_value . '%';
} else {
	$s_value_in_sql = '';
}
$USERID		= getHTTPParameter("userid");
$SUBMIT 	= getHTTPParameter("submit");

$sql = "SELECT DISTINCT pv_m.project_id, pv_m.version FROM project_versions AS pv_m INNER JOIN map_files as m ON pv_m.project_id = m.project_id AND pv_m.version = m.version WHERE pv_m.is_active UNION SELECT DISTINCT pv_s.project_id, pv_s.version FROM project_versions AS pv_s INNER JOIN project_source_locations as s ON pv_s.project_id = s.project_id AND pv_s.version = s.version WHERE pv_s.is_active ORDER BY project_id ASC, version DESC";
$rs_p_list = mysqli_query($dbh, $sql);

$sql = "SELECT language_id, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name FROM languages WHERE is_active AND iso_code != 'en' ORDER BY name";
$rs_l_list = mysqli_query($dbh, $sql);

$where = " t.is_active ";

if($PROJECT_ID != "") {
	$where = addAndIfNotNull($where) . " f.project_id = ";
	$where .= returnQuotedString(sqlSanitize($PROJECT_ID, $dbh));
}
if($LANGUAGE_ID != "") {
	$where = addAndIfNotNull($where) . " t.language_id = ";
	$where .= returnQuotedString(sqlSanitize($LANGUAGE_ID, $dbh));
}
if($VERSION != "") {
	$where = addAndIfNotNull($where) . "f.version = ";
	$where .= returnQuotedString(sqlSanitize($VERSION, $dbh));
}
if($s_value_in_sql !== "") {
	$where = addAndIfNotNull($where) . "s.value like ";
	$where .= returnQuotedString(sqlSanitize(htmlspecialchars_decode($s_value_in_sql), $dbh));
}
if($USERID != "") {
	$where = addAndIfNotNull($where) . "u.userid = ";
	$where .= sqlSanitize($USERID, $dbh);
}
if($FUZZY == 1) {
	$where = addAndIfNotNull($where) . "t.possibly_incorrect = 1 ";
}

if($where != "") {
	$where = " WHERE " . $where;
}


$sql = "SELECT 
  s.name AS string_key, s.value as string_value, 
  t.value as translation,
  t.possibly_incorrect as fuzzy, 
  IF(u.last_name <> '' AND u.first_name <> '', 
  	CONCAT(CONCAT(first_name, ' '), u.last_name), 
  	IF(u.first_name <> '', u.first_name, u.last_name)) AS who,
  u.userid, 
  t.created_on, l.iso_code as language,
  f.project_id, f.version, f.name
FROM 
  translations as t 
  LEFT JOIN strings as s on s.string_id = t.string_id 
  LEFT JOIN files as f on s.file_id = f.file_id 
  LEFT JOIN users as u on u.userid = t.userid
  LEFT JOIN languages as l on l.language_id = t.language_id 
$where
ORDER BY t.created_on desc 
LIMIT $LIMIT";
$rs_p_stat = mysqli_query($dbh, $sql);
global $addon;
$addon->callHook("head");
include($incfile);
$addon->callHook("footer");

?>