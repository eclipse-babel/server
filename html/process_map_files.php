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
header("Content-type: text/plain");
include("global.php");
InitPage("");

$headless = 0;
if(!isset($User)) {
	echo "User not defined -- running headless.";
	$User = new User();
	$User->loadFromID(40623);  // genie
	$headless = 1;
}


require(BABEL_BASE_DIR . "classes/file/file.class.php");
$html_spacer = "  ";

global $App, $dbh;

if(!is_dir("/tmp/tmp-babel")) {
	mkdir("/tmp/tmp-babel") || die("Cannot create a working directory");
}
chdir("/tmp/tmp-babel")  || die("Cannot use working directory");




$sql = "SELECT * FROM map_files WHERE is_active = 1 ORDER BY RAND()";
$rs_maps = mysql_query($sql, $dbh);
while($myrow_maps = mysql_fetch_assoc($rs_maps)) {
	echo "Processing map file: " . $myrow_maps['filename'] . " in location: " . $myrow_maps['location'] . "\n";
	
	$tmpdir = "/tmp/tmp-babel/" . $myrow_maps['project_id'];
	if(is_dir($tmpdir)) {
		# zap the directory to make sure CVS versions don't overlap
		exec("rm -rf " . $tmpdir);
	}
	mkdir($tmpdir) || die("Cannot create working directory $tmpdir !");
	chdir($tmpdir) || die("Cannot write to $tmpdir !"); 
	
	$h = fopen($myrow_maps['location'], "rb");
	$file_contents = stream_get_contents($h);
	fclose($h);
	$aLines = split("\n", $file_contents);
	
	
	foreach ($aLines as $line) {
		$line = trim($line);

		# plugin@org.eclipse.emf.query=v200802262150,:pserver:anonymous@dev.eclipse.org:/cvsroot/modeling,,org.eclipse.emf/org.eclipse.emf.query/plugins/org.eclipse.emf.query
		if(preg_match("/^(plugin|fragment)/", $line)) {
			echo $html_spacer . "Processling line: " . $line . "\n";
			$aParts = split("=", $line);
			$aElements = split("@", $aParts[0]);

			if($aElements[0] == "plugin") {
				echo $html_spacer . $html_spacer . "Processling plugin: " . $aParts[1] . "\n";
				$aStuff = parseLocation($aParts[1]);
				
				$tagstring = "";
				if(isset($aStuff['tag'])) {
					$tagstring = "-r " . $aStuff['tag'] . " ";
				}
				if(isset($aStuff['plugin'])) {
					if($aStuff['plugin'] != "") {
						$aElements[1] = $aStuff['plugin'];
					}
				}
				
				$command = "";
				# determine CVS or SVN
				if(isset($aStuff['cvsroot'])) {
					$command = "cvs -d " . $aStuff['cvsroot'] . " co " . $tagstring . $aElements[1];
				}
				elseif( isset($aStuff['svnroot'])) {
					$command = "/usr/local/bin/svn co " . $aStuff['svnroot'] . " --config-dir /tmp";
				}
				echo $html_spacer . $html_spacer ."--> " . $command . "\n";
				
				$out = "";
				if($command != "") {
					$out = shell_exec($command);
				}
				
				# process the output lines for .properties
				$aOutLines = split("\n", $out);
				foreach ($aOutLines as $out_line) {
					$out_line = trim($out_line);
					# remove SVN's multiple spaces
					$out_line = preg_replace("/\s+/", " ", $out_line);
					
					echo $html_spacer . $html_spacer . "CVS out line: " . $out_line . "\n";
					# CVS:
					# U org.eclipse.ant.ui/Ant Editor/org/eclipse/ant/internal/ui/dtd/util/AntDTDUtilMessages.properties
					# SVN: 
					# A org.eclipse.stp.bpmn/trunk/org.eclipse.stp.bpmn/org.eclipse.stp.eid/trunk/org.eclipse.stp.eid.generator.test/build.properties
					if(preg_match("/\.properties$/", $out_line) && !preg_match("/build\.properties$/", $out_line)) {
						# this is a .properties file!
						$file_name = trim(substr($out_line, 2)); 
						echo $html_spacer . $html_spacer . $html_spacer . "Processing .properties file: " . $file_name . "\n";
						
						$File = new File();
						$File->project_id 	= $myrow_maps['project_id'];
						$File->version		= $myrow_maps['version'];
						$File->name 		= $file_name;
						if(!$File->save()) {
							echo $html_spacer . $html_spacer . $html_spacer . $html_spacer . "***ERROR saving file: " . $file_name . "\n";
						}
						else {
							# Start importing the strings!
							$fh      = fopen($file_name, 'r');
							$size 	 = filesize($file_name);
						
							$content = fread($fh, $size);
							fclose($fh);
						
							$strings = $File->parseProperties($content);
							echo $html_spacer . $html_spacer . $html_spacer . $html_spacer . "Strings processed: $strings\n\n";
						}
									
					}
				}
			}			
		}
	}
}
echo "Done.";

if($headless) {
	$User = null;
}

function parseLocation($in_string) {
	# in_string looks something like this:
	# v_832,:pserver:anonymous@dev.eclipse.org:/cvsroot/eclipse,
	# v20080204,:pserver:anonymous@dev.eclipse.org:/cvsroot/birt,,source/org.eclipse.birt.report.designer.core
	# v200802262150,:pserver:anonymous@dev.eclipse.org:/cvsroot/modeling,,org.eclipse.emf/org.eclipse.emf.query/plugins/org.eclipse.emf.query
	# SVN,trunk,http://dev.eclipse.org/svnroot/stp,,org.eclipse.stp.bpmn/trunk/org.eclipse.stp.bpmn
	# svn://dev.eclipse.org/svnroot/stp/org.eclipse.stp.bpmn/trunk/
	
	$aTheseElements = array();
	
	$aLocation = split(",", $in_string);
	foreach($aLocation as $location_part) {
		# TAG  
		if(preg_match("/^[0-9a-zA-Z_]+$/", $location_part) && !isset($aTheseElements['cvsroot'])) {
			$aTheseElements['tag'] = $location_part;
		}
		# CVSROOT
		if(preg_match("/^:.*:.*@.*:\//", $location_part)) {
			$aTheseElements['cvsroot'] = $location_part;
		}
		# SVNROOT
		# SVN,trunk,http://dev.eclipse.org/svnroot/stp,,org.eclipse.stp.bpmn/trunk/org.eclipse.stp.bpmn
		# maps to: svn://dev.eclipse.org/svnroot/stp/org.eclipse.stp.bpmn/trunk/
		if(preg_match("/^(http|svn):\/\//", $location_part)) {
			$location_part = str_replace("http", "svn", $location_part);
			$aTheseElements['svnroot'] = $location_part . "/" . $aLocation[4];
		}
	}
	
	$aTheseElements['plugin'] = substr($in_string, strrpos($in_string, ",") + 1);
	
	return $aTheseElements;
}

?>