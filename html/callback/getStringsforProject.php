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

$project_id = $App->getHTTPParameter("proj", "POST");
$version = $App->getHTTPParameter("version", "POST");

//THE 3 VALID STATES
//	UNSTRANSLATED (DEFUALT)
//	FLAGGED (FLAGGED INCORRECT IN DATABASE)
//	AWAITING (TRANSLATED BUT NO RAITINGS YET)
$state = $App->getHTTPParameter("state", "POST"); 

if(!$proj_post){
	$project_id = $_SESSION['project'];
	$version =  $_SESSION['version'];
	$language =  $_SESSION['language'];
}
switch($state){
	case "flagged" :
	break;
	$query = "select 
				strings.value as string,
				translations.value as translation
			  from 
			  	strings,
			  	files
			  	left join translations on
			  		translations.language_id = '".addslashes($language)."'
			  	  and
			  		string_id = translations.string_id
			  where 
			  	strings.is_active != 0 
			  and 
			  	strings.file_id = files.file_id 
			  and 
				files.project_id = '".addslashes($project_id)."'
			  and	
				files.version = '".addslashes($version)."'
			";
	case "translated" :
	break;
	$query = "select 
				strings.value as string,
				translations.value as translation
			  from 
			  	strings,
			  	files
			  	left join translations on
			  		translations.language_id = '".addslashes($language)."'
			  	  and
			  		string_id = translations.string_id
			  where 
			  	strings.is_active != 0 
			  and 
			  	strings.file_id = files.file_id 
			  and 
				files.project_id = '".addslashes($project_id)."'
			  and	
				files.version = '".addslashes($version)."'
			";
	
	case "untranslated" :
	default:
		$query = "select 
					strings.string_id as stringId,
					strings.value as text,
					strings.created_on as createdOn,
					translations.value as translationString,
					max(translations.version)
				from 
				  	strings
				  	
				  	left join files on
					  	files.project_id = '".addslashes($project_id)."'
				  	
				  	left join translations on (
				  		translations.language_id = '".addslashes($language)."'
				  	  and
				  		translations.string_id  = strings.string_id
				  	)
				  where 
				  	strings.is_active != 0 
				  and 
				  	strings.file_id = files.file_id 
				  and 
					files.project_id = '".addslashes($project_id)."'
				  and	
					files.version = '".addslashes($version)."'
  				  group by strings.string_id,translations.version desc
				";

		
		$query = "select 
					strings.string_id as stringId,
					strings.value as text,
					strings.created_on as createdOn,
					translations.value as translationString,
					max(translations.version)
				from 
				  	strings
				  	
				  	left join project_versions on
					  	project_versions.project_id = '".addslashes($project_id)."'
				  	
				  	left join translations on (
				  		translations.language_id = '".addslashes($language)."'
				  	  and
				  		translations.string_id  = strings.string_id
				  	)
				  where 
				  	strings.is_active != 0 
				  and 
					project_versions.project_id = '".addslashes($project_id)."'
				  and	
					project_versions.version = '".addslashes($version)."'
					
  				  group by strings.string_id,translations.version desc
				";
		
		
//				  	translations.string_id is null
//				  and
		
		
		
		$query = "select 
					strings.string_id as stringId,
					strings.value as text,
					strings.created_on as createdOn,
					translations.value as translationString,
					max(translations.version)
				from 
					files,
				  	strings
				  	
				  	left join translations on (
				  		translations.language_id = '".addslashes($language)."'
				  	  and
				  		translations.string_id  = strings.string_id
				  	)
				  where 
				  	strings.is_active = 1 
				  and 
					files.file_id = strings.file_id
				  and	
					files.version = '".addslashes($version)."'
				  and 
					files.project_id = '".addslashes($project_id)."'
							
  				  group by strings.string_id,translations.version desc
				";
		
	
}

//print $query."<br>";

$res = mysql_query($query,$dbh);

//print mysql_error();

$stringids = Array();
$return = Array();
while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
    if($stringids[$line['stringId']] ){
 		  continue;
    }else{
		$return[] = $line;
		$stringids[$line['stringId']] = 1;
    }
}

print json_encode($return);
exit();

//	$return .= "<tr>";
//	$return .= "<td><a href='?string_id=".$line['string']."'>".$line['string']."</a></td>";
//	$return .= "<td>".$line['translation']."</td>";
//	$return .= "</tr>";
//<table id='string-choices'>
//	<?=$return;?>
//</table>


?>