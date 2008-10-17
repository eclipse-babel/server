<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Intalio, Inc. - Initial API and implementation
 *    Antoine Toulme - Initial contribution.
*******************************************************************************/

/*
 * Globals
 */


if(defined('BABEL_BASE_DIR')){
	require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
}else{
    define('BABEL_BASE_DIR', "../../");
    require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
} 

if( !function_exists('json_encode') ){
	require("/home/data/httpd/babel.eclipse.org/html/json_encode.php");
	function json_encode($encode){
 		$jsons = new Services_JSON();
		return $jsons->encode($encode);
	}
}

if (!($ini = @parse_ini_file(BABEL_BASE_DIR . 'classes/base.conf'))) {
	errorLog("Failed to find/read database conf file - aborting.");
	exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
}
  
$context = $ini['context'];
if($context == "") {
	$context = "staging";
}
global $context;

$headlessUserId = 40623;

$dbc = new DBConnection();
global $dbh;
$dbh = $dbc->connect();

echo "Connection established, ready to begin";
$langs = mysql_query( "SELECT language_id FROM languages where languages.is_active" );
while( ($lang_row = mysql_fetch_assoc($langs)) != null ) {
	$language_id = $lang_row['language_id'];
    echo "Investigating language " . $language_id;
	$untranslated_strings = mysql_query( "SELECT * from strings where is_active and value <> '' string_id not in(select string_id from translations where language_id=". $language_id .")" );
    while ( ($string_row = mysql_fetch_assoc($untranslated_strings)) != null) {
		$untranslated_value = $string_row['value'];
		$untranslated_id = $string_row['string_id'];
		$possible_translations = mysql_query( "SELECT t.value from strings As s inner join translations AS t on s.string_id = t.string_id where s.string_id != '" . $untranslated_id . "' and BINARY s.value = '" .$untranslated_value . "' and t.language_id = '" . $language_id . "' ");
       	if ($possible_translations and (($translation_row = mysql_fetch_assoc($possible_translations)) != null)) {
			$translation = $translation_row['value'];
           	$query = "INSERT INTO translations(string_id, language_id, value, userid, created_on) values('". addslashes($untranslated_id) ."','". addslashes($language_id) ."','" . addslashes($translation) . "', '". addslashes($headlessUserId) ."', NOW())";
           	echo $query . "\n";
			mysql_query($query);
		}
    }
}
?>
