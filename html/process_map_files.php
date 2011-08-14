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
 *    Antoine Toulmé - Bug 248917
 *    Motorola  - Change SVN map file format to follow SVN PDE
 *    Gustavo de Paula - Bug 261252
 *    Kit Lo (IBM) - Bug 266250, Map file processor not running properly on live server
 *    Kit Lo (IBM) - Bug 272176, Support "bundle" element type in map file
 *    Kit Lo (IBM) - Bug 257332, NLS warnings appear unnecessarily in runtime log
 *    Kit Lo (IBM) - Bug 302834, Add plugin filtering supports to map files process
*******************************************************************************/
$temp_dir = "/tmp/tmp-babel/";
$files = array();
$files_collected = array(array());

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
$html_spacer = "  ";

global $dbh;

$temp_downloads_dir = $temp_dir . "downloads/";
if (!is_dir($temp_downloads_dir)) {
  mkdir($temp_downloads_dir) || die("Cannot create a working directory");
}
chdir($temp_downloads_dir)  || die("Cannot use working directory");

$sql = "SELECT * FROM map_files AS m 
INNER JOIN release_train_projects AS r ON r.project_id = m.project_id AND r.version = m.version 
INNER JOIN release_trains AS t on t.train_id = r.train_id 
WHERE m.is_active = 1 AND m.is_map_file = 1 AND t.is_active = 1";
$rs_maps = mysql_query($sql, $dbh);
while ($myrow_maps = mysql_fetch_assoc($rs_maps)) {
  $location = $myrow_maps['location'];
  $project_id = $myrow_maps['project_id'];
  $version = $myrow_maps['version'];
  $properties_file_count = 0;

  # Parse each properties file for this project version
  echo "Start processing properties files in project $project_id version $version...\n";
  echo "  Map file location: $location\n";

  # Collect all files for this project version
  if (!(isset($files_collected[$project_id]) && isset($files_collected[$project_id][$version]))) {
    $files_collected[$project_id][$version] = 1;
    $sql = "SELECT * FROM files WHERE project_id = \"$project_id\" AND version = \"$version\"";
    $rs_files = mysql_query($sql, $dbh);
    while($myrow_files = mysql_fetch_assoc($rs_files)) {
      $file = new File();
      $file->project_id = $myrow_files['project_id'];
      $file->version = $myrow_files['version'];
      $file->name = $myrow_files['name'];
      $file->plugin_id = $myrow_files['plugin_id'];
      $file->file_id = $myrow_files['file_id'];
      $file->is_active = $myrow_files['is_active'];
      $files[$file->file_id] = $file;
    }
  }

  # Collect all plugin exclude patterns for this project version
  $sql = "SELECT pattern FROM plugin_exclude_patterns WHERE project_id = \"$project_id\" AND version = \"$version\"";
  $rs_patterns = mysql_query($sql, $dbh);
  $patterns = Array();
  # Add default exclude patterns
  $patterns[] = "/^.*\/feature.properties$/";
  $patterns[] = "/^.*\/build.properties$/";
  $patterns[] = "/^.*\/pom.properties$/";
  $patterns[] = "/^.*\/css\/.*$/";
  $patterns[] = "/^.*\.source\/.*$/";
  $patterns[] = "/^.*\.test\/.*$/";
  $patterns[] = "/^.*\.tests\/.*$/";
  $patterns[] = "/^.*\.testing\/.*$/";
  while ($myrow_patterns = mysql_fetch_assoc($rs_patterns)) {
    $patterns[] = $myrow_patterns['pattern'];
  }

  # echo "Processing map file: " . $myrow_maps['filename'] . " in location: " . $myrow_maps['location'] . "\n";
    
  $tmpdir = $temp_downloads_dir . str_replace(" ", "_", $myrow_maps['project_id']);
  if (is_dir($tmpdir)) {
    # zap the directory to make sure CVS versions don't overlap
    exec("rm -rf " . $tmpdir);
  }
  mkdir($tmpdir) || die("Cannot create working directory $tmpdir !");
  chdir($tmpdir) || die("Cannot write to $tmpdir !"); 
    
  $h = fopen($myrow_maps['location'], "rb");
  $file_contents = stream_get_contents($h);
  fclose($h);
  $file_contents = ereg_replace("\r\n?", "\n", $file_contents);
  $aLines = split("\n", $file_contents);

  foreach ($aLines as $line) {
    $line = trim($line);

    # $line looks something like this:
    # See http://help.eclipse.org/help33/index.jsp?topic=/org.eclipse.pde.doc.user/guide/tasks/pde_fetch_phase.htm for more info
    # plugin@org.eclipse.emf.query=v200802262150,:pserver:anonymous@dev.eclipse.org:/cvsroot/modeling,,org.eclipse.emf/org.eclipse.emf.query/plugins/org.eclipse.emf.query
    # plugin@org.eclipse.equinox.frameworkadmin=CVS,tag=R34x_v20080910,cvsRoot=:pserver:anonymous@dev.eclipse.org:/cvsroot/rt,path=org.eclipse.equinox/p2/bundles/org.eclipse.equinox.frameworkadmin
    # bundle@org.eclipse.wst.xml.xpath.ui=v200902122100,:pserver:anonymous@dev.eclipse.org:/cvsroot/webtools,,sourceediting/plugins/org.eclipse.wst.xml.xpath.ui

    # Bug 272176 - Support "bundle" element type in map file
    if (preg_match("/^(plugin|bundle)/", $line)) {
      # echo $html_spacer . "Processing line: " . $line . "\n";
      $aParts = split("=", $line);
      $aElements = split("@", $aParts[0]);
      $plugin_id = $aElements[1];
      # Bug 272176 - Support "bundle" element type in map file
      if ($aElements[0] == "plugin" || $aElements[0] == "bundle") {
        $plugin = $aParts[1];
        if ($aParts[1] == "CVS,tag") {
          $tagPart = split(",", $aParts[2]);
          $cvsRootPart = split(",", $aParts[3]);
          $plugin = $tagPart[0] . "," . $cvsRootPart[0] . "," . $aParts[4];
        } elseif ($aParts[1] == "SVN,url") {
          # Support STEM project SVN syntax
          # plugin@org.eclipse.stem.core=SVN,url=http://dev.eclipse.org/svnroot/technology/org.eclipse.stem,tag=trunk,path=core/org.eclipse.stem.core
          #
          # maps $plugin to:
          # SVN,<tagPath>[:revision],<svnRepositoryURL>,<preTagPath>,<postTagPath>
          $svnRootPart = split(",", $aParts[2]);
          $svnTagPart = split(",", $aParts[3]);
          $plugin = "SVN," . $svnTagPart[0] . "," . $svnRootPart[0] . ",," . $aParts[4];
        }
        $aStuff = parseLocation($plugin);

        $tagstring = "";
        if (isset($aStuff['tag'])) {
          $tagstring = "-r " . $aStuff['tag'] . " ";
        }
        if (isset($aStuff['plugin'])) {
          if ($aStuff['plugin'] != "") {
            $aElements[1] = $aStuff['plugin'];
          }
        }

        $command = "";
        # determine CVS or SVN
        if (isset($aStuff['cvsroot'])) {
          $command = "cvs -q -d " . $aStuff['cvsroot'] . " co " . $tagstring . $aElements[1];
        } elseif (isset($aStuff['svnroot'])) {
          $command = "/usr/local/bin/svn co " . $aStuff['svnroot'] . " --config-dir /tmp";
        }
        # echo $html_spacer . $html_spacer ."--> " . $command . "\n";

        $out = "";
        if ($command != "") {
          $out = shell_exec($command);
        }

        # process the output lines for .properties
        $aOutLines = split("\n", $out);
        foreach ($aOutLines as $out_line) {
          $out_line = trim($out_line);
          # remove SVN's multiple spaces
          $out_line = preg_replace("/\s+/", " ", $out_line);

          # echo $html_spacer . $html_spacer . "CVS out line: " . $out_line . "\n";
          # CVS:
          # U org.eclipse.ant.ui/Ant Editor/org/eclipse/ant/internal/ui/dtd/util/AntDTDUtilMessages.properties
          # SVN: 
          # A org.eclipse.stp.bpmn/trunk/org.eclipse.stp.bpmn/org.eclipse.stp.eid/trunk/org.eclipse.stp.eid.generator.test/build.properties
          if (preg_match("/\.properties$/", $out_line)) {
            # this is a .properties file!
            $repository_file_name = trim(substr($out_line, 2));
            $file_name = $repository_file_name;

            # remove optional outer dirs, e.g. 'pde/ui/'
            $pos = strripos($file_name, 'org.');
            if ($pos !== false) {
              $file_name = substr($file_name, $pos);
            }
            $pos = strripos($file_name, 'com.');
            if ($pos !== false) {
              $file_name = substr($file_name, $pos);
            }
 
            $pattern = 
              '/^
              (.*?)?                # $1 plugin name
              \/                    # slash
              (.*?\/)?              # $2 dir name
              ([^\/]+[.]properties) # $3 file name
              $/ix';
            $plugin_name_string = preg_replace($pattern, '$1', $file_name);
            $dir_name_string = preg_replace($pattern, '$2', $file_name);
            $file_name_string = preg_replace($pattern, '$3', $file_name);

            # remove optional source dir, e.g. 'src' or 'src_ant'
            $pos = stripos($dir_name_string, 'org/');
            if ($pos !== false) {
              $dir_name_string = substr($dir_name_string, $pos);
            }
            $pos = strripos($dir_name_string, 'com/');
            if ($pos !== false) {
              $dir_name_string = substr($dir_name_string, $pos);
            }

            $file_name = $plugin_name_string . "/" . $dir_name_string . $file_name_string;

            $file_id = File::getFileID($file_name, $myrow_maps['project_id'], $myrow_maps['version']);
            $properties_file_count = $properties_file_count + 1;

            # Match plugin exclude list
            $match = false;
            foreach ($patterns as $pattern) {
              if (preg_match($pattern, $file_name)) {
                $match = true;
                break;
              }
            }

            if (!$match) {
              if ($file_id > 0 && $files[$file_id] != null) {
                # Existing file
                $file = $files[$file_id];
                $file->is_active = 1;
                unset($files[$file_id]);
              } else {
                # New file
                $file = new File();
                $file->project_id = $myrow_maps['project_id'];
                $file->version = $myrow_maps['version'];
                $file->name = $file_name;
                $file->plugin_id = $plugin_id;
                $file->is_active = 1;
              }
              if (!$file->save()) {
                echo $html_spacer . $html_spacer . $html_spacer . $html_spacer . "***ERROR saving file: " . $file_name . "\n";
              } else {
                $file->parseProperties(file_get_contents($repository_file_name));
                echo "  $file_name\n";
              }
            } else {
              echo "  !!! Excluding $file_name\n";
            }
          }
        }
      }            
    }
  }
  echo "Done processing " . $properties_file_count . " properties files in project $project_id version $version\n\n";
}

