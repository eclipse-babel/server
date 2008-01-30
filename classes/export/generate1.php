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
*******************************************************************************/

/* 
 * Documentation: http://wiki.eclipse.org/Babel_/_Server_Tool_Specification#Outputs
 */
//TODO handle versions, however that works; files.version is like "3.4" for Eclipse 3.4

/*
 * Globals
 */
$temporary_dir = "tmp_generated/";
$staging_update_site = "staging/";
$source_files_for_generate = "source_files_for_generate/";

$leader1 = "";
$leader1S= "";
$leader = ". . ";
$leaderS= ". . ";
$generated_timestamp = date("Ymdhis");

/*
 * Clear the staging site
 */
if( file_exists( $staging_update_site ) ) {
	exec( "rm -rf $staging_update_site*" );
} else {
	exec( "mkdir $staging_update_site" );
}
if( file_exists( "${staging_update_site}plugins/" ) ) {
        exec( "rm -rf ${staging_update_site}plugins/*" );
} else {
        exec( "mkdir ${staging_update_site}plugins/" );
}
if( file_exists( "${staging_update_site}features/" ) ) {
        exec( "rm -rf ${staging_update_site}features/*" );
} else {
        exec( "mkdir ${staging_update_site}features/" );
}
/*
 * Get the data (plugins, files, translations) from the live database
 */

if(defined('BABEL_BASE_DIR')){
	require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
}else{
    define('BABEL_BASE_DIR', "../../");
	require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
} 

$dbc = new DBConnection();
global $dbh;
$dbh = $dbc->connect();


/*
 * Generate one language pack per language
 */
