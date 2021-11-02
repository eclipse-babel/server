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
 *    Andrew Johnson (IBM) - [564512] Escape HTML
*******************************************************************************/
require_once("cb_global.php");

$string_id = getHTTPParameter("string_id", "POST");
$checked_state = getHTTPParameter("check", "POST");

$query = "select
			strings.value, 
			strings.name as string_name, 
			files.name as file_name 
		  from
		  	strings,
		  	files
		  where
		  	files.file_id = strings.file_id
		  AND
		  	strings.string_id = '".addslashes($string_id)."
		 '";

$res = mysqli_query($dbh, $query);
$row = mysqli_fetch_assoc($res);

if($checked_state == "true"){
	$checked_state = 1;
}else{
	$checked_state = 0;
}

$query = "update 
			strings,files
		  set
			strings.non_translatable = '".addslashes($checked_state)."' 
		  where 		  	
			files.name = '".addslashes($row['file_name'])."'
			AND strings.name = '".addslashes($row['string_name'])."'
			AND strings.file_id = files.file_id
		  ";

$res = mysqli_query($dbh, $query);
$updated_rows = mysqli_affected_rows($dbh);

if($updated_rows < 0){
	$message = "An error has occurred in processing your request, please file a bug.";
}elseif ($checked_state == 1) {
	$message = "'".$row['value']."' has been marked as non-translatable in ".$updated_rows." file(s).";
} else {
	$message = "'".$row['value']."' has been marked as translatable in ".$updated_rows." file(s).";
}

print "<br><br><br><center><b>".nl2br(htmlspecialchars($message))."</b></center>";
?>