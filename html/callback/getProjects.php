<?php
/*******************************************************************************
 * Copyright (c) 2007-2019 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Denis Roy (Eclipse Foundation) - Bug 550544 - Babel server is not ready for PHP 7
*******************************************************************************/
require_once("cb_global.php");

$return = "";

if(isset($_SESSION['language'])) {
	$query = "SELECT /* getProjects.php */ DISTINCT P.project_id, P.is_active
FROM projects AS P
INNER JOIN files AS F ON P.project_id = F.project_id
WHERE P.is_active = 1";


	$res = mysqli_query($dbh, $query);

//	$return = '<ul id="project-choices">';
	$return = Array();
	while($line = mysqli_fetch_array($res, MYSQLI_ASSOC)){
		$ret = Array();
		$ret['project'] = $line['project_id'];
		//	$ret['version'] = $line['version'];
		if(isset($_SESSION['project']) and $line['project_id'] == $_SESSION['project']){
			$ret['current'] = true;
		}
		$return[] = $ret;
	}
	//	$return .= '<li><a href="project_id='.$line['project_id'].'">'.$line['project_id'].'</a>';

	//$return .= "</ul>";
}
print json_encode($return);

?>