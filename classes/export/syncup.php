<?php
/*******************************************************************************
 * Copyright (c) 2008-2012 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Intalio, Inc. - Initial API and implementation
 *    Antoine Toulme - Initial contribution.
 *    Satoru Yoshida - Bug 380524
*******************************************************************************/

// ini_set("memory_limit", "64M");

error_reporting(E_ALL);
ini_set('display_errors', '1');

require(dirname(__FILE__) . "/../system/dbconnection.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

require_once(dirname(__FILE__) . "/../system/backend_functions.php");

if( !function_exists('json_encode') ){
	require("/home/data/httpd/babel.eclipse.org/html/json_encode.php");
	function json_encode($encode){
 		$jsons = new Services_JSON();
		return $jsons->encode($encode);
	}
}

$User = getSyncupUser();

$dbc = new DBConnection();
global $dbh;
$dbh = $dbc->connect();

echo "Connection established. Ready to begin. The syncup user id is: $User->userid\n";

/**
 * Returns possible translation.
 * @param string $untranslated_value
 * @return string
 */
function possible_translation($untranslated_value) {
	global $language_id;
	#candidate may not be found if new line is there.
	$untranslated_value = rtrim($untranslated_value, "\n\r");
	# BINARY the lookup value instead of the field to support an index.
	# is_active is not used in consideration of case to reuse.
	$rs = mysql_query( "SELECT string_id FROM strings WHERE value = BINARY '" . addslashes($untranslated_value) . "'");
	if ($rs === false) {
		return NULL;
	}
	$string_ids_tmp = array();
	while ( ($row = mysql_fetch_assoc($rs)) != null) {
		$string_ids_tmp[] = $row['string_id'];
	}
	$string_ids = implode(',',$string_ids_tmp);
	if ($string_ids === '') {
		return NULL;
	}
	#if SQL result has many records, last created record will be used.
	# s.is_active is not used in consideration of case to reuse.
	$rs2 = mysql_query( "SELECT t.created_on, t.value from strings As s inner join translations AS t on s.string_id = t.string_id where s.string_id IN ($string_ids) and t.language_id = '" . $language_id . "' and t.is_active and s.value <> BINARY t.value order by created_on DESC");
	if ($rs2 and (($translation_row = mysql_fetch_assoc($rs2)) != null)) {
		return $translation_row['value'];
	}
	return null;
}

$language_result = mysql_query( "SELECT language_id, iso_code, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name FROM languages WHERE languages.is_active AND languages.language_id<>1 ORDER BY name ASC" );
while( ($language_row = mysql_fetch_assoc($language_result)) != null ) {
	$language_name = $language_row['name'];
	$language_iso = $language_row['iso_code'];
	$language_id = $language_row['language_id'];
	echo "\nInvestigating $language_name ($language_iso) (language_id=$language_id)\n";
	#In performance purpose, the SQL sorts a temporary table, TEMP.
	$untranslated_strings = mysql_query( "SELECT * FROM (SELECT string_id, value from strings where is_active and non_translatable = 0 and value <> '' and string_id not in(select string_id from translations where language_id=$language_id) ) AS TEMP order by value" );
	$count = 0;
	$prev_value = '';
    while ( ($string_row = mysql_fetch_assoc($untranslated_strings)) != null) {
    	$count++;

    	if($count % 10000 == 0) {
    		echo "Processed " . $count . " strings...\n";
    	}

		$untranslated_value = $string_row['value'];
		$untranslated_id = $string_row['string_id'];

		/*
		 * it sets new translation only when new value is detected.
		 * The if statement requires to be sorted by strings.value
		 */
		if ($untranslated_value !== $prev_value) {
			$translation = possible_translation($untranslated_value);
			$prev_value  = $untranslated_value;
		}

       	if ($translation !== null) {
           	$query = "INSERT INTO translations(string_id, language_id, value, userid, created_on, possibly_incorrect) values('". addslashes($untranslated_id) ."','". addslashes($language_id) ."','" . addslashes($translation) . "', '". addslashes($User->userid) ."', NOW(), 1)";
           	echo "\tTranslating ". addslashes($untranslated_id) ." with: " . addslashes($translation) . "\n";
			mysql_query($query);
		}
    }
}
echo "\nDone\n";
?>
