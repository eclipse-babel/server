<?php
/*******************************************************************************
 * Copyright (c) 2010-2013 Eclipse Foundation, IBM Corporation and others.
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

$query = "SELECT pattern FROM plugin_exclude_patterns WHERE project_id = " . returnQuotedString(sqlSanitize($project_id, $dbh)) .
	" AND version = " . returnQuotedString(sqlSanitize($version, $dbh));

$res = mysqli_query($dbh, $query);
if (mysql_affected_rows($dbh) > 0) {
	while ($line = mysql_fetch_array($res, MYSQL_ASSOC)) {
		echo $line['pattern'] . "\n";
	}
} else {
	echo "No plugin exclude patterns found for $project_id $version";
}
?>