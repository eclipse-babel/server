<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation

*******************************************************************************/
// if not logged in redirect to login page
// otherwise show choices
require_once("aptana_global/aptana.inc.php");
require_once("aptana_global/agent.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("export");


$project_name = (isset($_POST['project_name'])?$_GET['project_name']:'');
$package_name = (isset($_POST['package_name'])?$_GET['package_name']:'');
$entry_name   = (isset($_POST['entry_name'])?$_GET['entry_name']:'');
$entry_value  = (isset($_POST['entry_value'])?$_GET['entry_value']:'');
$repo_path    = (isset($_POST['repo_path'])?$_GET['repo_path']:'');
$lang_code    = (isset($_POST['lang'])?$_GET['lang']:'en');
$lang_id      = 0;
/* testing
$project_name = "Aptana FTP Synchronization Provider";
$package_name = "com.aptana.ide.syncing.ftp";
$entry_name   = "FtpVirtualFileManager_PathCanBeEmpty";
$entry_value  = "path cannot be empty or null.";
$repo_path    = "src/com/aptana/ide/io/ftp/messages.properties";
*/


$cmd = "SELECT * FROM languages WHERE iso_code='$lang_code'";
if ($qry = mysql_query($cmd)) {
  if ($rec = mysql_fetch_object($qry)) {
    $lang_id = $rec->id;
  }
}


if ($lang_id != 1) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>invalid language code</error>\n";
  exit;
}

if (empty($project_name)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no project_name</error>\n";
  exit;
}
if (empty($package_name)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no package_name</error>\n";
  exit;
}
if (empty($entry_name)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no entry_name</error>\n";
  exit;
}
if (empty($entry_value)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no entry_value</error>\n";
  exit;
}
if (empty($repo_path)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no repo_path</error>\n";
  exit;
}

$cmd = "SELECT * FROM projects WHERE name='$project_name'";
if ($qry = mysql_query($cmd)) {
  if (!($rec = mysql_fetch_object($qry))) {
    $inCmd = "INSERT INTO projects SET name='$project_name',package_name='$package_name',updated_on=NOW(),created_on=NOW()";
    if (mysql_query($inCmd)) {
      //if ($qry = mysql_query($cmd))
      //  $rec = mysql_fetch_object($qry);
    }
  }
}

if (empty($rec)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>error finding project</error>\n";
  exit;
}


$entry = new entries_iu(0);
$entry->sqlLoad("language_id=1 AND project_id=$rec->id AND name='$entry_name'");

if ($entry->_id && ($entry->_value != $entry_value)) {
  $cmd = "DELETE FROM entries WHERE language_id>1 AND project_id='" . $rec->id . "' AND name='$entry_name'";
  //mysql_query($cmd);
}


$entry->_name        = $entry_name;
$entry->_value       = $entry_value;
$entry->_repo_path   = $package_name . "/" . $repo_path;
$entry->_language_id = "1";
$entry->_project_id  = $rec->id;


$entry->selfPost();


echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<success>1</success>\n";


// ------...------...------...------...------...------...------...------...------...------...------

echo <<< toTheEnd
toTheEnd;

// ------...------...------...------...------...------...------...------...------...------...------
?>