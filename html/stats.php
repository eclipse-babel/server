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
*******************************************************************************/
include("global.php");

InitPage("");

global $User;
global $dbh;

$pageTitle 		= "Babel - Translation Statistics";
$pageKeywords 	= "";
$incfile 		= "content/en_stats.php";

$PROJECT_VERSION = getHTTPParameter("project_version");
$PROJECT_ID = "";
$VERSION	= "";

if($PROJECT_VERSION != "") {

	$items = explode("|", $PROJECT_VERSION);
	$PROJECT_ID = $items[0];
	$VERSION	= $items[1];
}
$LANGUAGE_ID= getHTTPParameter("language_id");
$SUBMIT 	= getHTTPParameter("submit");

$sql = "SELECT DISTINCT pv_m.project_id, pv_m.version FROM project_versions AS pv_m INNER JOIN map_files as m ON pv_m.project_id = m.project_id AND pv_m.version = m.version WHERE pv_m.is_active UNION SELECT DISTINCT pv_s.project_id, pv_s.version FROM project_versions AS pv_s INNER JOIN project_source_locations as s ON pv_s.project_id = s.project_id AND pv_s.version = s.version WHERE pv_s.is_active ORDER BY project_id ASC, version DESC";
$rs_p_list = mysql_query($sql, $dbh);

$sql = "SELECT language_id, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name FROM languages WHERE is_active AND iso_code != 'en' ORDER BY name";
$rs_l_list = mysql_query($sql, $dbh);


$where = "";

if($PROJECT_ID != "") {
	$where = addAndIfNotNull($where) . " p.project_id = ";
	$where .= returnQuotedString(sqlSanitize($PROJECT_ID, $dbh));
}
if($LANGUAGE_ID != "") {
	$where = addAndIfNotNull($where) . " l.language_id = ";
	$where .= returnQuotedString(sqlSanitize($LANGUAGE_ID, $dbh));
}
if($VERSION != "") {
	$where = addAndIfNotNull($where) . "p.version = ";
	$where .= returnQuotedString(sqlSanitize($VERSION, $dbh));
}

if($where != "") {
	$where = " WHERE " . $where;
}

$sql = "SELECT p.project_id, p.version, l.name, l.locale, p.pct_complete FROM project_progress AS p INNER JOIN languages AS l ON l.language_id = p.language_id $where ORDER BY p.pct_complete DESC, p.project_id, p.version, l.name";
$rs_p_stat = mysql_query($sql, $dbh);

global $addon;
$addon->callHook("head");
include($incfile);
$addon->callHook("footer");

?>