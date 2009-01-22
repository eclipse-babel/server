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
 *    Kit Lo (IBM) - patch, bug 234430, need language packs by means of other than update site
 *    Kit Lo (IBM) - patch, bug 251536, newline char missing after copyright comment on first line
 *    Kit Lo (IBM) - patch, bug 238580, language packs should not include strings that are marked "non-translatable"
 *    Kit Lo (IBM) - patch, bug 252140, Illegal token characters in babel fragment names
 *    Antoine Toulme (Intalio, Inc) - patch, bug 256430, Fragments with no host jeopardize Eclipse installation
 *    Kit Lo (IBM) - patch, bug 261739, Inconsistent use of language names
 *******************************************************************************/

/*
 * Documentation: http://wiki.eclipse.org/Babel_/_Server_Tool_Specification#Outputs
 */
define("METADATA_GENERATOR_LOCATION", "/home/genie/eclipse"); // you might want to read this value from a config file. Not sure yet.

ini_set("memory_limit", "64M");
define("BABEL_BASE_DIR", "../../");
require(BABEL_BASE_DIR . "html/common_functions.php");
require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

$work_dir = "/home/babel-working/";
if (!($ini = @parse_ini_file(BABEL_BASE_DIR . "classes/base.conf"))) {
	errorLog("Failed to find/read database conf file - aborting.");
	exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
}
$context = $ini['context'];

$work_context_dir = $work_dir . $context . "/";
$tmp_dir = $work_context_dir . "tmp/";
$babel_language_packs_dir = $work_context_dir . "babel_language_packs/";
$output_dir = $work_context_dir . "output/";
$source_files_dir = "source_files_for_generate/";

# Language pack URL leader, to enable mirrors on download.eclipse.org
$language_pack_leader = "";
if($context == "live") {
	$language_pack_leader = "http://www.eclipse.org/downloads/download.php?r=1&file=/technology/babel/babel_language_packs/";
}

$leader = ". . ";
$timestamp = date("Ymdhis");

$rm_command = "rm -rf $work_dir" . "*";
exec($rm_command);
exec("mkdir -p $output_dir");

/*
 * Create language pack links file
 */
exec("mkdir -p $babel_language_packs_dir");
$language_pack_links_file = fopen("${babel_language_packs_dir}index.php", "w");
fwrite($language_pack_links_file, "<?php\n\$pageTitle = \"Babel Language Packs\";\n");
fwrite($language_pack_links_file, "include \$_SERVER['DOCUMENT_ROOT'] . '/eclipse.org-common/themes/Phoenix/header.php';\n");
fwrite($language_pack_links_file, "?>\n");
fwrite($language_pack_links_file, "<div id='maincontent'><div id='midcolumn'>\n");
fwrite($language_pack_links_file, "\n\t<h1>Babel Language Packs</h1>" .
	"\n\t<h2>Build ID: $timestamp</h2>" .
	"\n\t<p>The following language packs are based on the community translations entered into the <a href='http://babel.eclipse.org/'>Babel Translation Tool</a>, and may not be complete or entirely accurate.  If you find missing or incorrect translations, please use the <a href='http://babel.eclipse.org/'>Babel Translation Tool</a> to update them." .   
	"\n\tAll downloads are provided under the terms and conditions of the <a href='http://www.eclipse.org/legal/epl/notice.php'>Eclipse Foundation Software User Agreement</a> unless otherwise specified.</p>");

