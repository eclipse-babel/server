<?php
/*******************************************************************************
 * Copyright (c) 2008-2020 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - initial API and implementation
 *    Satoru Yoshida - [470120] it is nice if translation hint will prefer front match.
 *    Andrew Johnson (IBM) - [564512] Escape HTML for hints
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

//At first, performs front match
$query = "SELECT DISTINCT t.value 
FROM translations as t 
 INNER JOIN strings AS s ON s.string_id = t.string_id
 INNER JOIN files   AS f ON s.file_id = f.file_id
WHERE s.value like '" . addslashes($tr_string). "%' 
 AND t.is_active
 AND t.language_id = '".addslashes($language)."'
ORDER BY LENGTH(t.value) ASC LIMIT 15";

$res = mysqli_query($dbh, $query);
if(mysqli_affected_rows($dbh) > 0) {
	echo "<ul>";
	while($line = mysqli_fetch_array($res, MYSQLI_ASSOC)){
	    echo "<li>", nl2br(htmlspecialchars($line['value'])), "</li>";
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
     INNER JOIN release_trains as rt ON rt.train_id = tr.train_id AND rt.is_active
	WHERE s.value like '%" . addslashes($tr_string). "%'
	 AND t.is_active
	 AND t.language_id = '".addslashes($language)."'
	ORDER BY LENGTH(t.value) ASC LIMIT 15";

	$res = mysqli_query($dbh, $query2);
	if(mysqli_affected_rows($dbh) > 0) {
		echo "<ul>";
		while($line = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			echo "<li>", nl2br(htmlspecialchars($line['value'])), "</li>";
		}
		echo "</ul>";
	}
	else {
		echo "No hints found.  Press [clear] to start over.";
	}

}
?>