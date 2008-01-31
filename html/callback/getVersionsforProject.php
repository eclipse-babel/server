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

$query = "select 
			DISTINCT files.version,files.project_id
		  from 
		  	project_versions,
		  	files
		  where 
		  	files.project_id = project_versions.project_id
		  and
		  	files.version = project_versions.version
		  and
		  	project_versions.is_active = 1 
		  and 
		  	project_versions.project_id = '".addslashes($_SESSION['project'])."'";

//print $query."\n";

$res = mysql_query($query,$dbh);

$return = array();

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$ret = Array();
	$ret['version'] = $line['version'];
	if(isset($_SESSION['version']) and $line['version'] == $_SESSION['version']){
		$ret['current'] = true;
	}
	$return[] = $ret;
}

print json_encode($return);

?>