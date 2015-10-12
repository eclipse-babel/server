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
 *    Satoru Yoshida - [470120] it is nice if translation hint will prefer front match.
*******************************************************************************/
require_once("cb_global.php");

$tr_string = getHTTPParameter("tr_string", "POST");

//if contains ampersand, remove before matching
$tr_string = preg_replace('/\&/', '', $tr_string, 1);
if (strlen(trim($tr_string)) < 1) {
	return false;
}

if(isset($_SESSION['language']) and isset($_SESSION['version']) and isset($_SESSION['project'])){
	$language = $_SESSION['language'];
	$version = $_SESSION['version'];
	$project_id = $_SESSION['project'];
}else{
	return false;
}

$train_id = "";
$query = "SELECT train_id FROM release_trains ORDER BY train_version LIMIT 2";
$res = mysql_query($query,$dbh);
while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	if($train_id != "") {
		$train_id .= ",";
	}
	$train_id .= "'" . $line['train_id'] . "'";
}

if($train_id == "") {
	$train_id = 'kepler';
}

//At first, performs front match
$query = "SELECT DISTINCT t.value 
FROM translations as t 
 INNER JOIN strings AS s ON s.string_id = t.string_id
 INNER JOIN files   AS f ON s.file_id = f.file_id
 INNER JOIN release_train_projects AS tr ON tr.project_id = f.project_id AND tr.version = f.version
WHERE s.value like '" . addslashes($tr_string). "%' 
 AND t.is_active
 AND tr.train_id IN (" . $train_id . ")
 AND t.language_id = '".addslashes($language)."'
ORDER BY LENGTH(t.value) ASC LIMIT 15";

$res = mysql_query($query,$dbh);
if(mysql_affected_rows($dbh) > 0) {
	echo "<ul>";
	while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
		echo "<li>", $line['value'], "</li>";
	}
	echo "</ul>";
}
else {

	//At second, performs partial match
	$query2 = "SELECT DISTINCT t.value
	FROM translations as t
	 INNER JOIN strings AS s ON s.string_id = t.string_id
	 INNER JOIN files   AS f ON s.file_id = f.file_id
	 INNER JOIN release_train_projects AS tr ON tr.project_id = f.project_id AND tr.version = f.version
	WHERE s.value like '%" . addslashes($tr_string). "%'
	 AND t.is_active
	 AND tr.train_id IN (" . $train_id . ")
	 AND t.language_id = '".addslashes($language)."'
	ORDER BY LENGTH(t.value) ASC LIMIT 15";
	
	$res = mysql_query($query2,$dbh);
	if(mysql_affected_rows($dbh) > 0) {
		echo "<ul>";
		while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
			echo "<li>", $line['value'], "</li>";
		}
		echo "</ul>";
	}
	else {
		echo "No hints found.  Press [clear] to start over.";
	}

}
?>