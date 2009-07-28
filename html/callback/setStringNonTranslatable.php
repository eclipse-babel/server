<?php
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

$res = mysql_query($query,$dbh);
$row = mysql_fetch_assoc($res);

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

$res = mysql_query($query,$dbh);
$updated_rows = mysql_affected_rows();

if($updated_rows < 0){
	$message = "An error has occurred in processing your request, please file a bug.";
}elseif ($checked_state == 1) {
	$message = "'".$row['value']."' has been marked as non-translatable in ".$updated_rows." file(s).";
} else {
	$message = "'".$row['value']."' has been marked as translatable in ".$updated_rows." file(s).";
}

print "<br><br><br><center><b>$message</b></center>";
?>