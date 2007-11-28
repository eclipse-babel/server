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
require(BABEL_BASE_DIR . "classes/string/string.class.php");

class File {
  public $errStrs;
  
  public $file_id		= 0;
  public $project_id	= '';
  public $name			= '';
  public $is_active 	= 0;

	
	function save() {
		$rValue = false;
		if($this->name != "" && $this->project_id != "") {
			global $App, $dbh;

			
			if($this->file_id == 0) {
				$this->file_id = $this->getFileID($this->name, $this->project_id);
			}
			
			$sql 	= "INSERT INTO";
			$where = "";
			if($this->file_id > 0) {
				$sql = "UPDATE";
				$where = " WHERE file_id = " . $App->sqlSanitize($this->file_id, $dbh);
			}
			
			$sql .= " files 
						SET file_id 	= " . $App->sqlSanitize($this->file_id, $dbh) . ",
							project_id	= " . $App->returnQuotedString($App->sqlSanitize($this->project_id, $dbh)) . ", 
							name		= " . $App->returnQuotedString($App->sqlSanitize($this->name, $dbh)) . ",
							is_active	= 1" . $where;
			if(mysql_query($sql, $dbh)) {
				if($this->file_id == 0) {
					$this->file_id = mysql_insert_id($dbh);
				}
				$rValue = true;
			}
			else {
				$GLOBALS['g_ERRSTRS'][1] = mysql_error();
			}
		}
		return $rValue;
	}
	
	function getFileID($_name, $_project_id) {
		$rValue = 0;
		if($this->name != "" && $this->project_id != "") {
			global $App, $dbh;

			$sql = "SELECT file_id
				FROM 
					files 
				WHERE name = " . $App->returnQuotedString($App->sqlSanitize($_name, $dbh)) . "
					AND project_id = " . $App->returnQuotedString($App->sqlSanitize($_project_id, $dbh));	

			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$myrow = mysql_fetch_assoc($result);
				$rValue = $myrow['file_id'];
			}
		}
		return $rValue;
	}
	
	function parseProperties($_content) {
		$rValue = "";
		if($_content != "") {
			global $User, $App;
			$lines = explode("\n", $_content);
			foreach($lines as $line) {
				if(strlen($line) > 0 && $line[0] != "#" && $line[0] != ";") {
					$tags = explode("=", trim($line), 2);
					if(count($tags) > 1) {
						if($rValue != "") {
							$rValue .= ",";
						}
						$rValue .= $tags[0];
						
						$String = new String();
						$String->file_id 	= $this->file_id;
						$String->name 		= $tags[0];
						$String->value 		= $tags[1];
						$String->userid 	= $User->userid;
						$String->created_on = $App->getCURDATE();
						$String->is_active 	= 1;
						$String->save();
					}
				}
			}
		}
		return $rValue;
	}
	
}
?>