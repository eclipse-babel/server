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



if(!isset($_SESSION['project'])){
	return array();
}

$query = "select DISTINCT
		f.version,
		f.project_id, 
		IF(ISNULL(pct_complete),0,ROUND(pct_complete,1)) AS pct_complete
	from 
		project_versions AS v
		INNER JOIN files as f on (f.project_id = v.project_id AND f.version = v.version)
		LEFT JOIN project_progress AS p ON (p.project_id = v.project_id AND p.version = v.version)
	where 
		v.is_active = 1 
		and v.project_id = '".addslashes($_SESSION['project'])."'";

//print $query."\n";

$res = mysql_query($query,$dbh);

$return = array();

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$ret = Array();
	$ret['version'] = $line['version'];
	$ret['pct'] = $line['pct_complete'];
	
	if(isset($_SESSION['version']) and $line['version'] == $_SESSION['version']){
		$ret['current'] = true;
	}
	$return[] = $ret;
}

print json_encode($return);

?>