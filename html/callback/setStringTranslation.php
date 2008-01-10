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

//print_r($_POST);

$string_id = $App->getHTTPParameter("string_id", "POST");
$translation = $App->getHTTPParameter("translation", "POST");

$language_id = $_SESSION["language"];
$project_id = $_SESSION['project'];
$language_id = $_SESSION["language"];
$user_id =	$User->userid;

$query = "insert into 
			translations
		  set
		  	string_id = '".addslashes($string_id)."',
		  	language_id = '".addslashes($language_id)."',
		  	value = '".addslashes($translation)."',
		  	userid = '".addslashes($user_id)."',
		  	created_on = NOW()
		  	";




$res = mysql_query($query,$dbh);

/*
$res_file_id = mysql_query("select file_id from files where project_id = '".addslashes($project_id)."' limit 1");
$file_id = mysql_fetch_assoc($res_file_id);
print "000000000000000";
print_r($file_id);
$file_id = $file_id['file_id'];


$res_string_name = mysql_query("select name from strings where string_id = '".addslashes($string_id)."' limit 1");
$string_name = mysql_fetch_assoc($res_string_name);

print "111111111111111111111111111";
print_r($string_name);

$string_name = $file_id['name'];



$res_old_translation = mysql_query("select value from translations where string_id = '".addslashes($string_id)."' order by version desc ");
$old_translation = mysql_fetch_assoc($res_old_translation);
$old_translation = $old_translation['value'];
//$old_translation = ;


$query = "
		INSERT INTO 
			translations 

			SELECT 
				S.string_id, 
				'".addslashes($language_id)."',
				'".addslashes($translation)."' 
			FROM 
				strings AS S 
			inner join 
				files AS F 
			on 
				F.file_id = S.file_id 
			inner join 
				translations AS T 
			on 
				T.string_id = S.string_id 

				
			where 
				F.project_id = '".addslashes($project_id)."' 
			AND 
				F.name = (SELECT files.name FROM files where file_id = '".addslashes($file_id)."'
			AND 
				S.name = '".addslashes($string_name)."'
			AND 
				T.value = '".addslashes($old_translation)."'   
			AND 
				T.is_active = 1
		";

print $query;
 
 */

?>