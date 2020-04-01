<?php
/*******************************************************************************
 * Copyright (c) 2008-2013 Eclipse Foundation, IBM Corporation and others.
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
 *    Sean Flanigan (Red Hat) - patch, bug 261584, wrong output folder
 *    Kit Lo (IBM) - patch, bug 270456, Unable to use Babel PTT to verify PDE in the eclipse SDK
 *    Kit Lo (IBM) - Bug 299402, Extract properties files from Eclipse project update sites for translation
 *    Kit Lo (IBM) - Bug 276306, Re-enable p2 repository
 *    Kit Lo (IBM) - Bug 310135, Babel p2 update site not using mirrors
 *    Kit Lo (IBM) - [289376] Need to start up a Helios build
 *    Kit Lo (IBM) - [344764] Translation fragments discard the plug-in's directory structure
 *    Kit Lo (IBM) - [330735] Generate a better id for the categories
 *    Kit Lo (IBM) - [313310] Support Eclipse-PlatformFilter for fragment of fragment
 *    Satoru Yoshida - [349107] properties file for template is located in wrong path
 *    Kit Lo (IBM) - [402215] Extract Orion JavaScript files for translation
 *    Satoru Yoshida - [487881] generate1.php needs some efficiency
 *    Satoru Yoshida - [487881] generate1.php needs some efficiency, array define fix
 *    Kit Lo (IBM) - Bug 552610 - split function removed in PHP 7
 *    Kit Lo (IBM) - Bug 561507 - translations for org.eclipse.e4.ui.workbench.renderers.swt missing
 *******************************************************************************/

/*
 * Documentation: http://wiki.eclipse.org/Babel_/_Server_Tool_Specification#Outputs
 */
define("METADATA_GENERATOR_LOCATION", "/home/genie/eclipse"); // you might want to read this value from a config file. Not sure yet.

ini_set("memory_limit", "512M");
require(dirname(__FILE__) . "/../system/backend_functions.php");
require(dirname(__FILE__) . "/../system/dbconnection.class.php");

# Get all release trains
$dbc = new DBConnection();
$dbh = $dbc->connect();
$result = mysqli_query($dbh, "SELECT * FROM release_trains ORDER BY train_version DESC");
$train_result = array();
while ($train_row = mysqli_fetch_assoc($result)) {
  $train_result[$train_row['train_id']] = $train_row['train_version'];
}

# Command-line parameter for the release train
# bug 272958
# b: build id
# t: (optional: train id) 

