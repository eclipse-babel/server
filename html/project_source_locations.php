<?php
/*******************************************************************************
 * Copyright (c) 2013 IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Kit Lo (IBM) - [402192] Extract project source files from Git repositories for translation
 *******************************************************************************/

include("global.php");

InitPage("");

global $User;
global $dbh;

if(!isset($User->userid)) {
	exitTo("importing.php");
}

if($User->is_committer != 1) {
	exitTo("login.php?errNo=3214","error: 3214 - you must be an Eclipse committer to access this page.");
}

require(dirname(__FILE__) . "/../classes/file/file.class.php");


$pageTitle 		= "Babel - Define Project Source Locations";
$pageKeywords 	= "";
$incfile 		= "content/en_project_source_locations.php";

$PROJECT_ID = getHTTPParameter("project_id");
$VERSION	= getHTTPParameter("version");
$TRAIN_ID 	= getHTTPParameter("train_id");
$FILE_FLD	= getHTTPParameter("fileFld");
$PATTERNS	= getHTTPParameter("patterns");
$FILENAME	= getHTTPParameter("filename");
$SUBMIT 	= getHTTPParameter("submit");

$VERSION = preg_replace("/^\* /", "", $VERSION);

if($SUBMIT == "Save") {
	if($PROJECT_ID != "" && $VERSION != "" && $FILE_FLD != "") {
		# Delete old project_source_locations for this project version
		$sql = "DELETE FROM project_source_locations WHERE project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. " AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh));
		mysql_query($sql, $dbh);

		# Insert new project_source_locations for this project version
		$list = explode("\n", $FILE_FLD);
		foreach ($list as $file) {
			$file = str_replace("\r", "", $file);
			if(strlen($file) > 8) {
				$sql = "INSERT INTO project_source_locations VALUES ("
					. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh))
					. "," . returnQuotedString(sqlSanitize($VERSION, $dbh))
					. "," . returnQuotedString(sqlSanitize($file, $dbh))
					. ")";
				mysql_query($sql, $dbh);
			}
		}

		# Delete old plugin exclude patterns for this project version
		$sql = "DELETE FROM plugin_exclude_patterns WHERE project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. " AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh));
		mysql_query($sql, $dbh);

		# Insert new plugin exclude patterns for this project version
		$list = explode("\n", $PATTERNS);
		foreach ($list as $pattern) {
			$pattern = str_replace("\r", "", $pattern);
			if (strlen($pattern) > 0) {
				if (strlen($pattern) > 26 && strcmp(substr($pattern, 0, 26), "No plugin exclude patterns") == 0) {
				} else {
					$sql = "INSERT INTO plugin_exclude_patterns VALUES ("
						. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh))
						. "," . returnQuotedString(sqlSanitize($VERSION, $dbh))
						. "," . returnQuotedString(sqlSanitize($pattern, $dbh)) . ")";
					mysql_query($sql, $dbh);
				}
			}
		}

		# Save the project/train association
		$sql = "DELETE FROM release_train_projects WHERE project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. " AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh));
		mysql_query($sql, $dbh);
		$sql = "INSERT INTO release_train_projects SET project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. ", version = " . returnQuotedString(sqlSanitize($VERSION, $dbh))
			. ", train_id = " . returnQuotedString(sqlSanitize($TRAIN_ID, $dbh));
		mysql_query($sql, $dbh);
		$GLOBALS['g_ERRSTRS'][0] = "Project source locations saved.";
	}
	else {
		$GLOBALS['g_ERRSTRS'][0] = "Project, version and URL cannot be empty.";  
	}
}

$sql = "SELECT project_id FROM projects WHERE is_active = 1 ORDER BY project_id";
$rs_project_list = mysql_query($sql, $dbh);

$sql = "SELECT pv.project_id, pv.version, count(m.location) AS map_count FROM project_versions as pv left join project_source_locations as m on m.project_id = pv.project_id and m.version = pv.version WHERE pv.is_active = 1 and pv.version != 'unspecified' group by pv.project_id, pv.version ORDER BY pv.project_id ASC, pv.version DESC;";
$rs_version_list = mysql_query($sql, $dbh);

$sql = "SELECT train_id FROM release_trains ORDER BY train_id ASC";
$rs_train_list = mysql_query($sql, $dbh);

$sql = "SELECT train_id, project_id, version FROM release_train_projects ORDER BY project_id, version ASC";
$rs_train_project_list = mysql_query($sql, $dbh);

global $addon;
$addon->callHook("head");
include($incfile);
$addon->callHook("footer");

?>