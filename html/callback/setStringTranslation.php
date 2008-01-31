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

//print_r($_REQUEST);

print_r($_POST);

$string_id = $App->getHTTPParameter("string_id", "POST");
$translation = $App->getHTTPParameter("translation", "POST");

$language_id = $_SESSION["language"];
$project_id = $_SESSION['project'];
$language_id = $_SESSION["language"];
$version = $_SESSION["version"];

$user_id =	$User->userid;

if($_POST['translate_action'] != "All Versions"){
$query = "update 
			translations 
		  set
			is_active = 0 
		  where 		  	
			string_id = '".addslashes($string_id)."'
		  and
		  	language_id = '".addslashes($language_id)."'
		  and 
		  	is_active = 1
		  ";
$res = mysql_query($query,$dbh);

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

//print $query;

}else{

//FIND ALL STRINGS THAT ARE THE SAME ACROSS VERSIONS

	$query = "select 
				string_id
			  from 
			  	strings,
			  	files 
			  where 
			  	files.file_id = strings.file_id 
			  and 
			  	strings.value = (select value from strings where string_id = '".addslashes($string_id)."')
			  and
				strings.name = (select name from strings where string_id = '".addslashes($string_id)."')		  	
			  and
			  	strings.is_active = 1
			  	";
		  	
	$res = mysql_query($query,$dbh);
	
	while($row = mysql_fetch_assoc($res)){
		$string_ids[] = $row['string_id'];
	}
	
	//GET CURRENT TRANSLATION FOR THIS STRING
	$query= "select value from translations where string_id = '".addslashes($string_id)."' and is_active = 1 order by version limit 1";
	$res = mysql_query($query,$dbh);
	$string_translation = "";
	while($row = mysql_fetch_assoc($res)){
		$string_translation = $row['value'];
	}
	
	//GET ALL STRINGS WITH SAME TRANSLATIONS
	if($string_translation){
		$query	= "
			select 
				translation_id,string_id
			from
				translations
			where
				string_id in (".addslashes(implode(',',$string_ids)).")
			and
				value = '".addslashes($string_translation)."'
			and
			  	is_active = 1
		  ";
		
		$res = mysql_query($query,$dbh);
		while($row = mysql_fetch_assoc($res)){
			//DE-ACTIVATE ALL OLD TRANSLATIONS
			$query = "update translations set is_active = 0 where translation_id = '".addslashes($row['translation_id'])."'";	
			$res2 = mysql_query($query,$dbh);
			
			//INSERT NEW TRANSLATIONS
			$query = "insert into 
					 	translations
					 set
	 					string_id = '".addslashes($row['string_id'])."', 
						language_id = '".addslashes($language)."' , 
						value = '".addslashes($translation)."', 
  						userid = '".addslashes($user_id)."',
				   		created_on  = NOW()
					";
			$res2 = mysql_query($query,$dbh);
			
		}
		
	}else{
		$query	= "
			select 
				strings.string_id
			from
				strings
				left join 
					translations
				on
					strings.string_id = translations.string_id
			and
				translations.value is NULL
			where
				strings.string_id in (".addslashes(implode(',',$string_ids)).")
		";
		
		$res = mysql_query($query,$dbh);
		
		while($row = mysql_fetch_assoc($res)){
			$translation_ids[] = $row['string_id'];
			//INSERT NEW TRANSLATIONS
			$query = "insert into 
					 	translations
					 set
	 					string_id = '".addslashes($row['string_id'])."', 
						language_id = '".addslashes($language)."' , 
						value = '".addslashes($translation)."', 
  						userid = '".addslashes($user_id)."',
				   		created_on  = NOW()
					";
			$res2 = mysql_query($query,$dbh);
		}
		
	}
	
}
	 
?>