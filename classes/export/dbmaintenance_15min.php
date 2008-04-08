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
	 * This is a cronjob-driven database maintenance script
	 * It is run every 15 minutes
	 */

	define('BABEL_BASE_DIR', "../../");
	require(BABEL_BASE_DIR . "classes/system/dbconnection.class.php");
	
	if (!($ini = @parse_ini_file(BABEL_BASE_DIR . 'classes/base.conf'))) {
		errorLog("Failed to find/read database conf file - aborting.");
		exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
	}
	  
	$context = $ini['context'];
	if($context == "") {
		$context = "staging";
	}
	$dbc = new DBConnection();
	$dbh = $dbc->connect();


	# refresh the scoreboard
	require_once(BABEL_BASE_DIR . "classes/system/scoreboard.class.php");
	$sb = new Scoreboard();
	$sb->refresh();
?>