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


$query = "select * from project_versions where is_active = 1 and project_id = '".addslashes($_SESSION['project'])."'";

//print $query."\n";

$res = mysql_query($query,$dbh);

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$ret = Array();
	$ret['version'] = $line['version'];
	$return[] = $ret;
}

print json_encode($return);

?>