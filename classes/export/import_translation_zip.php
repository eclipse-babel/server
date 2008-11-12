<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
*******************************************************************************/

# TODO: this is really rough and needs to be cleaned up
# but it will import translation strings from .properties files into the target project/version you specify below
# It also doesn't overwrite translations that were corrected by users, so you can
# run the import over later with little impact
# sub-structure: ./XX/eclipse/plugins/ where XX is the iso code for the language
# See: http://www.eclipse.org/babel/development/large_contributions.php

# To run this, copy the file to the 'root' of html/ (where translate.php resides)
# and set the project, version and indir, then call the script from a browser, or wget

# See convertiso.sh if the incoming files have various encodings.


header("Content-type: text/plain");
include("global.php");
InitPage("");

$User = getGenieUser();
$headless = 1;


require(BABEL_BASE_DIR . "classes/file/file.class.php");
require_once("json_encode.php");

$pageTitle 		= "Babel - Import Translation archive";
$pageKeywords 	= "import,properties,translation,language,nlpack,pack,eclipse,babel";

$PROJECT_ID = "eclipse";
$VERSION	= "3.3.1";

# TODO
$indir = "/home/droy/Desktop/Babel stuff/Eclipse32translations";
chdir($indir);
# sub-structure: ./XX/eclipse/plugins/ where XX is the iso code for the language
exec('find . -type f', $lines);
		
# loop through files
foreach ($lines as $line) {
	$line = trim($line);
	echo $line . "<br />";
	if(preg_match("/^\.\/([a-zA-Z0-9_-]+)\/(.+\.properties)$/", $line, $matches)) {
		$language = $matches[1];
		$file = $matches[2];
		
		# Find a matching file
		# File: eclipse/plugins/org.eclipse.jem.source/plugin.properties Language: ar
		$file = preg_replace("/eclipse\/plugins\//", "", $file);
		$first_part_file = substr($file, 0, strpos($file, "/"));
		$second_part_file = substr($file, strpos($file, "/"));
		$last_part_file = substr($file, strrpos($file, "/"));
		echo "File: " . $file ." Language: $language\n";
		echo "  first part: " . $first_part_file ." second part: $second_part_file last part: $last_part_file\n";
		$file_id = 0;
		$language_id = 0;
		
		$SQL = "SELECT F.file_id, L.language_id 
		FROM files AS F, languages AS L WHERE F.is_active = 1 
		AND F.project_id = '" . $PROJECT_ID . "' AND F.version = '" . $VERSION . "'
			AND F.name LIKE '%" . $first_part_file . "%' AND F.name LIKE '%" . $second_part_file . "' AND L.iso_code = '" . $language . "'";
		$rs = mysql_query($SQL, $dbh);
		if($myrow = mysql_fetch_assoc($rs)) {
			$file_id 		= $myrow['file_id'];
			$language_id 	= $myrow['language_id'];
			echo "  Found file: " . $file_id . "\n";
		} 
		else {
			$SQL = "SELECT F.file_id, L.language_id 
			FROM files AS F, languages AS L WHERE F.is_active = 1 
			AND F.project_id = '" . $PROJECT_ID . "' AND F.version = '" . $VERSION . "'
				AND F.name LIKE '%" . $first_part_file . "%' AND F.name LIKE '%" . $last_part_file . "' AND L.iso_code = '" . $language . "'";
			$rs = mysql_query($SQL, $dbh);
			if($myrow = mysql_fetch_assoc($rs)) {
				$file_id 		= $myrow['file_id'];
				$language_id 	= $myrow['language_id'];
				echo "  Found file: " . $file_id . "\n";
			}
		} 

		if($file_id > 0 && $language_id > 0) {
			# Get the list of translatable strings
		
			# Get the file contents
			$fh      = fopen($line, 'r');
			$size 	 = filesize($line);
	
			$content = fread($fh, $size);
			# echo $content . "<br/>";
			fclose($fh);
			$previous_line 	= "";
			$lines = explode("\n", $content);

			# Loop through each string
			foreach($lines as $line) {
				# echo "Doing line: " . $line . "<br />";
				if(strlen($line) > 0 && $line[0] != "#" && $line[0] != ";") {
					$line = trim($line);
					
					# Does line end with a \ ?
					if(preg_match("/\\\\$/", $line)) {
						# Line ends with \
						
						# strip the backslash
						$previous_line .= $line . "\n";
					}
					else {
						if($previous_line != "") {
							$line 			= $previous_line . $line;
							$previous_line 	= "";
						}

						$tags = explode("=", trim($line), 2);
						if(count($tags) > 1) {
							$tags[0] = trim($tags[0]);
							$tags[1] = trim($tags[1]);
							# echo "Doing " . $tags[0] . " with value " . $tags[1] . " Unescaped: " . unescape($tags[1]);
							
							# Get the matching string name
							$SQL = "SELECT a.string_id, a.value, b.value as tr_string
							FROM strings as a
							left join translations as b on (b.string_id = a.string_id
	    					and b.language_id = $language_id
	    					and b.value = '" . addslashes(unescape($tags[1])) . "')
							WHERE a.is_active = 1 AND a.file_id = " . $file_id . " AND name = '" . $tags[0] . "'";
							$rs_string = mysql_query($SQL, $dbh);
							$myrow_string = mysql_fetch_assoc($rs_string);
							if($myrow_string['string_id'] > 0 && $myrow_string['tr_string'] == "" && $tags[1] != "") {
								echo "    Found string never translated to this value: " . $myrow_string['string_id'] . " value: " . $myrow_string['value'] . "\n";
								$SQL = "UPDATE translations set is_active = 0 where string_id = " . $myrow_string['string_id'] . " and language_id = '" . $language_id . "'";
								mysql_query($SQL, $dbh);
								$SQL = "INSERT INTO translations (translation_id, string_id, language_id, version, value, possibly_incorrect, is_active, userid, created_on)
								VALUES (
									NULL, " . $myrow_string['string_id'] . ", 
									" . $language_id . ", 0, '" . addslashes(unescape($tags[1])) . "', 0, 1, 13212, NOW()
								)";
								mysql_query($SQL, $dbh);
								# echo $SQL;
							}
						}
					}
				}
			}
		}
		else {
			echo " Cannot find a file for: " .  $line . "\n";
		}
	}
}
echo "Done.\n\n";
?>