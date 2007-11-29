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

$query = "select * from projects where is_active = 1";
$res = mysql_query($query,$dbh);

$return = '<ul id="project-choices">';
$return .= "Please select a project to translate:<br>";
while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$return .= '<li><a href="project_id='.$line['project_id'].'">'.$line['project_id'].'</a>';
}
$return .= "</ul>";

print $return;

?>