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
 *    Eclipse Foundation
*******************************************************************************/

require_once("cb_global.php");

$query = "select * from languages where is_active = 1 and language_id != 1 order by name";

$res = mysql_query($query,$dbh);


$return = Array();

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
//	$return .= "<li><a href='?language_id=".$line['language_id']."'>".$line['iso_code']. " - ". $line['name']. "</a>";
    if($line['language_id'] == $_SESSION['language']){
    	$line['current'] = true;
    }
	$return[] = $line;
}

print json_encode($return);
exit();

?>