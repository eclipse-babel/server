#!/usr/local/bin/php
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
ini_set("error_prepend_string","");
ini_set("error_append_string","");
require_once(BABEL_BASE_DIR."utils.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------

$databases  = array("aptana");

// ------...------...------...------...------...------...------...------...------...------...------

$tables = array(

// ------...------...------...------...------...------...------...------...------...------...------
  "user" => array (
      "id"            => "`id` int(8) unsigned NOT NULL auto_increment",

      "username"      => "`username` varchar(16) binary default NULL",
      "password"      => "`password` varchar(41) binary default NULL",
      "email"         => "`email` varchar(48) default NULL",

      "first_name"    => "`first_name` varchar(24) default NULL",
      "last_name"     => "`last_name` varchar(24) default NULL",

      "created"       => "`created` timestamp default CURRENT_TIMESTAMP",
      "login"         => "`login` datetime default NULL",
      "code"          => "`code` char(32) default NULL",

      "type"          => "`type` tinyint(1) unsigned NOT NULL default '0'",
      "status"        => "`status` tinyint(1) unsigned NOT NULL default '0'",

      "INDEX 1"    => "UNIQUE `user_key` (`username`)",
  ),
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

);  // eof - $tables

// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

initConnect();
foreach ($databases as $db) {
  echo "\nbuilding $db database\n";
  initDB($db);
  foreach ($tables as $table => $def)
    buildTable($table,$def);
}

// ------...------...------...------...------...------...------...------...------...------...------

function buildTable($table,$def) {
  echo "    table $table\n";
  
  $fldList = array();
  $fldLast = "";
  $autoKey = "";
  $fldNew  = current($def);

  // Force create table - will fail if exists but thats ok.
  if (strstr($fldNew,"auto_increment"))
    $autoKey = ",PRIMARY KEY  (`" . getColName($fldNew) . "`)";
  mysql_query("CREATE TABLE `$table` ( $fldNew $autoKey ) TYPE=MyISAM CHARSET=latin1;");
  

  // Grab a list of existing fields.
  if ($qry = mysql_query("SHOW COLUMNS FROM $table")) {
    while ($row = mysql_fetch_assoc($qry)) {
      $fldList[$row['Field']] = 1;
    }
  }

  foreach ($def as $fldOld => $fldDef) {
    if (substr($fldOld,0,5) == "INDEX") {
      $cmd = "ALTER TABLE `$table` ADD $fldDef";
      @mysql_query($cmd);
      //echo mysql_error() . "\n";
    }
    else {
      $fldNew  = getColName($fldDef);

      if (($exists = isset($fldList[$fldOld])) == false)
        if (($exists = isset($fldList[$fldNew])) == true)
          $fldOld = $fldNew;

      $cmdHow  = ($exists?"CHANGE COLUMN `$fldOld`":"ADD COLUMN");
      $cmdPos  = ($fldLast?"AFTER $fldLast":"FIRST");
      $cmd     = "ALTER TABLE `$table` $cmdHow $fldDef $cmdPos";
      $fldLast = $fldNew;
      mysql_query($cmd);

      echo "        " . ($exists?"changing":"adding") . " $fldNew\t$cmd\n";
    }
  }
  //echo mysql_error() . "\n";
}

// ------...------...------...------...------...------...------...------...------...------...------

function getColName($str) {
  $delimit = substr($str,0,1);
  if (($delimit == "'") || ($delimit == "`"))
    $str  = substr($str,1);
  else
    $delimit = " ";
  return substr($str,0,strpos($str,$delimit));
}

// ------...------...------...------...------...------...------...------...------...------...------

function initConnect() {
  sqlOpen(NULL);	
//  if (!@mysql_connect("localhost","webphp","sabref86")) {
//   echo "\n\tUnable to connect to mySQL server!\n";
//    exit;
//  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function initDB($db) {
  mysql_query("CREATE DATABASE IF NOT EXISTS $db");
  if (!mysql_select_db($db)) {
    echo "\n\tUnable to select database: $db!\n";
    exit;
  }
}

// ------...------...------...------...------...------...------...------...------...------...------
?>