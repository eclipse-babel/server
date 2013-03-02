<?php
/*******************************************************************************
 * Copyright (c) 2009-2013 Eclipse Foundation, IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
 *    Kit Lo (IBM) - Bug 299402, Extract properties files from Eclipse project update sites for translation
 *    Kit Lo (IBM) - [402192] Extract project source files from Git repositories for translation
 *******************************************************************************/

require_once("cb_global.php");

$return = array();

$project_id = getHTTPParameter("project_id", "POST");
$version 	= getHTTPParameter("version", "POST");

$query = "SELECT m.project_id, m.version, r.train_id, m.location, m.filename FROM map_files m
	LEFT JOIN release_train_projects r ON m.project_id = r.project_id AND m.version = r.version
	WHERE m.is_active = 1 
	AND m.project_id = " . returnQuotedString(sqlSanitize($project_id, $dbh)) . "
	AND m.version = " . returnQuotedString(sqlSanitize($version, $dbh));

$res = mysql_query($query,$dbh);
if (mysql_affected_rows($dbh) > 0) {
	while ($line = mysql_fetch_array($res, MYSQL_ASSOC)) {
		echo $line['location'] . "\n";
	}
} else {
	echo "No map files or update sites found for $project_id $version";
}
?>