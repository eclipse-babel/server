<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - initial API and implementation
*******************************************************************************/

class String {
  public $errStrs;
  
  public $string_id		= 0;
  public $file_id		= 0;
  public $name			= '';
  public $value 		= '';
  public $userid		= 0;
  public $created_on	= '';
  public $is_active 	= 0;
  
	function save() {
		$rValue = false;
		if($this->file_id != 0 && $this->name != "" && $this->userid > 0) {
			global $App, $dbh;

			
			if($this->string_id == 0) {
				$this->string_id = $this->getIDFromName($this->file_id, $this->name);
			}

			$sql 		= "INSERT INTO";
			$created_on = "NOW()";
			$where 		= "";
			if($this->string_id > 0) {
				$sql = "UPDATE";
				$created_on = "created_on";
				$where = " WHERE string_id = " . $App->sqlSanitize($this->string_id, $dbh);
			}
			
			$sql .= " strings 
						SET string_id 	= " . $App->sqlSanitize($this->string_id, $dbh) . ",
							file_id		= " . $App->sqlSanitize($this->file_id, $dbh) . ", 
							name		= " . $App->returnQuotedString($App->sqlSanitize($this->name, $dbh)) . ",
							value		= " . $App->returnQuotedString($App->sqlSanitize($this->value, $dbh)) . ",
							userid		= " . $App->returnQuotedString($App->sqlSanitize($this->userid, $dbh)) . ",
							created_on	= " . $created_on . ",
							is_active	= " . $App->sqlSanitize($this->file_id, $dbh) . $where;
			if(mysql_query($sql, $dbh)) {
				if($this->string_id == 0) {
					$this->string_id = mysql_insert_id($dbh);
				}
				$rValue = true;
			}
			else {
				$GLOBALS['g_ERRSTRS'][1] = mysql_error();
			}
		}
		return $rValue;
	}
	
	function getIDFromName($_file_id, $_name) {
		$rValue = 0;
		if($_file_id > 0 && $_name != "") {
			global $App, $dbh;

			$sql = "SELECT string_id
				FROM 
					strings
				WHERE file_id = " . $App->sqlSanitize($_file_id, $dbh) . "
					AND name = " . $App->returnQuotedString($App->sqlSanitize($_name, $dbh));	

			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$myrow = mysql_fetch_assoc($result);
				$rValue = $myrow['string_id'];
			}
		}
		return $rValue;
	}
}
?>