# Deactivate the rest of the files
echo "Start deactivating inactive properties files in all projects above...\n";
foreach ($files as $file) {
  if ($file->is_active == 1) {
    $file->is_active = 0;
    if (!$file->save()) {
      echo $html_spacer . $html_spacer . $html_spacer . $html_spacer . "***ERROR saving file: " . $file->name . "\n";
    }
    echo "  " . $file->name . "\n";
  } else {
    unset($files[$file->file_id]);
  }
}
echo "Done deactivating " . sizeof($files) . " inactive properties files in all projects above\n\n";

if (is_dir($temp_downloads_dir)) {
  exec("rm -rf $temp_downloads_dir");
}

if ($headless) {
  $User = null;
}

echo "Done\n";

function parseLocation($in_string) {
  # in_string looks something like this:
  # v_832,:pserver:anonymous@dev.eclipse.org:/cvsroot/eclipse,
  # v20080204,:pserver:anonymous@dev.eclipse.org:/cvsroot/birt,,source/org.eclipse.birt.report.designer.core
  # v200802262150,:pserver:anonymous@dev.eclipse.org:/cvsroot/modeling,,org.eclipse.emf/org.eclipse.emf.query/plugins/org.eclipse.emf.query
  # SVN,tags/1.0M5,http://dev.eclipse.org/svnroot/dsdp/org.eclipse.mtj,,features/org.eclipse.mtj
  # svn://dev.eclipse.org/svnroot/stp/org.eclipse.stp.bpmn/trunk/

  $aTheseElements = array();

  $aLocation = split(",", $in_string);
  foreach($aLocation as $location_part) {
    # TAG  
    # Bug 257332, NLS warnings appear unnecessarily in runtime log
    if (preg_match("/^[0-9a-zA-Z_]+/", $location_part) && !isset($aTheseElements['cvsroot'])) {
      $aTheseElements['tag'] = $location_part;
    }
    # CVSROOT
    if (preg_match("/^:.*:.*@.*:\//", $location_part)) {
      $aTheseElements['cvsroot'] = $location_part;
    }
    # SVNROOT
    # SVN,<tagPath>[:revision],<svnRepositoryURL>,<preTagPath>,<postTagPath>
    # maps to: svn://<svnRepositoryURL>/<preTagPath>/<tagPath>/<postTagPath>
    if (preg_match("/^(http|svn):\/\//", $location_part)) {
      $location_part = str_replace("http", "svn", $location_part);
      if ($aLocation[3] == ' ' || $aLocation[3] == '') {
        $aTheseElements['svnroot'] = $location_part . "/" . $aLocation[1] . "/" . $aLocation[4];
      } else {
        $aTheseElements['svnroot'] = $location_part . "/" . $aLocation[3] . "/" . $aLocation[1] . "/" . $aLocation[4];         
      }
    }
  }

  $aTheseElements['plugin'] = substr($in_string, strrpos($in_string, ",") + 1);

  return $aTheseElements;
}
?>
