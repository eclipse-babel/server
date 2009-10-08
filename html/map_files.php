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


$pageTitle 		= "Babel - Define Map Files";
$pageKeywords 	= "";
$incfile 		= "content/en_map_files.php";

$PROJECT_ID = getHTTPParameter("project_id");
$VERSION	= getHTTPParameter("version");
$TRAIN_ID 	= getHTTPParameter("train_id");
$FILE_FLD	= getHTTPParameter("fileFld");
$FILENAME	= getHTTPParameter("filename");
$SUBMIT 	= getHTTPParameter("submit");

if($SUBMIT == "Save") {
	if($PROJECT_ID != "" && $VERSION != "" && $FILE_FLD != "") {
		$sql = "DELETE FROM map_files WHERE project_id = "
			. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh)) 
			. " AND version = " . returnQuotedString(sqlSanitize($VERSION, $dbh));
		mysql_query($sql, $dbh);

		# loop
		$list = explode("\n", $FILE_FLD);
		foreach ($list as $file) {
			$file = str_replace("\r", "", $file);
			if(strlen($file) > 8) {
				$sql = "INSERT INTO map_files VALUES ("
					. returnQuotedString(sqlSanitize($PROJECT_ID, $dbh))
					. "," . returnQuotedString(sqlSanitize($VERSION, $dbh))
					. "," . returnQuotedString(sqlSanitize(md5($file), $dbh))
					. "," . returnQuotedString(sqlSanitize($file, $dbh))
					. ", 1)";
				mysql_query($sql, $dbh);
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

$sql = "SELECT project_id, version FROM project_versions WHERE is_active = 1 and version != 'unspecified' ORDER BY project_id ASC, version DESC";
$rs_version_list = mysql_query($sql, $dbh);

$sql = "SELECT DISTINCT train_id FROM release_train_projects ORDER BY train_id ASC";
$rs_train_list = mysql_query($sql, $dbh);

$sql = "SELECT train_id, project_id, version FROM release_train_projects ORDER BY project_id, version ASC";
$rs_train_project_list = mysql_query($sql, $dbh);

global $addon;
$addon->callHook("head");
include($incfile);
$addon->callHook("footer");

?>