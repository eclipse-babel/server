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

ini_set("memory_limit", "512M");
require(dirname(__FILE__) . "/../system/dbconnection.class.php");

# Get all release trains
$dbc = new DBConnection();
$dbh = $dbc->connect();
$result = mysql_query("SELECT * FROM release_trains ORDER BY train_version DESC");
$train_result = array();
while ($train_row = mysql_fetch_assoc($result)) {
  $train_result[$train_row['train_id']] = $train_row['train_version'];
}

$options = getopt("b:t:");
$argv_train = "";
if (isset($options['t'])) {
	$argv_train = $options['t'];
	if( array_key_exists($argv_train, $train_result)) {
		# Picked a valid train, remove all others
		foreach ($train_result as $train_id => $train_version) {
			if ($train_id != $argv_train) {
				unset($train_result[$train_id]);
			}
		}
	}
}

$build_id = "";
if (!isset($options['b'])) {
	usage();
	exit();
} else {
	$build_id = $options['b'];
}

global $addon;
$work_dir = $addon->callHook('babel_working');
$context = $addon->callHook('context');
$work_context_dir = $work_dir . $context . "/";
$orion_language_packs_dir = $work_context_dir . "orion_language_packs/";
$babel_language_packs_dir = $work_context_dir . "babel_language_packs/";
$source_files_dir = dirname(__FILE__) . "/source_files_for_orion/";

$leader = ". ";
$timestamp = date("Ymdhis");

$rm_command = "rm -rf $orion_language_packs_dir";
exec($rm_command);

