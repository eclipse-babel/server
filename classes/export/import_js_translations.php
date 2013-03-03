<?php
/*******************************************************************************
 * Copyright (c) 2013 IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Kit Lo (IBM) - [402215] Extract Orion JavaScript files for translation
 *******************************************************************************/

error_reporting(E_ALL); ini_set("display_errors", true);

header("Content-type: text/plain");
include("../../html/global.php");
InitPage("");

$headless = 1;

# See http://wiki.eclipse.org/Babel_/_Large_Contribution_Import_Process
#
# !!  IMPORTANT !!
# Set to 1 unless the translations were authored (and tested/reviewed in context) by professionals
# This doesn't mean all incoming translations will be fuzzy --
# only those that are 'replacing' a non-fuzzy one
$fuzzy = 1;

require(dirname(__FILE__) . "/../file/file.class.php");
require_once("/home/data/httpd/babel.eclipse.org/html/json_encode.php");

$pageTitle = "Babel - Import Translation archive";
$pageKeywords = "import,properties,translation,language,nlpack,pack,eclipse,babel";

$USER = getGenieUser()->userid;

# TODO
$indir = "/tmp/tmp-babel/import";
chdir($indir);
# sub-structure: ./XX/eclipse/plugins/ where XX is the iso code for the language
exec('find . -type f', $lines);
		
# loop through files
foreach ($lines as $line) {
	$line = trim($line);
	# echo $line . "\n";
	if(preg_match("/^\.\/([a-zA-Z0-9._-]+)\/([0-9.]+)\/([a-zA-Z0-9_-]+)\/(.+\.js)$/", $line, $matches)) {
		$PROJECT_ID = $matches[1];
		$VERSION = $matches[2];
		$language = $matches[3];
		$file = $matches[4];
		
		$file_id = 0;
		$language_id = 0;
		
		$SQL = "SELECT F.file_id, L.language_id 
		FROM files AS F, languages AS L WHERE F.is_active = 1 
		AND F.project_id = '" . $PROJECT_ID . "' AND F.version = '" . $VERSION . "'
			AND F.name LIKE '%" . $file . "' AND L.iso_code = '" . $language . "'";
		$rs = mysql_query($SQL, $dbh);
		if($myrow = mysql_fetch_assoc($rs)) {
			$file_id 		= $myrow['file_id'];
			$language_id 	= $myrow['language_id'];
			# echo "  Found file: " . $file_id . "\n";
		} 

		if($file_id > 0 && $language_id > 0) {
			# Get the file contents
			$fh      = fopen($line, 'r');
			$size 	 = filesize($line);

			# echo $file . " - file size: " . $size . " language: " . $language . "\n";
			$content = fread($fh, $size);
			# echo $content . "<br/>";
			fclose($fh);
			$file_contents = ereg_replace("\r\n?", "\n", $content);
			$file_contents = preg_replace("/NON-NLS-(.*)/", "", $file_contents);
			$file_contents = preg_replace("/\\/\\/\\$/", "", $file_contents);
			$file_contents = preg_replace("/((.*?(\n))+.*?)define\(/", "define(", $file_contents);
			$file_contents = preg_replace("/define\(((.*?(\n))+.*?)\)\;/", "$1", $file_contents);
			$jsons = new Services_JSON();
			$lines = $jsons->decode($file_contents);
			foreach($lines as $key => $value) {
							# Get the matching string name
							$SQL = "SELECT s.string_id, s.value, tr.value as tr_last, tr.possibly_incorrect as tr_last_fuzzy, trv.value as ever_tr_value
							FROM strings as s
							left join translations as tr on (s.string_id = tr.string_id
	    					and tr.language_id = $language_id
	    					and tr.is_active)
	    					left join translations as trv on (s.string_id = trv.string_id
	    					and trv.language_id = $language_id
	    					and trv.value = '" . addslashes(unescape($value)) . "')
							WHERE s.is_active = 1 AND s.non_translatable <> 1 AND s.file_id = " . $file_id . " AND s.name = '" . $key . "'";
							$rs_string = mysql_query($SQL, $dbh);
						if ($rs_string) {
							$myrow_string = mysql_fetch_assoc($rs_string);
							if($myrow_string['string_id'] > 0  				# There is an English string   
								 && $value != ""							# With a non-null English value
								 && $myrow_string['ever_tr_value'] == ""	# That's never been translated to this incoming value
								 && $value != $myrow_string['value']  	# And the proposed translation is different from the English value
								 ) {
								$insert_as_fuzzy = 0;
								if($myrow_string['tr_last'] != "" && $fuzzy == 1 && $myrow_string['tr_last_fuzzy'] == 0) {
									# This incoming translation is replacing an existing value that is *not* marked as fuzzy
									# And the $fuzzy == 1, so we may be replacing a known good value !!
									$insert_as_fuzzy = 1;
								}
								else {
									## Nothing. Insert as non-fuzzy.
									## If this is replacing a fuzzy value, then that's a good thing
								}
								# echo "    Language: " . $language . " - Found string with ID: " . $myrow_string['string_id'] . " value: " . $myrow_string['value'] . " never translated to: " . $value . "\n";
								$SQL = "UPDATE translations set is_active = 0 where string_id = " . $myrow_string['string_id'] . " and language_id = '" . $language_id . "'";
								mysql_query($SQL, $dbh);
								$SQL = "INSERT INTO translations (translation_id, string_id, language_id, version, value, possibly_incorrect, is_active, userid, created_on)
								VALUES (
									NULL, " . $myrow_string['string_id'] . ", 
									" . $language_id . ", 0, '" . addslashes(unescape($value)) . "', $insert_as_fuzzy, 1, " . $USER . ", NOW()
								)";
								mysql_query($SQL, $dbh);
								# echo $SQL;
							}
						}
			}
		}
	}
}
echo "Done.\n\n";
?>
