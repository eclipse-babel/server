<div id="maincontent">
<div id="midcolumn">

<h1><?= $pageTitle ?></h1>
<p />
<form name="frmLogin" method="post" enctype="multipart/form-data">

<table cellspacing=4 cellpadding=0 border=0>

<tr><td></td><td colspan=2 style="color:red;"><?= $GLOBALS['g_ERRSTRS'][0] ?></td></tr>
<tr>
  <td>Project:</td><td><input type="text" name="project_id" value="<?= $PROJECT_ID ?>" size="42" maxlength="255" /></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][1] ?></td>
</tr>
<tr>
  <td>File:</td><td><input onchange="document.forms['frmLogin'].fullpath.value = fnFindPath(this.value);" type="file" name="name" value="<?= $NAME ?>" size="80" /></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][2] ?></td>
</tr>
<tr>
  <td>Complete CVS path:</td><td>/cvsroot/eclipse/<input type="text" name="fullpath" value="<?= $FULLPATH ?>" size="80" /></td>
  <td style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][3] ?></td>
</tr>
<tr>
  <td></td><td style='text-align:left;'><input type="submit" name="submit" value="Import" style="font-size:14px;" /></td></tr>
</table>
</form>
<?php
	if($strings != "" && $SUBMIT == "Import") {
		echo '<p>Successfully imported these strings:<br /><ul>';
		$strCount = 0;
		foreach($aStrings as $str) {
			$strCount++;
			echo "<li>" . $str . "</li>";
		}
		echo "</ul>Total: $strCount</p>";
		$FULLPATH = "";
	}
?>
<br />
</div><div id="rightcolumn">
<div class="sideitem">
	<h6>Related Links</h6>
	<ul>
		<li><a href="//www.eclipse.org/babel/">Babel project home</a></li>
	</ul>
</div>
</div>
</div>
<script language="javascript">
	function fnFindPath(_path) {
		if(_path.indexOf('org.eclipse') > 0) {
			return _path.substring(_path.indexOf('org.eclipse'));
		}
		if(_path.indexOf('plugins') > 0) {
			return _path.substring(_path.indexOf('plugins'));
		}
		if(_path.indexOf('features') > 0) {
			return _path.substring(_path.indexOf('features'));
		}
		return "";
	}
</script>