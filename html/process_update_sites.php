<?php
/*******************************************************************************
 * Copyright (c) 2010 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Kit Lo (IBM) - Initial API and implementation
 *    Kit Lo (IBM) - Bug 299402, Extract properties files from Eclipse project update sites for translation
*******************************************************************************/
/*
 * Extract properties files from update sites 
 */
# To-do: hard-coding the Eclipse, BIRT, & Webtools update sites for now; need to create an UI for project committers to enter their update sites
$eclipse = array("/home/data/httpd/download.eclipse.org/eclipse/updates/3.6milestones/S-3.6M4-200912101301", "eclipse", "3.6");
$birt = array("/home/data/httpd/download.eclipse.org/birt/update-site/2.6-interim", "birt", "2.6.0");
$webtools = array("/home/data/httpd/download.eclipse.org/webtools/updates", "webtools", "3.2");

# BIRT 2.6.0 & Webtools 3.2 are not defined yet, cannot test on staging server
#$update_sites = array($eclipse, $birt, $webtools);
$update_sites = array($eclipse);

$temp_dir = "/tmp/tmp-babel/";
$debug = TRUE;

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

if (is_dir($temp_dir)) {
  exec("rm -rf $temp_dir");
}
mkdir($temp_dir) || die("***ERROR: Cannot create working directory $temp_dir\n");

$files = array();
$sql = "SELECT * FROM files WHERE is_active = 1";
$rs_files = mysql_query($sql, $dbh);
while ($myrow_files = mysql_fetch_assoc($rs_files)) {
  $file = new File();
  $file->project_id = $myrow_files['project_id'];
  $file->version = $myrow_files['version'];
  $file->name = $myrow_files['name'];
  $file->plugin_id = $myrow_files['plugin_id'];
  $file->file_id = $myrow_files['file_id'];
  $files[$file->file_id] = $file;
}

foreach ($update_sites as $update_site) {
  $plugins_dir = $update_site[0] . "/plugins/";
  $project_id = $update_site[1];
  $version = $update_site[2];

  chdir($plugins_dir);
  foreach (glob("*.jar") as $jar_name) {
    # Sample jar name: org.eclipse.ui.workbench_3.6.0.I20100105-1530.jar
    #
    # Remove plugin version from jar name:
    # org.eclipse.ui.workbench_3.6.0.I20100105-1530.jar => org.eclipse.ui.workbench
    $temp_str = $jar_name;
    $temp_str = substr($temp_str, 0, strrpos($temp_str, "."));
    $temp_str = substr($temp_str, 0, strrpos($temp_str, "."));
    $temp_str = substr($temp_str, 0, strrpos($temp_str, "_"));
    $plugin_id = $temp_str;

    mkdir($temp_dir . $project_id);
    mkdir($temp_dir . $project_id . "/" . $version);
    mkdir($temp_dir . $project_id . "/" . $version . "/" . $plugin_id);
    chdir($temp_dir . $project_id . "/" . $version . "/" . $plugin_id);
    exec("unzip -o -q " . $plugins_dir . $jar_name . " *.properties");
  }

  $properties_file_names = array();
  chdir($temp_dir . $project_id . "/" . $version);
  exec("find . -name *.properties", $properties_file_names);
  sort($properties_file_names);

  echo "Start processing properties files in project $project_id version $version...\n";
  foreach ($properties_file_names as $properties_file_name) {
    $properties_file_name = substr($properties_file_name, 2);
    $plugin_id = substr($properties_file_name, 0, strpos($properties_file_name, "/"));
	$file_id = File::getFileID($properties_file_name, $project_id, $version);
						
    if ($files[$file_id] != null) {
      $file = $files[$file_id];
      $file->is_active = 1;
      unset($files[$file_id]);
    } else {
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
      $file_name = $temp_dir . $project_id . "/" . $version . "/" . $properties_file_name;
      $file->parseProperties(file_get_contents($file_name));
      if ($debug) {
        echo "  " . $file->name . "\n";
      }
    }
 }
  echo "Done processing " . sizeof($properties_file_names) . " properties files in project $project_id version $version\n\n";
}

# Deactivate the rest of the files
foreach ($files as $file) {
  $file->is_active = 0;
  if (!$file->save()) {
    echo "***ERROR: Cannot deactivate file $file->name\n";
  }
  if ($debug) {
    echo "  " . $file->name . "\n";
  }
}

if ($headless) {
  $User = null;
}

echo "Done\n";
?>