$options = getopt("b:t:");
$argv_train = "";
if (isset($options['t'])) {
	$argv_train = $options['t'];
	if (array_key_exists($argv_train, $train_result)) {
		# Picked a valid train .. remove all others
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

$release_id = "0.17.1";

global $addon;
$work_dir = $addon->callHook('babel_working');
$context = $addon->callHook('context');

$work_context_dir = $work_dir . $context . "/";
$tmp_dir = $work_context_dir . "tmp/";
$babel_language_packs_dir = $work_context_dir . "babel_language_packs/";
$output_dir = $work_context_dir . "update-site/";
$source_files_dir = dirname(__FILE__) . "/source_files_for_generate/";
$need_nl_path_case = array('templates/' => true, 'config/' => true, 'data/' => true, 'property_files/' => true);

$leader = ". ";
$timestamp = date("Ymdhis");

# Generate Orion language packs
exec("/usr/bin/php generate_orion.php -b $build_id -s $timestamp");

# Generate Babel language packs
exec("rm -rf $tmp_dir; rm -rf $output_dir");
exec("mkdir -p $output_dir");

echo "Requested builds: ";
foreach ($train_result as $train_id => $train_version) {
	echo $train_id . " ";
}
echo "\n";

# Loop through the trains
foreach ($train_result as $train_id => $train_version) {
	echo "Generating update site for: $train_id\n";
	
	/*
	 * Create language pack links file
	 */
	$babel_language_packs_dir_for_train = $babel_language_packs_dir . $train_id . "/";
	exec("mkdir -p $babel_language_packs_dir_for_train");
	$language_pack_links_file = fopen("${babel_language_packs_dir_for_train}${train_id}.php", "w");
	fwrite($language_pack_links_file, "<?php\n");
	fwrite($language_pack_links_file, "\$language_pack_leader = \".\";\n");
	fwrite($language_pack_links_file, "?>\n");
	# copy page_header.html here 
	$header = file_get_contents("${source_files_dir}page_header.html");
	fwrite($language_pack_links_file, $header);
	fwrite($language_pack_links_file, "\n\t<h1>Babel Language Packs for " . ucfirst($train_id) . "</h1>" .
		"\n\t<h2>Build ID: $build_id</h2>" .
		"\n\t<p>The following language packs are based on the community translations entered into the <a href='http://babel.eclipse.org/'>Babel Translation Tool</a>, and may not be complete or entirely accurate.  If you find missing or incorrect translations, please use the <a href='http://babel.eclipse.org/'>Babel Translation Tool</a> to update them." .   
		"\n\tAll downloads are provided under the terms and conditions of the <a href='http://www.eclipse.org/legal/epl/notice.php'>Eclipse Foundation Software User Agreement</a> unless otherwise specified.</p>" .
		"\n\t<p>Go to: ");
	$train_version_timestamp = "$train_version.v$timestamp";
	$language_pack_links_file_buffer = "";
	$site_xml = "";

	$output_dir_for_train = $output_dir . $train_id . "/";
	exec("mkdir $output_dir_for_train");
	exec("mkdir ${output_dir_for_train}features/");
	exec("mkdir ${output_dir_for_train}plugins/");

	$sql = "SELECT language_id, iso_code, IF(locale <> '', CONCAT(CONCAT(CONCAT(name, ' ('), locale), ')'), name) as name, is_active, IF(language_id = 1,1,0) AS sorthack FROM languages ORDER BY sorthack, name ASC";
	$language_result = mysqli_query($dbh, $sql);
	if($language_result === FALSE) {
		# We may have lost the database connection with our shell-outs, reconnect
		$dbh = $dbc->connect();
		$language_result = mysqli_query($dbh, $sql);
	}
	while (($language_row = mysqli_fetch_assoc($language_result)) != null) {
		$language_name = $language_row['name'];
		$language_iso = $language_row['iso_code'];
		$language_id = $language_row['language_id'];
		if (strcmp($language_iso, "en") == 0) {
			$language_name = "Pseudo Translations";
			$language_iso = "en_AA";
		}

		echo "${leader}Generating language pack for: $language_name ($language_iso) (language_id=" . $language_id . ")\n";

		# Determine which plug-ins need to be in this language pack
		if (strcmp($language_iso, "en_AA") == 0) {
			$file_result = mysqli_query($dbh, "SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE f.is_active
				AND v.train_id = '" . $train_id . "' ORDER BY f.project_id DESC");
		} else {
			$file_result = mysqli_query($dbh, "SELECT DISTINCT f.project_id, f.version, f.file_id, f.name
				FROM files AS f
				INNER JOIN strings AS s ON f.file_id = s.file_id
				INNER JOIN translations AS t ON (s.string_id = t.string_id AND t.is_active)
				INNER JOIN release_train_projects as v ON (f.project_id = v.project_id AND f.version = v.version)
				WHERE t.language_id = " . $language_id . "
				AND f.is_active
				AND v.train_id = '" . $train_id . "' ORDER BY f.project_id DESC");
		}

		$plugins = array();
		$projects = array();
		$projects_include_orion = array();
		$project_versions = array();
		$pseudo_translations_indexes = array();
		while (($file_row = mysqli_fetch_assoc($file_result)) != null) {
			# save original filename
			$file_row['origname'] = $file_row['name'];

			# remove optional outer dirs, e.g. 'pde/ui/'
			$pos = strripos($file_row['name'], 'org.');
			if ($pos !== false) {
				$file_row['name'] = substr($file_row['name'], $pos);
			}
			$pos = strripos($file_row['name'], 'com.');
			if ($pos !== false) {
				$file_row['name'] = substr($file_row['name'], $pos);
			}

			$pattern = 
				'/^
				(.*?)?					# $1 plugin name
				\/						# slash
				(.*?\/)?				# $2 dir name
				([^\/]+[.]properties)	# $3 file name
				$/ix';
			$plugin_name_string = preg_replace($pattern, '$1', $file_row['name']);
			$dir_name_string = preg_replace($pattern, '$2', $file_row['name']);
			$file_name_string = preg_replace($pattern, '$3', $file_row['name']);

			# remove optional source dir, e.g. 'src' or 'src_ant'
			$pos = stripos($dir_name_string, 'org/');
			if ($pos !== false) {
				$dir_name_string = substr($dir_name_string, $pos);
			}
			$pos = strripos($dir_name_string, 'com/');
			if ($pos !== false) {
				$dir_name_string = substr($dir_name_string, $pos);
			}

			$file_row['plugin_name'] = $plugin_name_string;
			$file_row['dir_name'] = $dir_name_string;
			$file_row['file_name'] = $file_name_string;

			if (preg_match("/^(.*)\.js$/", $plugin_name_string)) {
				$project_id = $file_row['project_id'];
				$projects_include_orion[$project_id][] = "eclipse.orion";
				$project_versions[$project_id] = $file_row['version'];
			} else {
				$plugins[$plugin_name_string][] = $file_row;
			}
		}

		/*
		 * Generate one plug-in fragment for each plug-in
		 */
		ksort($plugins);
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
				$dirname = $properties_file['dir_name'];
				$filename = $properties_file['file_name'];

				if (isset($need_nl_path_case[$dirname])) {
					/*
					 * Convert the dirname to nl/lang/path, e.g., nl/fr/templates
					 */
					$dirname = 'nl/' . $language_iso . '/' . $dirname;
				} else {
					/*
					 * Convert the filename to *_lang.properties, e.g., foo_fr.properties
					 */
					if (preg_match( "/^(.*)\.properties$/", $filename, $matches)) {
						$filename = $matches[1] . '_' . $language_iso . '.properties';
					}
				}
				echo "${leader}${leader}${leader}Generating properties file " . $dirname . $filename . " (file_id=" . $properties_file['file_id'] . ")\n";
				/*
				 * Create any needed sub-directories
				 */
				exec("mkdir -p \"" . $tmp_dir . $dirname . "\"");
				/*
				 * Start writing to the file
				 */
				$fullpath = $tmp_dir . $dirname . $filename;
				$outp = fopen($fullpath, "w");
				fwrite($outp, "# Copyright by many contributors; see http://babel.eclipse.org/");
				if (strcmp($language_iso, "en_AA") == 0) {
					$sql = "SELECT string_id, name, value FROM strings WHERE file_id = " . $properties_file['file_id'] .
						" AND is_active AND non_translatable = 0";
					$strings_result = mysqli_query($dbh, $sql);
					while (($strings_row = mysqli_fetch_assoc($strings_result)) != null) {
						/* Check for value starting with form tag (bug 270456) */
						if (preg_match("/^(<form>)(.*)/i", $strings_row['value'], $matches)) {
							$pattern = "/^(<form>)(.*)/i";
							$replace = "$1" . "<p>" . $properties_file['project_id'] . $strings_row['string_id'] . ":" . "</p>" . "$2";
							$strings_row['value'] = preg_replace($pattern, $replace, $strings_row['value']);
							$outp_line = "\n" . $strings_row['name'] . "=" . $strings_row['value'];
						} else {
						        $outp_line = "\n" . $strings_row['name'] . "=" . $properties_file['project_id'] . $strings_row['string_id'] .
								":" . $strings_row['value'];
						}
						fwrite($outp, $outp_line);

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
					$strings_result = mysqli_query($dbh, $sql);
					while (($strings_row = mysqli_fetch_assoc($strings_result)) != null) {
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
				echo "${leader}${leader}${leader}Completed  properties file " . $dirname . $filename . "\n";
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

			exec("mkdir -p $tmp_dir/META-INF" );
			$outp = fopen("$tmp_dir/META-INF/MANIFEST.MF", "w");
			fwrite($outp, "Manifest-Version: 1.0\n");
			fwrite($outp, "Bundle-Name: $parent_plugin_id $language_name NLS Support\n");
			fwrite($outp, "Bundle-SymbolicName: $fragment_id ;singleton=true\n");
			fwrite($outp, "Bundle-Version: $train_version_timestamp\n");
			fwrite($outp, "Bundle-Vendor: Eclipse.org\n");

			if (preg_match("/64$/", $plugin_name)) {

				if     (preg_match("/.gtk.linux.x86_64$/", $plugin_name)) {
					$arr = explode(".gtk.linux.x86_64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=linux) (osgi.arch=x86_64))";
				}
				elseif (preg_match("/.linux.x86_64$/", $plugin_name)) {
					$arr = explode(".linux.x86_64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=linux) (osgi.arch=x86_64))";
				}
				elseif (preg_match("/.gtk.linux.ppc64$/", $plugin_name)) {
					$arr = explode(".gtk.linux.ppc64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=linux) (osgi.arch=ppc64))";
				}
				elseif (preg_match("/.gtk.aix.ppc64$/", $plugin_name)) {
					$arr = explode(".gtk.aix.ppc64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=aix) (osgi.arch=ppc64))";
				}
				elseif (preg_match("/.cocoa.macosx.x86_64$/", $plugin_name)) {
					$arr = explode(".cocoa.macosx.x86_64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=cocoa) (osgi.os=macosx) (osgi.arch=x86_64))";
				}
				elseif (preg_match("/.win32.win32.x86_64$/", $plugin_name)) {
					$arr = explode(".win32.win32.x86_64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=win32) (osgi.os=win32) (osgi.arch=x86_64))";
				}
				elseif (preg_match("/.win32.x86_64$/", $plugin_name)) {
					$arr = explode(".win32.x86_64", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=win32) (osgi.arch=x86_64))";
				}

			}

			elseif (preg_match("/86$/", $plugin_name)) {

				if     (preg_match("/.gtk.linux.x86$/", $plugin_name)) {
					$arr = explode(".gtk.linux.x86", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=linux) (osgi.arch=x86))";
				}
				elseif (preg_match("/.linux.x86$/", $plugin_name)) {
					$arr = explode(".linux.x86", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=linux) (osgi.arch=x86))";
				}
				elseif (preg_match("/.gtk.solaris.x86$/", $plugin_name)) {
					$arr = explode(".gtk.solaris.x86", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=solaris) (osgi.arch=x86))";
				}
				elseif (preg_match("/.win32.win32.x86$/", $plugin_name)) {
					$arr = explode(".win32.win32.x86", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=win32) (osgi.os=win32) (osgi.arch=x86))";
				}
				elseif (preg_match("/.win32.x86$/", $plugin_name)) {
					$arr = explode(".win32.x86", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=win32) (osgi.arch=x86))";
				}

			}

			elseif (preg_match("/32$/", $plugin_name)) {

				if     (preg_match("/.gtk.hpux.ia64_32$/", $plugin_name)) {
					$arr = explode(".gtk.hpux.ia64_32", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=hpux) (osgi.arch=ia64_32))";
				}
				elseif (preg_match("/.hpux.ia64_32$/", $plugin_name)) {
					$arr = explode(".hpux.ia64_32", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=hpux) (osgi.arch=ia64_32))";
				}
				elseif (preg_match("/^org.eclipse.compare.win32$/", $plugin_name)) {
					$parent_plugin_id .= "\nEclipse-PlatformFilter: (osgi.os=win32)";
				}
				elseif (preg_match("/.win32$/", $plugin_name)) {
					$arr = explode(".win32", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (osgi.ws=win32)";
				}

			}

			elseif (preg_match("/.macosx$/", $plugin_name)) {

				if     (preg_match("/.carbon.macosx$/", $plugin_name)) {
					$arr = explode(".carbon.macosx", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=carbon) (osgi.os=macosx))";
				}
				elseif (preg_match("/.cocoa.macosx$/", $plugin_name)) {
					$arr = explode(".cocoa.macosx", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=cocoa) (osgi.os=macosx))";
				}
				else {
					$arr = explode(".macosx", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (osgi.os=macosx)";
				}

			}

			elseif (preg_match("/.aix.ppc$/", $plugin_name)) {

				if (preg_match("/.gtk.aix.ppc$/", $plugin_name)) {
					$arr = explode(".gtk.aix.ppc", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=aix) (osgi.arch=ppc))";
				}
				else {
					$arr = explode(".aix.ppc", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=aix) (osgi.arch=ppc))";
				}

			}

			elseif (preg_match("/.solaris.sparc$/", $plugin_name)) {

				if (preg_match("/.gtk.solaris.sparc$/", $plugin_name)) {
					$arr = explode(".gtk.solaris.sparc", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=solaris) (osgi.arch=sparc))";
				}
				else {
					$arr = explode(".solaris.sparc", $plugin_name);
					$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.os=solaris) (osgi.arch=sparc))";
				}

			}

			elseif (preg_match("/.gtk.linux.s390$/", $plugin_name)) {
				$arr = explode(".gtk.linux.s390", $plugin_name);
				$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=linux) (osgi.arch=s390))";
			}
			elseif (preg_match("/.gtk.linux.s390x$/", $plugin_name)) {
				$arr = explode(".gtk.linux.s390x", $plugin_name);
				$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (& (osgi.ws=gtk) (osgi.os=linux) (osgi.arch=s390x))";
			}

			elseif (preg_match("/.carbon$/", $plugin_name)) {
				$arr = explode(".carbon", $plugin_name);
				$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (osgi.ws=carbon)";
			}
			elseif (preg_match("/.cocoa$/", $plugin_name)) {
				$arr = explode(".cocoa", $plugin_name);
				$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (osgi.ws=cocoa)";
			}
			elseif (preg_match("/.linux$/", $plugin_name)) {
				$arr = explode(".linux", $plugin_name);
				$parent_plugin_id = $arr[0] . "\nEclipse-PlatformFilter: (osgi.os=linux)";
			}

			fwrite($outp, "Fragment-Host: $parent_plugin_id\n");
			fclose($outp);
			/*
			 * Jar up this directory as the fragment plug-in jar
			 */
			system("cd $tmp_dir; jar cfM ${output_dir_for_train}plugins/$fragment_filename .");
			echo "${leader}${leader}Completed  plug-in fragment $plugin_name\n";

			$projects[$project_id][] = $fragment_id;
			$projects_include_orion[$project_id][] = $fragment_id;
			$project_versions[$project_id] = $properties_file['version'];
		}
		if (sizeof($projects) > 0) {
			$site_xml .= "\n\t<category-def name=\"lang_$language_iso\" label=\"Babel Language Packs in $language_name\">";
			$site_xml .= "\n\t\t<description>Babel Language Packs in $language_name</description>";
			$site_xml .= "\n\t</category-def>";
		}
		if (sizeof($projects_include_orion) > 0) {
			fwrite($language_pack_links_file, "\n\t\t<a href='#$language_iso'>$language_name</a>");
			if (strcmp($language_iso, "en_AA") != 0) {
				fwrite($language_pack_links_file, ",");
			}
			$language_pack_links_file_buffer .= "\n\t<h4>Language: <a name='$language_iso'>$language_name</a></h4>";
			$language_pack_links_file_buffer .= "\n\t<ul>";
		}
		ksort($projects_include_orion);
		foreach ($projects_include_orion as $project_id => $fragment_ids) {
			/*
			 * Sort fragment names
			 */
			asort($fragment_ids);
			/*
			 * Create ${babel_language_packs_dir_for_train}tmp
			 */
			exec("mkdir -p ${babel_language_packs_dir_for_train}tmp/eclipse/features");
			exec("mkdir -p ${babel_language_packs_dir_for_train}tmp/eclipse/plugins");
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

			if (strcmp($language_iso, "en_AA") == 0) {
				$project_pct_complete = 100;
				if (strcmp($project_id, "eclipse.orion") != 0) {
					$site_xml .= "\n\t<category-def name=\"proj_$project_id\" label=\"Babel Language Packs for $project_id\">";
					$site_xml .= "\n\t\t<description>Babel Language Packs for $project_id</description>";
					$site_xml .= "\n\t</category-def>";
				}
			} else {
				$project_version = $project_versions[$project_id];
				$sql = "SELECT pct_complete
					FROM project_progress
					WHERE project_id = \"$project_id\"
						AND version = \"$project_version\"
						AND language_id = $language_id";
				$project_pct_complete_result = mysqli_query($dbh, $sql);
				$project_pct_complete = mysqli_result($project_pct_complete_result, 0);
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
				if (strcmp($fragment_id, "eclipse.orion") != 0) {
					$jar_name = "${output_dir_for_train}plugins/${fragment_id}_$train_version_timestamp.jar";
					$size = filesize($jar_name);
					fwrite($outp, "\n\t<plugin fragment=\"true\" id=\"$fragment_id\" unpack=\"false\" " .
						"version=\"$train_version_timestamp\" download-size=\"$size\" install-size=\"$size\" />");
					/*
					 * Copy the plugin to ${babel_language_packs_dir_for_train}tmp
					 */
					exec("cp ${output_dir_for_train}plugins/${fragment_id}_$train_version_timestamp.jar ${babel_language_packs_dir_for_train}tmp/eclipse/plugins");
				}
			}
			fwrite($outp, "\n</feature>");
			fclose($outp);
			/*
			 * Copy in the various legal files
			 */
			exec("cp ${source_files_dir}about.html $tmp_dir");
			exec("cp ${source_files_dir}eclipse_update_120.jpg $tmp_dir");
			exec("cp ${source_files_dir}epl-2.0.html $tmp_dir");
			exec("cp ${source_files_dir}feature.properties $tmp_dir");
			exec("cp ${source_files_dir}license.html $tmp_dir");
			/*
			 * Copy in the Babel Pseudo Translations Index file
			 */
			if (strcmp($language_iso, "en_AA") == 0 && strcmp($project_id, "eclipse.orion") != 0) {
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
			 * Copy the feature to ${babel_language_packs_dir_for_train}tmp before jar'ing up
			 */
			exec("mkdir -p ${babel_language_packs_dir_for_train}tmp/eclipse/features/${feature_id}_$train_version_timestamp");
			exec("cd $tmp_dir; cp * ${babel_language_packs_dir_for_train}tmp/eclipse/features/${feature_id}_$train_version_timestamp");
			/*
			 * Zip up language pack
			 */
			$language_pack_name = "BabelLanguagePack-$project_id-${language_iso}_$train_version_timestamp.zip";
			if (strcmp($project_id, "eclipse.orion") != 0) {
				exec("cd ${babel_language_packs_dir_for_train}tmp; zip -r $babel_language_packs_dir_for_train$language_pack_name eclipse");
			}
			/*
			 * Clean up ${babel_language_packs_dir_for_train}tmp
			 */
			exec("rm -rf ${babel_language_packs_dir_for_train}tmp");
			/*
			 * Add project language pack link to language pack links file
			 */
			$language_pack_links_file_buffer .= "\n\t\t<li><a href=\"<?= \$language_pack_leader ?>/${language_pack_name}\">$language_pack_name ($project_pct_complete%)</a></li>";
			/*
			 * Jar up this directory as the feature jar
			 */
			if (strcmp($project_id, "eclipse.orion") != 0) {
				system("cd $tmp_dir; jar cfM ${output_dir_for_train}features/$feature_filename .");
			}
			/*
			 * Clean the temporary directory
			 */
			exec("rm -rf $tmp_dir");
			/*
			 * Register this feature with the site.xml
			 */
			if (strcmp($project_id, "eclipse.orion") != 0) {
				$site_xml .= "\n\t<feature url=\"features/$feature_filename\" id=\"$feature_id\" version=\"$train_version_timestamp\">";
				$site_xml .= "\n\t\t<category name=\"proj_$project_id\"/>";
				$site_xml .= "\n\t\t<category name=\"lang_$language_iso\"/>";
				$site_xml .= "\n\t</feature>";
			}
		}  /*  End: foreach project  */
		echo "${leader}Completed language pack for $language_name ($language_iso)\n";
		if (sizeof($projects_include_orion) > 0) {
			$language_pack_links_file_buffer .= "\n\t</ul>";
		}
	}

	fwrite($language_pack_links_file, "\n\t</p>");
	fwrite($language_pack_links_file, $language_pack_links_file_buffer);
	
	fwrite($language_pack_links_file, "\n\t<br />\n</body>\n</html>");
	fclose($language_pack_links_file);

	$dbc->disconnect($dbh);

	/*
	 * Generate and save site.xml/content.jar/artifacts.jar with mirrorsURL
	 */
	$outp = fopen("${output_dir_for_train}site.xml", "w");
	fwrite($outp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
		"\n<site pack200=\"true\" digestURL=\"https://download.eclipse.org/technology/babel/update-site/R$release_id/$train_id\"" .
		"\n\tmirrorsURL=\"https://www.eclipse.org/downloads/download.php?file=/technology/babel/update-site/R$release_id/$train_id/site.xml&amp;format=xml\">" .
		"\n\t<description url=\"https://babel.eclipse.org/\">" .
		"\n\t\tThis update site contains user-contributed translations of the strings in all Eclipse projects." .
		"\n\t\tPlease see the https://babel.eclipse.org/ Babel project web pages for a full how-to-use explanation of" .
		"\n\t\tthese translations as well as how you can contribute to the translations of this and future versions of Eclipse." .
		"\n\t</description>");
	fwrite($outp, $site_xml);
	fwrite($outp, "\n</site>");
	fclose($outp);

	/*
	 * Generate the metadata and add the non-greedy tags
	 * Note: Not needed for Europa and Ganymede because p2 repository was not supported
	 */
	exec("mkdir ${output_dir_for_train}mirrors/");
	if (file_exists(METADATA_GENERATOR_LOCATION) && strcmp($train_id, "europa") != 0 && strcmp($train_id, "ganymede") != 0) {
		echo "Running the Meta at " . dirname(__FILE__) . "/runMetadata.sh\n";
		system("/bin/sh " . dirname(__FILE__) . "/runMetadata.sh ". METADATA_GENERATOR_LOCATION . " $output_dir_for_train $train_version_timestamp");
		echo "Processing XML\n";
		system("xsltproc -o ${output_dir_for_train}content.xml ". dirname(__FILE__) . "/content.xsl ${output_dir_for_train}content.xml");
		system("cd ${output_dir_for_train} ; jar -fc content.jar content.xml ; jar -fc artifacts.jar artifacts.xml");
		system("cd ${output_dir_for_train} ; rm content.xml ; rm artifacts.xml");
		system("cd ${output_dir_for_train} ; mv content.jar mirrors/content.mirrors ; mv artifacts.jar mirrors/artifacts.mirrors");
	}
	system("cd ${output_dir_for_train} ; mv site.xml mirrors/site.mirrors");

	/*
	 * Generate normal site.xml/content.jar/artifacts.jar without mirrorsURL
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

	/*
	 * Generate the metadata and add the non-greedy tags
	 * Note: Not needed for Europa and Ganymede because p2 repository was not supported
	 */
	if (file_exists(METADATA_GENERATOR_LOCATION) && strcmp($train_id, "europa") != 0 && strcmp($train_id, "ganymede") != 0) {
		echo "Running the Meta at " . dirname(__FILE__) . "/runMetadata.sh\n";
		system("/bin/sh " . dirname(__FILE__) . "/runMetadata.sh ". METADATA_GENERATOR_LOCATION . " $output_dir_for_train $train_version_timestamp");
		echo "Processing XML\n";
		system("xsltproc -o ${output_dir_for_train}content.xml ". dirname(__FILE__) . "/content.xsl ${output_dir_for_train}content.xml");
		system("cd ${output_dir_for_train} ; jar -fc content.jar content.xml ; jar -fc artifacts.jar artifacts.xml");
		system("cd ${output_dir_for_train} ; rm content.xml ; rm artifacts.xml");
		system("cd ${output_dir_for_train} ; mv site.xml mirrors/site.txt");
	} else {
		system("cd ${output_dir_for_train} ; cp site.xml mirrors/site.txt");
	}
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

function usage() {
	echo "\n";
	echo "generate1.php -b <build_id> [-t <train_id>]\n";
	echo "  -b <build_id>: The Build ID for this build.\n";
	echo "  -t <train_id>: Optional: train to build (neon, mars, luna, kepler, juno, indigo, helios, galileo, ganymede, europa)";
	echo "\n";
}

?>
