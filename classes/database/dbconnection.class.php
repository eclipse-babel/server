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

	var $MysqlUrl 		= "localhost";
	var $MysqlUser		= "ef_rw";
	var $MysqlPassword	= "68rf32eas3";
	var $MysqlDatabase 	= "eclipsefoundation";

	function connect()
	{
		static $dbh;
				
		$dbh = mysql_connect($this->MysqlUrl, $this->MysqlUser, $this->MysqlPassword);
	
		if (!$dbh) {
	  		echo( "<P>Unable to connect to the database server at this time.</P>" );
	  		exit();
		}
		$DbSelected = mysql_select_db($this->MysqlDatabase, $dbh);
		if (!$DbSelected) {
		   die ("Can't use $this->MysqlDatabase : " . mysql_error());
		}
		
		return $dbh;
	}
	
	function disconnect() {
		mysql_close();
	}
}
?>