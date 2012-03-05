<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - initial API and implementation
*******************************************************************************/
require_once("cb_global.php");

$return = array();

$tr_string = getHTTPParameter("tr_string", "POST");

if(isset($_SESSION['language']) and isset($_SESSION['version']) and isset($_SESSION['project'])){
	$language = $_SESSION['language'];
	$version = $_SESSION['version'];
	$project_id = $_SESSION['project'];
}else{
	return false;
}


/* $query = "SELECT DISTINCT t.value 
	FROM translations as t 
		INNER JOIN strings AS s ON s.string_id = t.string_id 
	WHERE s.value like '%" . addslashes($tr_string). "%' 
		AND t.is_active
		AND t.language_id = '".addslashes($language)."'
	ORDER BY LENGTH(t.value) ASC LIMIT 15";
*/

$train_id = "";
$query = "SELECT train_id FROM release_trains ORDER BY release_train_version LIMIT 2";
$res = mysql_query($query,$dbh);
while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	if($train_id != "") {
		$train_id .= ",";
	}
	$train_id .= "'" . $line['value'] . "'";
}

if($train_id == "") {
	$train_id = 'indigo';
}


$query = "SELECT DISTINCT t.value 
FROM translations as t 
 INNER JOIN strings AS s ON s.string_id = t.string_id
 INNER JOIN files   AS f ON s.file_id = f.file_id
 INNER JOIN release_train_projects AS tr ON tr.project_id = f.project_id AND tr.version = f.version
WHERE s.value like '%" . addslashes($tr_string). "%' 
 AND t.is_active
 AND tr.train_id IN (" . $train_id . ")
 AND t.language_id = '".addslashes($language)."'
ORDER BY LENGTH(t.value) ASC LIMIT 15";
# print $query."\n";

$res = mysql_query($query,$dbh);
if(mysql_affected_rows($dbh) > 0) {
	echo "<ul>";
	while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
		echo "<li>" . $line['value'] . "</li>";
	}
	echo "</ul>";
}
else {
	echo "No hints found.  Press [clear] to start over.";
}

?>