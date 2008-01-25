<h1>Map files for project: <?= $PROJECT_ID ?></h1>
<table cellspacing=1 cellpadding=3 border=1 width="100%">
<tr>
  <td>Project</td><td align="right">Version</td><td>URL</td><td>Delete</td></tr>
<?php
	while($myrow = mysql_fetch_assoc($rs_map_file_list)) {
		echo "<tr><td>"	. $myrow['project_id'] . "</td>
		<td align='right'>" . $myrow['version'] . "</td>
		<td><a href='" . $myrow['location'] . "' target='new'>" . $myrow['location'] . "</a></td>
		<td><a onclick=\"javascript:return fnConfirm();\" href='map_files.php?submit=delete&project_id=" . $PROJECT_ID . "&version=" . $VERSION . "&filename=" . $myrow['filename'] . "'><img border=0 src='http://dev.eclipse.org/small_icons/actions/process-stop.png'></a></td></tr>";
	}
?>
</table>
<script language="javascript">
	function fnConfirm() {
		return confirm('Sure?');
	}
</script>