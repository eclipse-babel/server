<script src="js/mapFiles.js" type='text/javascript'></script>
<script src="js/train.js" type='text/javascript'></script>
<div id="maincontent">
<div id="midcolumn">
<h1><?= $pageTitle ?></h1>
<p>Use this form to define the map files or update sites for your project. The map files or update sites are read nightly. Any .properties files except those listed in the plugin exclude patterns will be parsed and imported into Babel, allowing the community to translate the externalized strings.</p>
<form name="form1" method="post">
<table cellspacing=4 cellpadding=0 border=0>
<tr>
  <td style="color:red;"><?= $GLOBALS['g_ERRSTRS'][0] ?></td>
</tr>
<tr>
  <td>Project:</td>
  <td><select name="project_id" onchange="fnSetVersionList();" style="width:150px"><?php
	while($myrow = mysql_fetch_assoc($rs_project_list)) {
		$selected = "";
		if($myrow['project_id'] == $PROJECT_ID) {
			$selected = 'selected="selected"';
		}
		echo "<option value='" . $myrow['project_id'] . "' $selected>" . $myrow['project_id'] . "</option>";
	}
  ?></select></td>
  <td style="color:red;"><?= $GLOBALS['g_ERRSTRS'][1] ?></td>
</tr>
<tr>
  <td>Release Version:</td>
  <td><select name="version" onchange="fnUpdateFileList();" style="width:150px"></select> * Indicates map files or update sites present</td> 
  <td style="color:red;"><?= $GLOBALS['g_ERRSTRS'][4] ?></td>
</tr>
<tr>
  <td>Release Train:</td>
  <td><select name="train_id" style="width:150px"><?php
	while($myrow = mysql_fetch_assoc($rs_train_list)) {
		$selected = "";
		if($myrow['train_id'] == $TRAIN_ID) {
			$selected = 'selected="selected"';
		}
		echo "<option value='" . $myrow['train_id'] . "' $selected>" . $myrow['train_id'] . "</option>";
	}
  ?></select></td>
  <td></td>
</tr>
<tr>
  <td>URLs to Map Files or Update Sites:</td>
  <td>
    <input id="urlType-mapFiles" name="urlType" type="radio" value="mapFiles" checked>Map Files</input>
    <input id="urlType-updateSites" name="urlType" type="radio" value="updateSites">Update Sites</input>
  </td>
  <td style="color:red;"><?= $GLOBALS['g_ERRSTRS'][2] ?></td>
</tr>
<tr>
  <td colspan=3><textarea id="files-area" name="fileFld" onclick="fnClickTextFilesArea();" rows=5 cols="100"></textarea></td>
</tr>
<tr>
  <td colspan=2>Plugin Exclude Patterns: (regular expressions, example: <b>/^org\.junit\..*$/</b>)</td>
  <td style="color:red;"><?= $GLOBALS['g_ERRSTRS'][2] ?></td>
</tr>
<tr>
  <td colspan=3><textarea id="patterns-area" name="patterns" onclick="fnClickTextPatternsArea();" rows="5" cols="100"></textarea></td>
</tr>
<tr>
  <td><input type="submit" name="submit" value="Save" style="font-size:14px;" /></td>
  <td></td>
  <td></td>
</tr>
</table>
</form>
</div>
<br class='clearing'>
</div>
<script language="javascript">
	function fnSetVersionList() {
		document.form1.version.options.length = 0;
		
		if(typeof(versions[document.form1.project_id.value]) != "undefined") {
			for(i = 0; i < versions[document.form1.project_id.value].length; i++) {
				var opt = document.createElement("OPTION");
				document.form1.version.options.add(opt);
				document.form1.version.options[i].text 		= versions[document.form1.project_id.value][i];
				document.form1.version.options[i].value 	= versions[document.form1.project_id.value][i];
				if(versions[document.form1.project_id.value][i] == "<?= $VERSION ?>") {
					document.form1.version.options[i].selected = "selected";
				}
			}
		}
		else {
			var opt = document.createElement("OPTION");
			document.form1.version.options.add(opt);
			document.form1.version.options[0].text 		= "unspecified";
			document.form1.version.options[0].value 	= "unspecified";
		}
		
		fnUpdateFileList();
	}

	function cleanVersion(_value) {
		return _value.replace(/^\* /, "");
	}

	function fnClickTextFilesArea() {
		if(document.form1.fileFld.value.substr(0,12) == "No map files") {
			document.form1.fileFld.value = "";
		}
	}
	
	function fnClickTextPatternsArea() {
		if(document.form1.patterns.value.substr(0,26) == "No plugin exclude patterns") {
			document.form1.patterns.value = "";
		}
	}

	function fnUpdateFileList() {
        var project_id = document.form1.project_id.value;
		var version = cleanVersion(document.form1.version.options[document.form1.version.selectedIndex].value);
		showMapFiles(project_id, version);		
		showPluginExcludePatterns(project_id, version);		
		setUrlType(project_id, version);		
		fnSetTrain();
	}
	
	function fnSetTrain() {
		<?# Update train according to selected project/version  ?>
		if(typeof(project_trains[document.form1.project_id.value][cleanVersion(document.form1.version.options[document.form1.version.selectedIndex].value)]) != "undefined") {
			for(i = 0; i < document.form1.train_id.length; i++) {
				document.form1.train_id.options[i].selected = "";
				if(document.form1.train_id.options[i].value == project_trains[document.form1.project_id.value][cleanVersion(document.form1.version.options[document.form1.version.selectedIndex].value)]) {
					document.form1.train_id.options[i].selected = "selected";
				}
			}
		}
	}

<?php
global $addon;
echo $addon->callHook('validate_map_file_url');
?>

	var versions = new Array();
	
<?php
	$prev_project = "";
	$count = 0;
	while($myrow = mysql_fetch_assoc($rs_version_list)) {
		if($prev_project != $myrow['project_id']) {
			if($count > 0) {
				echo "];
";
			}
			echo "versions['" . $myrow['project_id'] . "'] = [";
			$count = 0;
		}
		if($count > 0) {
			echo ",";
		}
		$str = "";
		if($myrow['map_count'] > 0) {
			$str = "* ";
		}
		
		echo "\"$str" . $myrow['version'] . "\"";
		$count++;
		$prev_project = $myrow['project_id'];
	}
	echo "];";
 ?>
 

	var project_trains = new Array();
	
<?php
	$prev_project = "";
	$count = 0;
	while($myrow = mysql_fetch_assoc($rs_train_project_list)) {
		if($prev_project != $myrow['project_id']) {
			if($count > 0) {
				echo "};
";
			}
			echo "project_trains['" . $myrow['project_id'] . "'] = {";
			$count = 0;
		}
		if($count > 0) {
			echo ",";
		}
		
		echo "'" . $myrow['version'] . "' : '" . $myrow['train_id'] . "'";
		$count++;
		$prev_project = $myrow['project_id'];
	}
	echo "};";
 ?>
	fnSetVersionList();
 </script>