$site_xml = '';
$language_result = mysql_query( 'SELECT * FROM languages WHERE languages.is_active' );
while( ($language_row = mysql_fetch_assoc($language_result)) != null ) {
	$language_name = $language_row['name'];
	$language_iso  = $language_row['iso_code'];
	echo "${leader1}Generating language pack for $language_name ($language_iso)(" . $language_row['language_id'] . ")\n";

	/*
	 * Determine which plug-ins need to be in this language pack.
	 */
	$file_result = mysql_query( "SELECT DISTINCT files.file_id, files.name 
		FROM files, strings, translations
	  	WHERE files.file_id = strings.file_id
		AND strings.string_id = translations.string_id
		AND translations.language_id = ". $language_row['language_id'] . "
		AND translations.is_active
		AND files.is_active ");
	$plugins = array();
	while( ($file_row = mysql_fetch_assoc($file_result)) != null ) {
		if( preg_match( "/^([a-zA-Z0-9\.]+)\/(.*)$/", $file_row['name'], $matches ) ) {
			$file_row['subname'] = $matches[2];
			$plugins[$matches[1]][] = $file_row;
		} else {
			echo "  WARNING: no plug-in name found in file " . $file_row['file_id'] . " \"" . $file_row['name'] . "\"\n";
		}
	}
	/*
	 * Generate one plug-in fragment for each plug-in
	 */
	foreach ($plugins as $plugin_name => $plugin_row ) {
		echo "${leader1}${leader}Generating plug-in fragment $plugin_name \n";
		/*
		 * Clean and create the temporary directory
		 */
		if ( file_exists( $temporary_dir ) ) {
			exec( "rm -rf $temporary_dir; mkdir $temporary_dir" );
		} else {
			exec( "mkdir $temporary_dir" );
		}

		/*
		 * Generate each *.properties file
		 */
		foreach ($plugin_row as $properties_file) {
			/*
			 * Convert the filename to *_lang.properties, e.g., foo_fr.properties
			 */
			$filename = $properties_file['subname'];
			if( preg_match( "/^(.*)\.properties$/", $filename, $matches ) ) {
				$filename = $matches[1] . '_' . $language_iso . '.properties';
			}
			echo "${leader1}${leader}${leader}Generating properties file $filename (" . $properties_file['file_id'] . ")\n";
			/*
			 * Create any needed sub-directories
			 */
			$fullpath =  $temporary_dir . $filename;
			preg_match( "/^((.*)\/)?(.+?)$/", $fullpath, $matches );
			$dirs1 = split( "\/", $matches[1] );
			$dirs2 = array();
			$d = '';
			foreach ( $dirs1 as $each ) {
				if( $each ) {
					$d .= $each . '/';
					$dirs2[] = $d;
				}
			}
			foreach( $dirs2 as $each ) {
				if( !file_exists( $each) ) {
					exec( "mkdir " . $each );
				}
			}
			/*
			 * Start writing to the file
			 */
			$outp = fopen( $fullpath, "w" );
			fwrite( $outp, "# Copyright by many contributors; see http://babel.eclipse.org/\n" ); 
			//TODO correct copyrights from all contributors
			/*
			 * For each string that is translated in this file, write it out
			 * Note that if a string is not translated, then it will not be
			 * included and thus Eclipse will pick up the default string for
			 * that key from the default *.properities file. Thus we only 
			 * include the strings that are translated.
			 */
			$sql = "
				SELECT 
				    strings.name AS `key`, 
				    strings.value AS orig, 
				    translations.value AS trans
					FROM strings, translations
					WHERE strings.string_id = translations.string_id
					AND translations.language_id = " . $language_row['language_id'] . "
					AND strings.file_id = " . $properties_file['file_id'] . "
					AND translations.is_active
				";
			$strings_result = mysql_query( $sql );
			while( ($strings_row = mysql_fetch_assoc($strings_result)) != null ) {
				fwrite( $outp, $strings_row['key'] . "=" );
				echo "${leader1S}${leaderS}${leaderS}${leaderS}" . $strings_row['key'] . "=";
				if( $strings_row['trans'] ) {
					fwrite( $outp, $strings_row['trans'] );
					echo $strings_row['trans'];
				} else {
					fwrite( $outp, $strings_row['orig'] );
				}
				fwrite( $outp, "\n" );
				echo "\n";
			}
			/*
			 * Finish the properties file
			 */
			fclose( $outp );
			echo "${leader1}${leader}${leader}completed  properties file $filename\n";
		}
		/*
		 * Copy in the various legal files
		 */
		exec( "cp ${source_files_for_generate}about.html ${temporary_dir}" );
		exec( "cp ${source_files_for_generate}license.html ${temporary_dir}" );
		exec( "cp ${source_files_for_generate}epl-v10.html ${temporary_dir}" );
		exec( "cp ${source_files_for_generate}eclipse_update_120.jpg ${temporary_dir}" );
		/*
		 * Generate the META-INF/MANIFEST.MF file
		 */
		$parent_plugin_id = $plugin_name;
		$fragment_id = "${parent_plugin_id}.nl_$language_iso";
		$fragment_major_version = "0.2.0"; //TODO what version number should these plugins be?
		$fragment_version = $fragment_major_version . ".v" . $generated_timestamp;
		$fragment_filename = $fragment_id . "_" . $fragment_version . ".jar";
		$parent_min_version = "0.0.0"; //TODO specify a min version (when versions are supported)
		$parent_max_version = "9.9.9"; //TODO specify a max version (when versions are supported)

		$plugins[$plugin_name]['id'] = $fragment_id;
		$plugins[$plugin_name]['version'] = $fragment_version;

		exec( "mkdir $temporary_dir/META-INF" );
		$outp = fopen( "$temporary_dir/META-INF/MANIFEST.MF", "w" );
		fwrite( $outp, "Manifest-Version: 1.0\n");
		fwrite( $outp, "Bundle-Name: $parent_plugin_id $language_name NLS Support\n");
		fwrite( $outp, "Bundle-SymbolicName: $fragment_id ;singleton=true\n");
		fwrite( $outp, "Bundle-Version: $fragment_version\n");
		fwrite( $outp, "Bundle-Vendor: Eclipse Foundation Inc.\n");
		fwrite( $outp, "Fragment-Host: $parent_plugin_id;bundle-version=\"[$parent_min_version,$parent_max_version)\"\n");
		fclose( $outp );
		/*
		 * Jar up this directory as the fragment plug-in jar
		 */
		system( "cd $temporary_dir; /home/bfreeman/jdk1.6.0_04/bin/jar cfM ../${staging_update_site}plugins/$fragment_filename ." );
		echo "${leader1}${leader}completed  plug-in fragment $plugin_name\n";
	}
        /*
         * Clean and create the temporary directory
         */
        if ( file_exists( $temporary_dir ) ) {
        	exec( "rm -rf $temporary_dir; mkdir $temporary_dir" );
        } else {
        	exec( "mkdir $temporary_dir" );
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
	$feature_id = "org.eclipse.nls.$language_iso";
        $feature_major_version = "0.2.0"; //TODO what version number should this feature be?
        $feature_version = $feature_major_version . ".v" . $generated_timestamp;
        $feature_filename = $feature_id . "_" . $feature_version . ".jar";

	$outp = fopen( "$temporary_dir/feature.xml", "w" );
	fwrite( $outp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?> 
<feature 
  id=\"$feature_id\" 
  label=\"Eclipse Language Pack for $language_name\"
  image=\"eclipse_update_120.jpg\"
  provider-name=\"Eclipse Foundation Inc.\" 
  version=\"$feature_version\">
<license url=\"license.html\">" 
	. htmlspecialchars( file_get_contents( "${source_files_for_generate}license.txt" ) ) . "</license>
<description>Translations in $language_name for all Eclipse Projects</description>
" );
	foreach ($plugins as $plugin_name => $plugin_row ) {
		fwrite( $outp, '<pluging fragment="true" id="'
			. $plugin_row['id'] . '" unpack="false" version="'
			. $plugin_row['version'] . '"/>
' );
	}
	fwrite( $outp, '</feature>
' );
	fclose( $outp );
        /*
         * Jar up this directory as the feature jar
         */
        system( "cd $temporary_dir; /home/bfreeman/jdk1.6.0_04/bin/jar cfM ../${staging_update_site}features/$feature_filename ." );
	/*
	 * Register this feature with the site.xml
	 */
	$site_xml .= "<feature url=\"features/$feature_filename\" id=\"$feature_id\" version=\"$feature_version\">
  <category name=\"Language Packs\"/></feature>
";
	echo "${leader1}completed  language pack for $language_name ($language_iso)\n";
}
/*
 * TODO <site mirrorsURL=... is not yet implemented
 */
$outp = fopen( "${staging_update_site}site.xml", "w" );
fwrite( $outp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<site>
  <description url=\"http://babel.eclipse.org/\">This update site contains
user-contributed translations of the strings in all Eclipse projects. Please
see the http://babel.eclipse.org/ Babel project web pages for a full how-to-use
explanation of these translations as well as how you can contribute to
the translations of this and future versions of Eclipse.</description>
  <category-def name=\"Language Packs\" label=\"Language Packs\">
    <description>Language packs for all Eclipse projects</description>
  </category-def>
" );
fwrite( $outp, $site_xml );
fwrite( $outp, "</site>
" );
fclose( $outp );

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

?>
