<?php
/*******************************************************************************
 * Copyright (c) 2013 IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Kit Lo (IBM) - [402192] Extract project source files from Git repositories for translation
 *******************************************************************************/

/*
 * Extract properties or js files from update sites 
 */
$temp_dir = "/tmp/tmp-babel/";
$files = array();
$files_collected = array(array());

header("Content-type: text/plain");
include("global.php");
InitPage("");

$headless = 0;
if (!isset($User)) {
  echo "User not defined - running headless\n";
  $User = getGenieUser();
  $headless = 1;
}

require(dirname(__FILE__) . "/../classes/file/file.class.php");
global $dbh;

$temp_unzips_dir = $temp_dir . "unzips/";
if (is_dir($temp_unzips_dir)) {
  exec("rm -rf $temp_unzips_dir");
}
mkdir($temp_unzips_dir, 0777, TRUE) || die("***ERROR: Cannot create working directory: $temp_unzips_dir\n");

global $addon;
$context = $addon->callHook('context');
if ($context == "live") {
  $rsync_host = "download.eclipse.org::eclipseFullMirror/";
} else {
  $rsync_host = "rsync.osuosl.org::eclipse/";
}

# Get all active update sites
$sql = "SELECT * FROM project_source_locations AS m 
INNER JOIN release_train_projects AS r ON r.project_id = m.project_id AND r.version = m.version 
INNER JOIN release_trains AS t on t.train_id = r.train_id 
WHERE t.is_active = 1";
$rs_maps = mysqli_query($dbh, $sql);
while($update_site = mysqli_fetch_assoc($rs_maps)) {
  $site_url = $update_site['location'];
  $project_id = $update_site['project_id'];
  $version = $update_site['version'];

  # fix WTP version "3.12 (2018-12)"
  $version_dir = str_replace(" ", "", $version);
  $version_dir = str_replace("(", "\(", $version_dir);
  $version_dir = str_replace(")", "\)", $version_dir);

  # Sample dirs:
  # $site_url           http://git.eclipse.org/c/platform/eclipse.platform.git/snapshot/I20130101-0800.zip
  # $temp_snapshots_dir /tmp/tmp-babel/snapshots/eclipse/4.3/
  # $temp_unzips_dir    /tmp/tmp-babel/unzips/eclipse/4.3/
  $temp_snapshots_dir = $temp_dir . "snapshots/" . $project_id . "/" . $version_dir . "/";
  $temp_unzips_dir = $temp_dir . "unzips/" . $project_id . "/" . $version_dir . "/";

  # Collect all files for this project version
  if (!(isset($files_collected[$project_id]) && isset($files_collected[$project_id][$version]))) {
    $files_collected[$project_id][$version] = 1;
    $sql = "SELECT * FROM files WHERE project_id = \"$project_id\" AND version = \"$version\"";
    $rs_files = mysqli_query($dbh, $sql);
    while ($myrow_files = mysqli_fetch_assoc($rs_files)) {
      $file = new File();
      $file->project_id = $myrow_files['project_id'];
      $file->version = $myrow_files['version'];
      $file->name = $myrow_files['name'];
      $file->plugin_id = $myrow_files['plugin_id'];
      $file->file_id = $myrow_files['file_id'];
      $file->is_active = $myrow_files['is_active'];
      $files[$file->file_id] = $file;
    }
  }

  # Collect all plugin exclude patterns for this project version
  $sql = "SELECT pattern FROM plugin_exclude_patterns WHERE project_id = \"$project_id\" AND version = \"$version\"";
  $rs_patterns = mysqli_query($dbh, $sql);
  $patterns = Array();
  # Add default exclude patterns
  $patterns[] = "/^.*\/feature.properties$/";
  $patterns[] = "/^.*\/build.properties$/";
  $patterns[] = "/^.*\/pom.properties$/";
  $patterns[] = "/^.*\.source\/.*$/";
  $patterns[] = "/^.*\.test\/.*$/";
  $patterns[] = "/^.*\.tests\/.*$/";
  $patterns[] = "/^.*\.testing\/.*$/";
  while ($myrow_patterns = mysqli_fetch_assoc($rs_patterns)) {
    $patterns[] = $myrow_patterns['pattern'];
  }

  exec("mkdir -p $temp_snapshots_dir");
  exec("wget $site_url -O ${temp_snapshots_dir}snapshot.zip");

  # Make unzip dir
  mkdir($temp_unzips_dir, 0777, TRUE);
  chdir($temp_unzips_dir);
  exec("unzip -o -q ${temp_snapshots_dir}snapshot.zip");

  # Collect properties file names
  $properties_file_names = array();
  chdir($temp_unzips_dir);
  exec("find . -name *.properties", $properties_file_names);
  sort($properties_file_names);

  # Parse each properties file
  echo "Start processing properties files in project $project_id version $version...\n";
  echo "  Update site location: $site_url\n";
  foreach ($properties_file_names as $properties_file_name) {
	# extract plugin name
    $file_name = $temp_unzips_dir . $properties_file_name;
    $properties_file_name = substr($properties_file_name, strrpos($properties_file_name, "org."));
    $plugin_id = substr($properties_file_name, 0, strpos($properties_file_name, "/"));
    $pos = strpos($properties_file_name, '/');
    if ($pos !== false) {
      $properties_file_name = substr($properties_file_name, $pos);
    }

    # remove optional source dir, e.g. 'src' or 'src_ant'
    $pos = stripos($properties_file_name, '/org/');
    if ($pos !== false) {
      $properties_file_name = substr($properties_file_name, $pos);
    }
    $pos = strripos($properties_file_name, '/com/');
    if ($pos !== false) {
      $properties_file_name = substr($properties_file_name, $pos);
    }

    # get file ID
    $properties_file_name = $plugin_id . $properties_file_name;
	$file_id = File::getFileID($properties_file_name, $project_id, $version);

    # Match plugin exclude list
    $match = false;
    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $properties_file_name)) {
        $match = true;
        break;
      }
    }

    if (!$match) {
      if ($file_id > 0 && array_key_exists($file_id, $files) && $files[$file_id] != null) {
        # Existing file
        $file = $files[$file_id];
        $file->is_active = 1;
        unset($files[$file_id]);
      } else {
        # New file
        $file = new File();
        $file->project_id = $project_id;
        $file->version = $version;
        $file->name = $properties_file_name;
        $file->plugin_id = $plugin_id;
        $file->is_active = 1;
      }
      if (!$file->save()) {
        echo "***ERROR: Cannot save file $file->name\n";
      } else {
        $file_contents = ereg_replace("\r\n?", "\n", file_get_contents($file_name));
        $file->parseProperties($file_contents);
        echo "  $properties_file_name\n";
      }
    } else {
      echo "  !!! Excluding $properties_file_name\n";
    }
  }
  echo "Done processing " . sizeof($properties_file_names) . " properties files in project $project_id version $version\n\n";

  # Collect js file names
  $js_file_names = array();
  chdir($temp_unzips_dir);
  exec("find . -name *.js | grep nls/root", $js_file_names);
  sort($js_file_names);

  # Parse each js file
  echo "Start processing js files in project $project_id version $version...\n";
  echo "  Update site location: $site_url\n";
  foreach ($js_file_names as $js_file_name) {
    $file_name = $temp_unzips_dir . $js_file_name;
    $js_file_name = substr($js_file_name, strrpos($js_file_name, "org.eclipse."));
    $plugin_id = substr($js_file_name, 0, strpos($js_file_name, "/"));
	$file_id = File::getFileID($js_file_name, $project_id, $version);

    # Match plugin exclude list
    $match = false;
    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $js_file_name)) {
        $match = true;
        break;
      }
    }

    if (!$match) {
      if ($file_id > 0 && $files[$file_id] != null) {
        # Existing file
        $file = $files[$file_id];
        $file->is_active = 1;
        unset($files[$file_id]);
      } else {
        # New file
        $file = new File();
        $file->project_id = $project_id;
        $file->version = $version;
        $file->name = $js_file_name;
        $file->plugin_id = $plugin_id;
        $file->is_active = 1;
      }
      if (!$file->save()) {
        echo "***ERROR: Cannot save file $file->name\n";
      } else {
        $file_contents = ereg_replace("\r\n?", "\n", file_get_contents($file_name));
        $file->parseJs($file_contents);
        echo "  $js_file_name\n";
      }
    } else {
      echo "  !!! Excluding $js_file_name\n";
    }
  }
  echo "Done processing " . sizeof($js_file_names) . " js files in project $project_id version $version\n\n";

  chdir($temp_dir);
  exec("rm -rf $temp_snapshots_dir");
  exec("rm -rf $temp_unzips_dir");
}

# Deactivate the rest of the files
echo "Start deactivating inactive properties or js files in all projects above...\n";
foreach ($files as $file) {
  if ($file->is_active == 1) {
    $file->is_active = 0;
    if (!$file->save()) {
      echo "***ERROR: Cannot deactivate file $file->name\n";
    }
    echo "  " . $file->name . "\n";
  } else {
    unset($files[$file->file_id]);
  }
}
echo "Done deactivating " . sizeof($files) . " inactive properties or js files in all projects above\n\n";

chdir($temp_dir);
exec("rm -rf snapshots");
exec("rm -rf unzips");

if ($headless) {
  $User = null;
}

echo "Done\n";
?>