echo "Generating update site\n";
$train_result = mysql_query("SELECT DISTINCT train_id FROM release_train_projects ORDER BY train_id DESC");
while (($train_row = mysql_fetch_assoc($train_result)) != null) {
	$train_id = $train_row['train_id'];
	$train_version = "3.4.0";
	if (strcmp($train_id, "europa") == 0) {
		$train_version = "3.3.0";
	}
	$train_version_timestamp = "$train_version.v$timestamp";
	$site_xml = "";

	$output_dir_for_train = $output_dir . $train_row['train_id'] . "/";
	exec("mkdir $output_dir_for_train");
	exec("mkdir ${output_dir_for_train}features/");
	exec("mkdir ${output_dir_for_train}plugins/");

	fwrite($language_pack_links_file, "\n\t<h3>Release Train: $train_id</h3>\n\t<ul>");

	$language_result = mysql_query("SELECT language_id, iso_code, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name, is_active, IF(language_id = 1,1,0) AS sorthack FROM languages ORDER BY sorthack, name ASC");
	while (($language_row = mysql_fetch_assoc($language_result)) != null) {
		$language_name = $language_row['name'];
		$language_iso = $language_row['iso_code'];
		$language_id = $language_row['language_id'];
		if (strcmp($language_iso, "en") == 0) {
			$language_iso = "en_AA";
			$language_name = "Pseudo Translations";
		}

		$site_xml .= "\n\t<category-def name=\"Babel Language Packs in $language_name\" label=\"Babel Language Packs in $language_name\">";
		$site_xml .= "\n\t\t<description>Babel Language Packs in $language_name</description>";
		$site_xml .= "\n\t</category-def>";

		fwrite($language_pack_links_file, "\n\t<h4>Language: $language_name</h4>\n\t<ul>");

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
		$projects = array();
		$project_versions = array();
		$pseudo_translations_indexes = array();
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
				fwrite($outp, "# Copyright by many contributors; see http://babel.eclipse.org/");
				if (strcmp($language_iso, "en_AA") == 0) {
					$sql = "SELECT string_id, name, value FROM strings WHERE file_id = " . $properties_file['file_id'] .
						" AND is_active AND non_translatable = 0";
					$strings_result = mysql_query($sql);
					while (($strings_row = mysql_fetch_assoc($strings_result)) != null) {
						fwrite($outp, "\n" . $strings_row['name'] . "=" . $properties_file['project_id'] . $strings_row['string_id'] .
							":" . $strings_row['value']);

						$value = htmlspecialchars($strings_row['value']);
						if (strlen($value) > 100) {
							$value = substr($value, 0, 100) . " ...";
						}
						$pseudo_translations_indexes[$properties_file['project_id']][] = "\n\t\t<li><a href=\"http://babel.eclipse.org/babel/translate.php?project=" .
						$properties_file['project_id'] . "&version=" . $properties_file['version'] . "&file=" .
						$properties_file['origname'] . "&string=" . $strings_row['name'] . "\">" .
						$properties_file['project_id'] . $strings_row['string_id'] . "</a>&nbsp;" . $value . "</li>";
					}
				} else {
					$sql = "SELECT
						strings.name AS 'key', 
						strings.value AS orig, 
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
						fwrite($outp, "\n" . $strings_row['key'] . "=");
						# echo "${leader1S}${leaderS}${leaderS}${leaderS}" . $strings_row['key'] . "=";
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
			exec("cp ${source_files_dir}about.html $tmp_dir");
			/*
			 * Generate the META-INF/MANIFEST.MF file
			 */
			$parent_plugin_id = $plugin_name;
			$project_id = $properties_file['project_id'];
			$fragment_id = "$parent_plugin_id.nl_$language_iso";
			$fragment_filename = "${fragment_id}_$train_version_timestamp.jar";

			$plugins[$plugin_name]['id'] = $fragment_id;
			$plugins[$plugin_name]['version'] = $train_version_timestamp;

			exec("mkdir $tmp_dir/META-INF" );
			$outp = fopen("$tmp_dir/META-INF/MANIFEST.MF", "w");
			fwrite($outp, "Manifest-Version: 1.0\n");
			fwrite($outp, "Bundle-Name: $parent_plugin_id $language_name NLS Support\n");
			fwrite($outp, "Bundle-SymbolicName: $fragment_id ;singleton=true\n");
			fwrite($outp, "Bundle-Version: $train_version_timestamp\n");
			fwrite($outp, "Bundle-Vendor: Eclipse.org\n");
			fwrite($outp, "Fragment-Host: $parent_plugin_id\n");
			fclose($outp);
			/*
			 * Jar up this directory as the fragment plug-in jar
			 */
			system("cd $tmp_dir; jar cfM ${output_dir_for_train}plugins/$fragment_filename .");
			echo "${leader}${leader}Completed  plug-in fragment $plugin_name\n";

			$projects[$project_id][] = $fragment_id;
			$project_versions[$project_id] = $properties_file['version'];
		}
		foreach ($projects as $project_id => $fragment_ids) {
			/*
			 * Sort fragment names
			 */
			asort($fragment_ids);
			/*
			 * Create ${babel_language_packs_dir}tmp
			 */
			exec("mkdir -p ${babel_language_packs_dir}tmp/eclipse/features");
			exec("mkdir -p ${babel_language_packs_dir}tmp/eclipse/plugins");
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
			$feature_id = "org.eclipse.babel.nls_${project_id}_$language_iso";
			$feature_filename = "${feature_id}_$train_version_timestamp.jar";

			$project_version = $project_versions[$project_id];
			$sql = "SELECT pct_complete
				FROM project_progress
				WHERE project_id = \"$project_id\"
					AND version = \"$project_version\"
					AND language_id = $language_id";
			$project_pct_complete_result = mysql_query($sql);
			$project_pct_complete = mysql_result($project_pct_complete_result, 0);
			if (strcmp($language_iso, "en_AA") == 0) {
				$project_pct_complete = 100;
			}

			$outp = fopen("$tmp_dir/feature.xml", "w");
			fwrite($outp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" .
				"\n<feature" .
				"\n\tid=\"$feature_id\"" .
				"\n\tlabel=\"Babel Language Pack for $project_id in $language_name ($project_pct_complete%)\"" .
				"\n\timage=\"eclipse_update_120.jpg\"" .
				"\n\tprovider-name=\"%providerName\"" .
				"\n\tversion=\"$train_version_timestamp\">" .
				"\n\t<copyright>\n\t\t%copyright\n\t</copyright>" .
				"\n\t<license url=\"%licenseURL\">\n\t\t%license\n\t</license>" .
				"\n\t<description>Babel Language Pack for $project_id in $language_name</description>" );
			foreach ($fragment_ids as $fragment_id) {
				$jar_name = "${output_dir_for_train}plugins/${fragment_id}_$train_version_timestamp.jar";
				$size = filesize($jar_name);
				fwrite($outp, "\n\t<plugin fragment=\"true\" id=\"$fragment_id\" unpack=\"false\" " .
					"version=\"$train_version_timestamp\" download-size=\"$size\" install-size=\"$size\" />");
				/*
				 * Copy the plugin to ${babel_language_packs_dir}tmp
				 */
				exec("cp ${output_dir_for_train}plugins/${fragment_id}_$train_version_timestamp.jar ${babel_language_packs_dir}tmp/eclipse/plugins");
			}
			fwrite($outp, "\n</feature>");
			fclose($outp);
			/*
			 * Copy in the various legal files
			 */
			exec("cp ${source_files_dir}about.html $tmp_dir");
			exec("cp ${source_files_dir}eclipse_update_120.jpg $tmp_dir");
			exec("cp ${source_files_dir}epl-v10.html $tmp_dir");
			exec("cp ${source_files_dir}feature.properties $tmp_dir");
			exec("cp ${source_files_dir}license.html $tmp_dir");
			/*
			 * Copy in the Babel Pseudo Translations Index file
			 */
			if (strcmp($language_iso, "en_AA") == 0) {
				$pseudo_translations_index_file = fopen("${output_dir}BabelPseudoTranslationsIndex-$project_id.html", "w");
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
				exec("cp ${output_dir}BabelPseudoTranslationsIndex-$project_id.html $tmp_dir");
				exec("rm ${output_dir}BabelPseudoTranslationsIndex-$project_id.html");
			}
			/*
			 * Copy the feature to ${babel_language_packs_dir}tmp before jar'ing up
			 */
			exec("mkdir -p ${babel_language_packs_dir}tmp/eclipse/features/${feature_id}_$train_version_timestamp");
			exec("cd $tmp_dir; cp * ${babel_language_packs_dir}tmp/eclipse/features/${feature_id}_$train_version_timestamp");
			/*
			 * Zip up language pack
			 */
			$language_pack_name = "BabelLanguagePack-$project_id-${language_iso}_$train_version_timestamp.zip";
			exec("cd ${babel_language_packs_dir}tmp; zip -r $babel_language_packs_dir$language_pack_name eclipse");
			/*
			 * Clean up ${babel_language_packs_dir}tmp
			 */
			exec("rm -rf ${babel_language_packs_dir}tmp");
			/*
			 * Add project language pack link to language pack links file
			 */
			fwrite($language_pack_links_file, "\n\t<li><a href=\"${language_pack_leader}${language_pack_name}\">$language_pack_name ($project_pct_complete%)</a></li>");
			/*
			 * Jar up this directory as the feature jar
			 */
			system("cd $tmp_dir; jar cfM ${output_dir_for_train}features/$feature_filename .");
			/*
			 * Register this feature with the site.xml
			 */
			$site_xml .= "\n\t<feature url=\"features/$feature_filename\" id=\"$feature_id\" version=\"$train_version_timestamp\">";
			$site_xml .= "\n\t\t<category name=\"Babel Language Packs in $language_name\"/>";
			$site_xml .= "\n\t</feature>";
			echo "${leader}Completed language pack for $language_name ($language_iso)\n";
		}
		fwrite($language_pack_links_file, "\n\t</ul>");
	}
	/*
	 * <site mirrorsURL=... implemented in the weekly build process by sed'ing <site>
	 */
	$outp = fopen("${output_dir_for_train}site.xml", "w");
	fwrite($outp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
		"\n<site>" .
		"\n\t<description url=\"http://babel.eclipse.org/\">" .
		"\n\t\tThis update site contains user-contributed translations of the strings in all Eclipse projects." .
		"\n\t\tPlease see the http://babel.eclipse.org/ Babel project web pages for a full how-to-use explanation of" .
		"\n\t\tthese translations as well as how you can contribute to the translations of this and future versions of Eclipse." .
		"\n\t</description>");
	fwrite($outp, $site_xml);
	fwrite($outp, "\n</site>");
	fclose($outp);

	fwrite($language_pack_links_file, "\n\t</ul>");
	
	// now generate the metadata and add the non-greedy tags
	
	system("sh " . BABEL_BASE_DIR . "classes/export/runMetadata.sh ". 
	   METADATA_GENERATOR_LOCATION . " ${output_dir_for_train} ");
	system("xsltproc -o ${output_dir_for_train}content.xml ".
           dirname(__FILE__) . "/content.xsl ${output_dir_for_train}content.xml");
}
echo "Completed generating update site\n";

fwrite($language_pack_links_file, "\n</body>\n</html>");
fclose($language_pack_links_file);

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