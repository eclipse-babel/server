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

$proj_post = $App->getHTTPParameter("proj", "POST");
if(!$proj_post){
	$proj_post = $_SESSION['project'];
}


$query = "select 
			strings.* 
		  from 
		  	strings,
		  	files 
		  where 
		  	strings.is_active != 0 
		  and 
		  	strings.file_id = files.file_id 
		  and 
			files.project_id = '".addslashes($proj_post)."'";

$res = mysql_query($query,$dbh);

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$return .= "<li><a href='?string_id=".$line['string_id']."'>".$line['value']."</a>";
}

?>

<ul id='string-choices' style="border: 1px solid black; height: 10em; width: 600px; overflow-y: scroll;">
	<?=$return;?>
</ul>
