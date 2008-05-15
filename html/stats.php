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

$pageTitle 		= "Babel - Translation statistics";
$pageKeywords 	= "";
$incfile 		= "content/en_stats.php";

$PROJECT_VERSION = $App->getHTTPParameter("project_version");
$PROJECT_ID = "";
$VERSION	= "";

if($PROJECT_VERSION != "") {

	$items = explode("|", $PROJECT_VERSION);
	$PROJECT_ID = $items[0];
	$VERSION	= $items[1];
}
$LANGUAGE_ID= $App->getHTTPParameter("language_id");
$SUBMIT 	= $App->getHTTPParameter("submit");

$sql = "SELECT project_id, version FROM project_versions WHERE is_active ORDER BY project_id ASC, version DESC";
$rs_p_list = mysql_query($sql, $dbh);

$sql = "SELECT language_id, name FROM languages WHERE is_active ORDER BY name";
$rs_l_list = mysql_query($sql, $dbh);

if($SUBMIT == "showfiles") {
	$incfile 	= "content/en_map_files_show.php";
	$sql = "SELECT project_id, version, filename, location FROM map_files WHERE is_active = 1 
	AND project_id = " . $App->returnQuotedString($App->sqlSanitize($PROJECT_ID, $dbh)) . "
	AND version = " . $App->returnQuotedString($App->sqlSanitize($VERSION, $dbh));
	$rs_map_file_list = mysql_query($sql, $dbh);
	include($incfile);
}
else {
	
	$where = "";
	
	if($PROJECT_ID != "") {
		$where = $App->addAndIfNotNull($where) . " p.project_id = ";
		$where .= $App->returnQuotedString($App->sqlSanitize($PROJECT_ID, $dbh));
	}
	if($LANGUAGE_ID != "") {
		$where = $App->addAndIfNotNull($where) . " l.language_id = ";
		$where .= $App->returnQuotedString($App->sqlSanitize($LANGUAGE_ID, $dbh));
	}
	if($VERSION != "") {
		$where = $App->addAndIfNotNull($where) . "p.version = ";
		$where .= $App->returnQuotedString($App->sqlSanitize($VERSION, $dbh));
	}
	
	if($where != "") {
		$where = " WHERE " . $where;
	}
	
	$sql = "SELECT p.project_id, p.version, l.name, p.pct_complete FROM project_progress AS p INNER JOIN languages AS l ON l.language_id = p.language_id $where ORDER BY p.pct_complete DESC, p.project_id, p.version, l.name";
	$rs_p_stat = mysql_query($sql, $dbh);
	
	include("head.php");
	include($incfile);
	include("foot.php");  
}



?>