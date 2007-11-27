<?php

class DBConnection {

	#*****************************************************************************
	#
	# dbconnection.class.php
	#
	# Author: 		Denis Roy
	# Date:			2004-08-05
	#
	# Description: Functions and modules related to the MySQL database connection
	#
	# HISTORY:
	#
	#*****************************************************************************

	function connect()
	{
		static $dbh;
		if (!($ini = @parse_ini_file('base.conf'))) {
			errorLog("Failed to find/read database conf file - aborting.");
			exitTo("error.php?errNo=101300","error: 101300 - database conf can not be found");
  		}
  
		if (!mysql_connect($ini['db_read_host'],$ini['db_read_user'],$ini['db_read_pass'])) {
			errorLog("Failed attempt to connect to server - aborting.");
			exitTo("/error.php?errNo=101301","error: 101301 - data server can not be found");
		}

		$dbh = mysql_connect($ini['db_read_host'],$ini['db_read_user'],$ini['db_read_pass']);
	
		if (!$dbh) {
    		errorLog("Failed attempt to connect to server - aborting.");
    		exitTo("/error.php?errNo=101301","error: 101301 - data server can not be found");
		}
    	$database = $ini['db_read_name'];
		if (isset($database)) {
			if (!mysql_select_db($database)) {
				errorLog("Failed attempt to open database: $database - aborting \n\t" . mysql_error());
				exitTo("/error.php?errNo=101303","error: 101303 - unknown database name");
			}
		}					
		return $dbh;
	}
	
	function disconnect() {
		mysql_close();
	}
}
?>