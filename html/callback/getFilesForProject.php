<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation

*******************************************************************************/
require_once("cb_global.php");

$return = array();


if(!isset($_SESSION['project']) or !isset($_SESSION['version'])){
	return $return; 
}

$language = "";
if(isset($_SESSION['language'])) {
		$language =  $_SESSION['language'];
}

$query = "SELECT 
        f.name, 
        IF(ISNULL(pct_complete),0,pct_complete) AS pct_complete
FROM
        files AS f
        LEFT JOIN project_versions AS v ON v.project_id = f.project_id 
        AND v.version = f.version
        LEFT JOIN file_progress as p ON p.file_id = f.file_id
          AND p.language_id = '" . addslashes($language) . "'
WHERE
        v.is_active = 1 
        AND v.project_id = '".addslashes($_SESSION['project'])."'
        AND f.version = '".addslashes($_SESSION['version'])."'
        GROUP BY f.name
        ORDER BY pct_complete, f.name";

# print $query."\n";

$res = mysql_query($query,$dbh);


while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$ret = Array();
	
	$ret['name'] = $line['name'];
	$ret['pct'] = $line['pct_complete'];
	
	
	if(isset($_SESSION['file']) and $line['name'] == $_SESSION['file']){
		$ret['current'] = true;
	}
	$return[] = $ret;
}

print json_encode($return);

?>