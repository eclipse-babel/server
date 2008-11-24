<?php
/*******************************************************************************
 * Copyright (c) 2007-2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Antoine Toulme, Intalio Inc. bug 248845: Refactoring generate1.php into different files with a functional approach
*******************************************************************************/

/*
 * Documentation: http://wiki.eclipse.org/Babel_/_Server_Tool_Specification#Outputs
 */

ob_start();
ini_set("memory_limit", "64M");
define("BABEL_BASE_DIR", "../../");
require(BABEL_BASE_DIR . "html/common_functions.php");
require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
require(BABEL_BASE_DIR . "classes/system/feature.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

$work_dir = "/home/babel-working/";
if (!($ini = @parse_ini_file(BABEL_BASE_DIR . "classes/base.conf"))) {
	errorLog("Failed to find/read database conf file - aborting.");
	exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
}

$context = $ini['context'];

$work_context_dir = $work_dir . $context . "_feature/";
$tmp_dir = $work_context_dir . "tmp/";
$output_dir = $work_context_dir . "output/";
$features_dir = $work_context_dir . "features/";

exec("rm -rf $work_context_dir*");
exec("mkdir -p $output_dir");

foreach($ReleaseTrain::all() as $train) {
	$output_dir_for_train = "$output_dir/$train->id/";
	foreach(Language::all() as $lang) {
		$feature = new Feature($lang, $train, $tmp_dir, $output_dir_for_train);
		$feature->generateAll();
		$featureZip = $feature->zip($features_dir);
		echo "Feature created here: $featureZip";
	}
}
