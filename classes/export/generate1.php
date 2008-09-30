<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
 *    Motoki MORT mori-m@mxa.nes.nec.co.jp - patch, bug 227366
 *    Kit Lo (IBM) - patch, bug 217339, generate pseudo translations language packs 
 *******************************************************************************/

/*
 * Documentation: http://wiki.eclipse.org/Babel_/_Server_Tool_Specification#Outputs
 */

ob_start();
ini_set("memory_limit", "12M");
define("BABEL_BASE_DIR", "../../");
require(BABEL_BASE_DIR."html/common_functions.php");
require(BABEL_BASE_DIR."classes/system/dbconnection.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

$work_dir = "/home/babel-working/";
if (!($ini = @parse_ini_file(BABEL_BASE_DIR."classes/base.conf"))) {
	errorLog("Failed to find/read database conf file - aborting.");
	exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
}
$context = $ini['context'];

$work_context_dir = $work_dir.$context."/";
$tmp_dir = $work_context_dir."tmp/";
$output_dir = $work_context_dir."output/";
$source_files_dir = "source_files_for_generate/";

$leader = ". . ";
$timestamp = date("Ymdhis");

exec("rm -rf $work_dir*");
exec("mkdir $work_context_dir");
exec("mkdir $output_dir");

echo "Generating update site\n";
$train_result = mysql_query('SELECT DISTINCT train_id FROM release_train_projects');
while (($train_row = mysql_fetch_assoc($train_result)) != null) {
	$train_id = $train_row['train_id'];
	$train_version = "3.4.0";
	if (strcmp($train_id, "europa") == 0) {
		$train_version = "3.3.0";
	}
	$site_xml = '';

	$output_dir_for_train = $output_dir . $train_row['train_id'] . "/";
	exec("mkdir $output_dir_for_train");
	exec("mkdir ${output_dir_for_train}features/");
	exec("mkdir ${output_dir_for_train}plugins/");

	$language_result = mysql_query('SELECT * FROM languages WHERE languages.is_active');
	while( ($language_row = mysql_fetch_assoc($language_result)) != null ) {
		$language_name = $language_row['name'];
		$language_iso = $language_row['iso_code'];
		$language_locale = $language_row['locale'];
		$language_id = $language_row['language_id'];
		if (strcmp($language_iso, "en") == 0) {
			$language_iso = "en_AA";
			$language_name = "Pseudo Translations";
		}
		if ($language_locale != null) {
			$language_name = $language_locale . " " . $language_name;
		}
		echo "${leader}Generating language pack for $train_id - $language_name ($language_iso) (language_id=" . $language_id . ")\n";

		/*
		 * Determine which plug-ins need to be in this language pack.
		 */
		if (strcmp($language_iso, "en_AA") == 0) {
			$file_result = mysql_query("SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE f.is_active
				AND v.train_id = '" . $train_row['train_id'] . "'");

			$index_file = fopen("${output_dir}BabelPseudoTranslationsIndex.html", "w");
			fwrite($index_file, "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n<html>\n<head>\n<title>Babel Pseudo Translations Index</title>\n" .
				"<meta http-equiv=Content-Type content=\"text/html; charset=UTF-8\">\n</head>\n<body>\n\t<h1>Babel Pseudo Translations Index</h1>\n" .
				"\t<h2>" . $train_version . ".v" . $timestamp . "</h2>\n\t<ul>\n");
		} else {
			$file_result = mysql_query("SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN translations AS t ON (s.string_id = t.string_id AND t.is_active)
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE t.language_id = " . $language_id . "
				AND f.is_active
				AND v.train_id = '" . $train_row['train_id'] . "'");
		}

		$plugins = array();
		while (($file_row = mysql_fetch_assoc($file_result)) != null) {
			# save original filename
			$file_row['origname'] = $file_row['name'];

			# strip useless CVS structure before the plugin name (bug 221675 c14):
			$pattern = '/^([a-zA-Z0-9\/_-])+\/([a-zA-Z0-9_-]+)\.([a-zA-Z0-9_-]+)(.*)\.properties$/i';
			$replace = '${2}.${3}${4}.properties';
			$file_row['name'] = preg_replace($pattern, $replace, $file_row['name']);

			# strip source folder (bug 221675) (org.eclipse.plugin/source_folder/org/eclipse/plugin)
			$pattern = '/^([a-zA-Z0-9_-]+)\.([a-zA-Z0-9_-]+)\.([a-zA-Z0-9\._-]+)(.*)\/(\1)([\.\/])(\2)([\.\/])(.*)\.properties$/i';
			$replace = '${1}.${2}.${3}/${5}${6}${7}${8}${9}.properties';
			$file_row['name'] = preg_replace($pattern, $replace, $file_row['name']);

			if (preg_match("/^([a-zA-Z0-9\.]+)\/(.*)$/", $file_row['name'], $matches)) {
				$file_row['subname'] = $matches[2];
				$plugins[$matches[1]][] = $file_row;
			} else {
				echo "  WARNING: no plug-in name found in file " . $file_row['file_id'] . " \"" . $file_row['name'] . "\"\n";
			}
		}

		/*
		 * Generate one plug-in fragment for each plug-in
		 */
		foreach ($plugins as $plugin_name => $plugin_row) {
			echo "${leader}${leader}Generating plug-in fragment $plugin_name\n";
			/*
			 * Clean and create the temporary directory
			 */
			if (file_exists($tmp_dir)) {
				exec("rm -rf $tmp_dir; mkdir $tmp_dir");
			} else {
				exec("mkdir $tmp_dir");
			}

			/*
			 * Generate each *.properties file
			 */
			foreach ($plugin_row as $properties_file) {
				/*
				 * Convert the filename to *_lang.properties, e.g., foo_fr.properties
				 */
				$filename = $properties_file['subname'];
				if (preg_match( "/^(.*)\.properties$/", $filename, $matches)) {
					$filename = $matches[1] . '_' . $language_iso . '.properties';
				}
				echo "${leader}${leader}${leader}Generating properties file $filename (file_id=" . $properties_file['file_id'] . ")\n";
				/*
				 * Create any needed sub-directories
				 */
				$fullpath =  $tmp_dir . $filename;
				preg_match("/^((.*)\/)?(.+?)$/", $fullpath, $matches);
				exec("mkdir -p \"" . $matches[1] . "\"");
				/*
				 * Start writing to the file
				 */
				$outp = fopen($fullpath, "w");
				fwrite($outp, "# Copyright by many contributors; see http://babel.eclipse.org/\n");
				if (strcmp($language_iso, "en_AA") == 0) {
					$sql = "SELECT string_id, name, value FROM strings WHERE file_id = " . $properties_file['file_id'] .
						" AND is_active AND non_translatable = 0";
					$strings_result = mysql_query($sql);
					while (($strings_row = mysql_fetch_assoc($strings_result)) != null) {
						fwrite($outp, $strings_row['name'] . "=");
						fwrite($outp, $properties_file['project_id'] . $strings_row['string_id'] . ":" . $strings_row['value']);
						fwrite($outp, "\n");

						$value = htmlspecialchars($strings_row['value']);
						if (strlen($value) > 100) {
							$value = substr($value, 0, 100) . " ...";
						}
						fwrite($index_file, "\t\t<li><a href=\"http://babel.eclipse.org/babel/translate.php?project=" . $properties_file['project_id'] .
							"&version=" . $properties_file['version'] . "&file=" . $properties_file['origname'] . "&string=" . $strings_row['name'] .
							"\">" . $properties_file['project_id'] . $strings_row['string_id'] . "</a>&nbsp;" . $value . "</li>\n");
					}
				} else {
					$sql = "SELECT
						strings.name AS 'key', 
						strings.value AS orig, 
						translations.value AS trans
						FROM strings, translations
						WHERE strings.string_id = translations.string_id
						AND translations.language_id = " . $language_id . "
						AND strings.file_id = " . $properties_file['file_id'] . "
						AND translations.is_active";
					$strings_result = mysql_query($sql);
					while (($strings_row = mysql_fetch_assoc($strings_result)) != null) {
						fwrite($outp, $strings_row['key'] . "=");
						#echo "${leader1S}${leaderS}${leaderS}${leaderS}" . $strings_row['key'] . "=";
						if ($strings_row['trans']) {
							# json_encode returns the string with quotes fore and aft.  Need to strip them.
							# $tr_string = preg_replace('/^"(.*)"$/', '${1}', json_encode($strings_row['trans']));
							# $tr_string = str_replace('\\\\', '\\', $tr_string);
							$tr_string = toescapedunicode($strings_row['trans']);
							fwrite($outp, $tr_string);
							# echo $strings_row['trans'];
						} else {
							fwrite($outp, $strings_row['orig']);
						}
						fwrite($outp, "\n");
					}
				}
				/*
				 * Finish the properties file
				 */
				fclose($outp);
				echo "${leader}${leader}${leader}Completed  properties file $filename\n";
			}
			/*
			 * Copy in the various legal files
			 */
			exec("cp ${source_files_dir}about.html ${tmp_dir}");
			/*
			 * Generate the META-INF/MANIFEST.MF file
			 */
			$parent_plugin_id = $plugin_name;
			$fragment_id = "${parent_plugin_id}.nl_$language_iso";
			$fragment_version = $train_version . ".v" . $timestamp;
			$fragment_filename = $fragment_id . "_" . $fragment_version . ".jar";

			$plugins[$plugin_name]['id'] = $fragment_id;
			$plugins[$plugin_name]['version'] = $fragment_version;

			exec("mkdir $tmp_dir/META-INF" );
			$outp = fopen("$tmp_dir/META-INF/MANIFEST.MF", "w");
			fwrite($outp, "Manifest-Version: 1.0\n");
			fwrite($outp, "Bundle-Name: $parent_plugin_id $language_name NLS Support\n");
			fwrite($outp, "Bundle-SymbolicName: $fragment_id ;singleton=true\n");
			fwrite($outp, "Bundle-Version: $fragment_version\n");
			fwrite($outp, "Bundle-Vendor: Eclipse Foundation Inc.\n");
			fwrite($outp, "Fragment-Host: $parent_plugin_id\n");
			fclose($outp);
			/*
			 * Jar up this directory as the fragment plug-in jar
			 */
			system("cd $tmp_dir; jar cfM ${output_dir_for_train}plugins/$fragment_filename .");
			echo "${leader}${leader}Completed  plug-in fragment $plugin_name\n";
		}
		/*
		 * Clean and create the temporary directory
		 */
		if (file_exists($tmp_dir)) {
			exec("rm -rf $tmp_dir; mkdir $tmp_dir");
		} else {
			exec("mkdir $tmp_dir");
		}
		/*
		 * Create the feature.xml
		 *
		 * TODO <url><update label=... url=... and <url><discovery label=... url=... are not implemented
		 *
		 * <url>
		 *   <update label="%updateSiteName" url="http://update.eclipse.org/updates/3.2" />
		 *   <discovery label="%updateSiteName" url="http://update.eclipse.org/updates/3.2" />
		 * </url>
		 */
		$feature_id = "org.eclipse.babel.nls.$language_iso";
		$feature_version = $train_version . ".v" . $timestamp;
		$feature_filename = $feature_id . "_" . $feature_version . ".jar";

		$outp = fopen("$tmp_dir/feature.xml", "w");
		fwrite($outp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<feature
	id=\"$feature_id\"
	label=\"Eclipse Language Pack for $language_name\"
	image=\"eclipse_update_120.jpg\"
	provider-name=\"Eclipse Foundation Inc.\"
	version=\"$feature_version\">
	<license url=\"license.html\">\n" . htmlspecialchars(file_get_contents("${source_files_dir}license.txt")) . "\t</license>
	<description>Translations in $language_name for all Eclipse Projects</description>" );
		foreach ($plugins as $plugin_name => $plugin_row) {
			fwrite($outp, '
	<plugin fragment="true" id="' .
			$plugin_row['id'] . '" unpack="false" version="' .
			$plugin_row['version'] . '"/>');
		}
		fwrite($outp, '
</feature>');
		fclose($outp);
		/*
		 * Copy in the various legal files
		 */
		exec("cp ${source_files_dir}about.html ${tmp_dir}");
		exec("cp ${source_files_dir}license.html ${tmp_dir}");
		exec("cp ${source_files_dir}epl-v10.html ${tmp_dir}");
		exec("cp ${source_files_dir}eclipse_update_120.jpg ${tmp_dir}");
		/*
		 * Copy in the Babel Pseudo Translations Index file
		 */
		if (strcmp($language_iso, "en_AA") == 0) {
			fwrite($index_file, "\t</ul>\n</body>\n</html>");
			fclose($index_file);
			exec("cp ${output_dir}BabelPseudoTranslationsIndex.html ${tmp_dir}");
			exec("rm ${output_dir}BabelPseudoTranslationsIndex.html");
		}
		/*
		 * Jar up this directory as the feature jar
		 */
		system("cd $tmp_dir; jar cfM ${output_dir_for_train}features/$feature_filename .");
		/*
		 * Register this feature with the site.xml
		 */
		$site_xml .= "	<feature url=\"features/$feature_filename\" id=\"$feature_id\" version=\"$feature_version\">
		<category name=\"Language Packs\"/>
	</feature>\n";
		echo "${leader}Completed language pack for $language_name ($language_iso)\n";
	}
	/*
	 * <site mirrorsURL=... implemented in the weekly build process by sed'ing <site>
	 */
	$outp = fopen("${output_dir_for_train}site.xml", "w");
	fwrite($outp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<site>
	<description url=\"http://babel.eclipse.org/\">
		This update site contains user-contributed translations of the strings in all Eclipse projects.
		Please see the http://babel.eclipse.org/ Babel project web pages for a full how-to-use explanation of
		these translations as well as how you can contribute to the translations of this and future versions of Eclipse.
	</description>
	<category-def name=\"Language Packs\" label=\"Language Packs\">
		<description>Language packs for all Eclipse projects</description>
	</category-def>\n");
	fwrite($outp, $site_xml);
	fwrite($outp, "</site>");
	fclose($outp);
}
echo "Completed generating update site\n";

/*
 2. what happens if the translation feature includes plug-in fragments for
 plug-ins that are not in the current image?
 does it load correctly and ignore those fragments? if so, good
 A: warnings appear in the run-time error log
 does it fail to load? if so, then we need to generate different features, perhaps
 one feature for each plug or else we need to know more about the project
 distro structure to know which plug-ins to put in each feature
 what happens if those plug-ins are later added - does it load the strings now?
 A: probably not
 3. need to handle different versions of each feature/plugin/platform; generate different
 language packs for each
 */

$alloutput = fopen($output_dir."langpack_output_".date("m_d_Y"), "w");
fwrite($alloutput,ob_get_contents());
?>