# Loop through the trains
foreach ($train_result as $train_id => $train_version) {
	echo "Generating language packs for: $train_id\n";

	$orion_language_packs_dir_for_train = $orion_language_packs_dir . $train_id . "/";
	exec("mkdir -p $orion_language_packs_dir_for_train");
	
	$babel_language_packs_dir_for_train = $babel_language_packs_dir . $train_id . "/";
	exec("mkdir -p $babel_language_packs_dir_for_train");

	$train_version_timestamp = "$train_version.v$timestamp";
	$language_pack_links_file_buffer = "";
	$site_xml = "";

	$sql = "SELECT language_id, iso_code, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name, IF(locale <> '', CONCAT(name, locale), name) as name_no_space, is_active, IF(language_id = 1,1,0) AS sorthack FROM languages ORDER BY sorthack, name ASC";
	$language_result = mysql_query($sql);
	if($language_result === FALSE) {
		# We may have lost the database connection with our shell-outs, reconnect
		$dbh = $dbc->connect();
		$language_result = mysql_query($sql);
	}
	while (($language_row = mysql_fetch_assoc($language_result)) != null) {
		$language_name = $language_row['name'];
		$language_name_no_space = $language_row['name_no_space'];
		$language_iso = $language_row['iso_code'];
		$language_id = $language_row['language_id'];
		if (strcmp($language_iso, "en") == 0) {
			$language_name = "Pseudo Translations";
			$language_name_no_space = "PseudoTranslations";
			$language_iso = "en_AA";
		}
		$language_iso_web = str_replace("_", "-", strtolower($language_iso));

		echo "${leader}Generating language pack for: $language_name ($language_iso) (language_id=" . $language_id . ")\n";

		# Determine which plug-ins need to be in this language pack
		if (strcmp($language_iso, "en_AA") == 0) {
			$file_result = mysql_query("SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE f.is_active
				AND f.project_id = 'eclipse.orion'
				AND v.train_id = '" . $train_id . "'");
		} else {
			$file_result = mysql_query("SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE f.is_active
				AND f.project_id = 'eclipse.orion'
				AND v.train_id = '" . $train_id . "'");
		}

		$plugins = array();
		$projects = array();
		$project_versions = array();
		$pseudo_translations_indexes = array();
		while (($file_row = mysql_fetch_assoc($file_result)) != null) {
			# parse plugin name, dir name, file name
			$pattern = 
				'/^
				(.*?)?					# $1 plugin name
				\/						# slash
				(.*?\/)?				# $2 dir name
				([^\/]+[.]js)			# $3 file name
				$/ix';
			$plugin_name_string = preg_replace($pattern, '$1', $file_row['name']);
			$dir_name_string = preg_replace($pattern, '$2', $file_row['name']);
			$file_name_string = preg_replace($pattern, '$3', $file_row['name']);

			# Generate dir name in language pack
			$dir_name_string = str_replace("web/", "", $dir_name_string);
			$dir_name_string = str_replace("nls/root/", "nls/" . $language_iso_web . "/", $dir_name_string);

			$file_row['plugin_name'] = $plugin_name_string;
			$file_row['dir_name'] = $dir_name_string;
			$file_row['file_name'] = $file_name_string;

			$plugins[$plugin_name_string][] = $file_row;
		}

		# Generate one plug-in fragment for each plug-in
		ksort($plugins);
		foreach ($plugins as $plugin_name => $plugin_row) {
			echo "${leader}${leader}Generating plug-in fragment: $plugin_name\n";

			foreach ($plugin_row as $properties_file) {
				$project_id = $properties_file['project_id'];
				$dirname = $properties_file['dir_name'];
				$filename = $properties_file['file_name'];
				$each_language_pack_dir = "BabelLanguagePack-$project_id-${language_iso}/";

				echo "${leader}${leader}${leader}Generating file: " . $plugin_name . "/" . $dirname . $filename . " (file_id=" . $properties_file['file_id'] . ")\n";
				# Create any needed sub-directories
				exec("mkdir -p " . $orion_language_packs_dir_for_train . $each_language_pack_dir . $plugin_name . "/" . $dirname);
				$fullpath = $orion_language_packs_dir_for_train . $each_language_pack_dir . $plugin_name . "/" . $dirname . $filename;
				$outp = fopen($fullpath, "w");
				fwrite($outp, "// Copyright by many contributors; see http://babel.eclipse.org/\ndefine({");
				$line_leader = null;
				if (strcmp($language_iso, "en_AA") == 0) {
					$sql = "SELECT string_id, name AS 'key', value FROM strings WHERE file_id = " . $properties_file['file_id'] .
						" AND is_active AND non_translatable = 0";
					$strings_result = mysql_query($sql);
					while (($strings_row = mysql_fetch_assoc($strings_result)) != null) {
						if ($line_leader == null) {
							fwrite($outp, "\n  ");
							$line_leader = ",\n  ";
						} else {
							fwrite($outp, $line_leader);
						}
				        $outp_line = "\"" . $strings_row['key'] . "\": \"" . $properties_file['project_id'] . $strings_row['string_id'] .
								":" . $strings_row['value'] . "\"";
						fwrite($outp, $outp_line);

						$value = htmlspecialchars($strings_row['value']);
						if (strlen($value) > 100) {
							$value = substr($value, 0, 100) . " ...";
						}
						$pseudo_translations_indexes[$properties_file['project_id']][] = "\n\t\t<li><a href=\"http://babel.eclipse.org/babel/translate.php?project=" .
						$properties_file['project_id'] . "&version=" . $properties_file['version'] . "&file=" .
						$properties_file['name'] . "&string=" . $strings_row['key'] . "\">" .
						$properties_file['project_id'] . $strings_row['string_id'] . "</a>&nbsp;" . $value . "</li>";
					}
				} else {
					$sql = "SELECT
						strings.name AS 'key', 
						translations.value AS trans
						FROM strings, translations
						WHERE strings.string_id = translations.string_id
						AND strings.file_id = " . $properties_file['file_id'] . "
						AND strings.is_active
						AND strings.non_translatable = 0
						AND translations.language_id = " . $language_id . "
						AND translations.is_active";
					$strings_result = mysql_query($sql);
					while (($strings_row = mysql_fetch_assoc($strings_result)) != null) {
						if ($line_leader == null) {
							fwrite($outp, "\n  ");
							$line_leader = ",\n  ";
						} else {
							fwrite($outp, $line_leader);
						}
						fwrite($outp, "\"" . $strings_row['key'] . "\": \"" . $strings_row['trans'] . "\"");
					}
				}
				/*
				 * Finish the properties file
				 */
				fwrite($outp, "\n});");
				fclose($outp);
				echo "${leader}${leader}${leader}Completed  file: " . $plugin_name . "/" . $dirname . $filename . "\n";
			}
			echo "${leader}${leader}Completed  plug-in fragment: $plugin_name\n";

			$parent_plugin_id = $plugin_name;
			$project_id = $properties_file['project_id'];
			$fragment_id = "$parent_plugin_id.nl_$language_iso";
			$fragment_filename = "${fragment_id}_$train_version_timestamp.jar";

			$projects[$project_id][] = $fragment_id;
			$project_versions[$project_id] = $properties_file['version'];
		}
		ksort($projects);
		foreach ($projects as $project_id => $fragment_ids) {
			/*
			 * Sort fragment names
			 */
			asort($fragment_ids);
			/*
			 * Copy in the build files
			 */
			exec("cp -r ${source_files_dir}* $orion_language_packs_dir_for_train$each_language_pack_dir");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; rename Plugin ${language_name_no_space}Plugin *.html");

			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[locale\]/$language_iso_web/g\" babelEditor*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelEditor*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[locale\]/$language_iso_web/g\" babelGit*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelGit*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[locale\]/$language_iso_web/g\" babelUi*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUi*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[locale\]/$language_iso_web/g\" babelUsers*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUsers*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[language\]/$language_name/g\" babelEditor*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelEditor*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[language\]/$language_name/g\" babelGit*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelGit*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[language\]/$language_name/g\" babelUi*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUi*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[language\]/$language_name/g\" babelUsers*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUsers*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[build_id\]/$build_id/g\" babelEditor*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelEditor*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[build_id\]/$build_id/g\" babelGit*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelGit*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[build_id\]/$build_id/g\" babelUi*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUi*Plugin.html");
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; sed -e \"s/\[build_id\]/$build_id/g\" babelUsers*Plugin.html > /tmp/babelfile.$$; mv -f /tmp/babelfile.$$ babelUsers*Plugin.html");
			/*
			 * Copy in the Babel Pseudo Translations Index file
			 */
			if (strcmp($language_iso, "en_AA") == 0) {
				$pseudo_translations_index_file = fopen($orion_language_packs_dir_for_train . $each_language_pack_dir . "BabelPseudoTranslationsIndex-$project_id.html", "w");
				fwrite($pseudo_translations_index_file, "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">" .
					"\n<html>\n<head>\n<title>Babel Pseudo Translations Index for $project_id</title>" .
					"\n<meta http-equiv=Content-Type content=\"text/html; charset=UTF-8\">\n</head>" .
					"\n<body>\n\t<h1>Babel Pseudo Translations Index for $project_id</h1>" .
					"\n\t<h2>Version: $train_version_timestamp</h2>\n\t<ul>");
				foreach ($pseudo_translations_indexes[$project_id] as $index) {
					fwrite($pseudo_translations_index_file, $index);
				}
				fwrite($pseudo_translations_index_file, "\n\t</ul>\n</div></div></body>\n</html>");
				fclose($pseudo_translations_index_file);
			}
			/*
			 * Zip up language pack
			 */
			$language_pack_name = "BabelLanguagePack-$project_id-${language_iso}_$train_version_timestamp.zip";
			exec("cd $orion_language_packs_dir_for_train$each_language_pack_dir; zip -r $babel_language_packs_dir_for_train$language_pack_name *");
			/*
			 * Save and do not delete $orion_language_packs_dir_for_train$each_language_pack_dir
			 */
			/* exec("rm -rf $orion_language_packs_dir_for_train$each_language_pack_dir"); */
		}  /* End: foreach project  */
		echo "${leader}Completed  language pack for: $language_name ($language_iso)\n";
	}

	$dbh = $dbc->disconnect();
	echo "Completed  language packs for: $train_id\n";
}

function usage() {
	echo "\n";
	echo "generate_orion.php -b <build_id> [-t <train_id>]\n";
	echo "  -b <build_id>: The Build ID for this build\n";
	echo "  -t <train_id>: Optional: train to build (indigo, helios, galileo, ganymede, europa)";
	echo "\n";
}

?>