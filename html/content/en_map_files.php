<div id="maincontent">
<div id="midcolumn">

<h1><?= $pageTitle ?></h1>
<p>Use this form to define the map files for your project. The map files are read nightly, and any .properties files (except build.properties) contained in the plugins they reference will be parsed and imported into Babel, allowing the community to translate the externalized strings.</p>  
<p>This page is only accessible by Eclipse committers.</p>
<form name="form1" method="post">
<table cellspacing=4 cellpadding=0 border=0 width="950">
<tr><td></td><td colspan=2 style="color:red;"><?= $GLOBALS['g_ERRSTRS'][0] ?></td></tr>
<tr>
  <td>Project:</td><td><select name="project_id" onchange="fnSetVersionList();">
<?php
	while($myrow = mysql_fetch_assoc($rs_project_list)) {
		$selected = "";
		if($myrow['project_id'] == $PROJECT_ID) {
			$selected = 'selected="selected"';
		}
		echo "<option value='" . $myrow['project_id'] . "' $selected>" . $myrow['project_id'] . "</option>";
	}
 ?></select></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][1] ?></td>
</tr>
<tr>
  <td>Release Version</td><td><select name="version" onchange="fnUpdateFileList();">
</select></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][4] ?></td>
</tr>
<tr>
  <td><a href="http://dev.eclipse.org/viewcvs/index.cgi">ViewCVS</a> download URL to map file:</td><td><input type="text" name="location" value="<?= $LOCATION ?>" size="80" onchange="fnCheckUrl();" /></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][2] ?></td>
</tr>
<tr>
  <td>&#160;</td><td>e.g. <a href="http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core.map?view=co">http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core.map?view=co</a></td>
</tr>
<tr>
  <td>&#160;</td><td><b>NOTE: </b>If you're defining map files for a Release, you must use the download link to the CVS TAG of that release.  This is the pathrev=R3_4 parameter in this example:<br />
  <a href="http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core.map?view=co&pathrev=R3_4">http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core.map?view=co&pathrev=R3_4</a>
  </td>
</tr>
<tr>
  <td>File name: </td><td><input type="text" name="filename" value="<?= $FILENAME ?>" size="32" /></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][5] ?></td>
</tr>

<tr>
  <td></td><td><input type="submit" name="submit" value="Save" style="font-size:14px;" /></td></tr>
  <tr>
	<td colspan="2"><iframe id="fileShow" name="somefiles" width="100%" height="200"
		  style="border: 1px black solid"
		  src="">
		</iframe>
	</td>
  </tr>
</table>
</form>
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
	
	function fnUpdateFileList() {
		source = "map_files.php?submit=showfiles&project_id=" + document.form1.project_id.value + "&version=" + document.form1.version.options[document.form1.version.selectedIndex].value;
		document.getElementById("fileShow").src = source;
	}
	
	function fnCheckUrl() {
		if(!document.form1.location.value.match(/view=co/)) {
			alert("The ViewCVS URL must contain view=co");
			document.form1.submit.disabled = "disabled";
		}
		else {
			document.form1.submit.disabled = "";

			var re = /\/([A-Za-z0-9_-]+\.map)/;
			var match = re.exec(document.form1.location.value)
			document.form1.filename.value = match[1];
		}
	}

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
		echo "\"" . $myrow['version'] . "\"";
		$count++;
		$prev_project = $myrow['project_id'];
	}
	echo "];";
 ?>
 
   fnSetVersionList();
   document.form1.submit.disabled = "disabled";
 
 </script>