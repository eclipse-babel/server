<?php
/*******************************************************************************
 * Copyright (c) 2008-2009 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
 *    Kit Lo (IBM) - patch, bug 266010, Map file table does not show release train and file name info
 *    Kit Lo (IBM) - Bug 299402, Extract properties files from Eclipse project update sites for translation
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


$pageTitle 		= "Babel - Define Map Files or Update Sites";
$pageKeywords 	= "";
$incfile 		= "content/en_map_files.php";

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
		# Set URL type
		$is_Map_file = 1;
		if ($_POST["urlType"] == "updateSites") {
			$is_Map_file = 0;
		}

		# Delete old map files for this project version
		$sql = "DELETE FROM map_files WHERE project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. " AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh));
		mysql_query($sql, $dbh);

		# Insert new map files for this project version
		$list = explode("\n", $FILE_FLD);
		foreach ($list as $file) {
			$file = str_replace("\r", "", $file);
			if(strlen($file) > 8) {
				$sql = "INSERT INTO map_files VALUES ("
					. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh))
					. "," . returnQuotedString(sqlSanitize($VERSION, $dbh))
					. "," . returnQuotedString(sqlSanitize(md5($file), $dbh))
					. "," . returnQuotedString(sqlSanitize($file, $dbh))
					. ", 1, $is_Map_file)";
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
		$GLOBALS['g_ERRSTRS'][0] = "Map files saved.";
	}
	else {
		$GLOBALS['g_ERRSTRS'][0] = "Project, version and URL cannot be empty.";  
	}
}
if($SUBMIT == "delete") {
	$SUBMIT = "showfiles";
	$sql = "DELETE FROM map_files WHERE  
	project_id = " . returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) . "
	AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh)) . "
	AND filename = ". returnQuotedString(sqlSanitize($FILENAME, $dbh)) . " LIMIT 1";
	mysql_query($sql, $dbh);
}


$sql = "SELECT project_id FROM projects WHERE is_active = 1 ORDER BY project_id";
$rs_project_list = mysql_query($sql, $dbh);

$sql = "SELECT pv.project_id, pv.version, count(m.is_active) AS map_count FROM project_versions as pv left join map_files as m on m.project_id = pv.project_id and m.version = pv.version WHERE pv.is_active = 1 and pv.version != 'unspecified' group by pv.project_id, pv.version ORDER BY pv.project_id ASC, pv.version DESC;";
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
