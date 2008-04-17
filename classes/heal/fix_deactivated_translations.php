<?php
/*******************************************************************************
 * Copyright (c) 2007-2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/
//require("global.php");
//InitPage("");

ini_set('memory_limit', '512M');

define('BABEL_BASE_DIR', "../../");
require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

print "fetching translation to heal\n";
$query = "select translation_id,string_id,language_id,created_on,value from translations group by string_id,language_id order by created_on desc";
$res = mysql_query($query);

print "starting to heal the translations\n";

while($row = mysql_fetch_assoc($res)){
	$string_id = $row['string_id'];
	$language_id = $row['language_id'];
	
	$query = "select translation_id from translations where string_id = $string_id and language_id = $language_id and is_active = 1";
	
	$looking = mysql_query($query);
	if(mysql_num_rows($looking) == 0){
//		print "found 0 ".$row['translation_id']."\n";
	}elseif(mysql_num_rows($looking) > 1){
//		print "found == ".mysql_num_rows($looking)." --  translation_id ".$row['translation_id']." string_id ---  ".$row['string_id']."  -- date : ".$row['created_on']."\n".$row['value']."\n";
		
		$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
		$max = mysql_fetch_assoc(mysql_query($query));
		$max = $max['max'];
		$query = "update translations set is_active = 0 where string_id = $string_id and language_id = $language_id and version != $max";
		mysql_query($query);			
		
		$query =  "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";
		mysql_query($query);
	}
}

print "deleting file_progress table data\n";
//drop all the old calced file progress
$query = "delete from file_progress";
mysql_query($query);

print "getting all the file ids and language ids\n";
//get all the files
$query = "select file_id from files";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)){
	$file_ids[] = $row['file_id'];
}
//get all the langs
$query = "select language_id from languages";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)){
	$lang_ids[] = $row['language_id'];
}

print "starting to rebuild the file_progress table\n";
//for each file and lang calc the percent the files has been done
foreach($file_ids as $file_id){
	foreach($lang_ids as $language_id){
		$query = "
				     SELECT IF(COUNT(s.string_id) > 0, (COUNT(t.string_id))/COUNT(s.string_id)*100,0) AS translate_percent
			       FROM files AS f
			         LEFT JOIN strings AS s ON s.file_id = ".$file_id."
			         LEFT JOIN translations AS t ON (s.string_id = t.string_id 
			           AND t.language_id = ".$language_id." AND t.is_active = 1)
			       WHERE f.file_id = ".$file_id."
		";
		$rez = mysql_query($query);
		while($row = mysql_fetch_assoc($rez)){
			$precent = $row['translate_percent'];
		}
		$query = " INSERT INTO file_progress SET file_id = $file_id	,
		   language_id = ".$language_id.",
		   pct_complete = ".$precent."
	    ";
		mysql_query($query);
	}
}

print "cleaning up the file progress of all 0 completed!\n";
//clean up all the pct_complete == 0
$query = "delete from file_progress where pct_complete = 0";
mysql_query($query);

print "done!\n";

/*
 *  OLD CODE
 * 
 * foreach($found as $string_id => $v){
	foreach($v as $language_id => $langs){
		$found_active = 0;
		foreach($langs as $foo => $trans){
			if($trans['is_active'] == 1){
				$found_active++;		
			}
		}
		if(	$found_active == 0){
//			print "0 - $string_id - $language_id<br>\n";
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysql_fetch_assoc(mysql_query($query));
			$max = $max['max'];
			$query = "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";			
			print $query."\n";
//			mysql_query($query);			
			print mysql_error();
			
		}elseif($found_active > 1){
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysql_fetch_assoc(mysql_query($query));
			$max = $max['max'];
			$query = "update translations set is_active = 0 where string_id = $string_id and language_id = $language_id and version != $max";
			print $query."\n";
//			mysql_query($query);			
			print mysql_error();
			
			$query =  "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";
			print $query."\n";
//			mysql_query($query);
			print mysql_error();
			
		}
	}
}
 */


?>