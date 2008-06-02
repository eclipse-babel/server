<div id="maincontent">
<div id="midcolumn">

<style>
.head {
	background-color: SteelBlue;
  	color: white;
	margin: 0px;	
	font-size: 14px;
	padding: 2px;
}

.odd {
	background-color: LightSteelBlue;
}

.foot {
	background-color: LightGray;
}

.
</style>

<h1><?= $pageTitle ?></h1>
<form method="post">
<table>
<tr><td>Show only this project</td>
 <td><select name="project_version">
 <option value="">All projects</option>
<?php
	while($myrow = mysql_fetch_assoc($rs_p_list)) {
		$selected = "";
		if($myrow['project_id'] . "|" . $myrow['version'] == $PROJECT_ID . "|" . $VERSION) {
			$selected = 'selected="selected"';
		}
		echo "<option value='" . $myrow['project_id'] . "|" . $myrow['version']  . "' $selected>" . $myrow['project_id'] . " " . $myrow['version'] . "</option>";
	}
 ?></select></td>
 <td>Show only this language</td>
   <td><select name="language_id">
   <option value="All">All languages</option>
<?php
	while($myrow = mysql_fetch_assoc($rs_l_list)) {
		$selected = "";
		if($myrow['language_id'] == $LANGUAGE_ID) {
			$selected = 'selected="selected"';
		}
		echo "<option value='" . $myrow['language_id'] . "' $selected>" . $myrow['name'] . "</option>";
	}
 ?></select></td>
 <td>Show last <select name="limit">
   <option value="25">25</option>
   <option value="50">50</option>
   <option value="100">100</option>
   <option value="200">200</option>
   <option value="500">500</option>
   <option value="1000">1000</option>
</select> translations</td>
 </tr>
 <tr><td colspan="4"><input type="submit" value="Apply filter" /></td></tr></table></form>
<table cellspacing=1 cellpadding=2 border=0 width="950">
<tr class="head">
<?php
    $i = 0;
	while($i < mysql_num_fields($rs_p_stat)) {
		 $meta = mysql_fetch_field($rs_p_stat, $i);
		 $align = "";
		 if($meta->numeric) {
		 	$align="align='right'";
		 }
		 echo "<td $align><b>" . $meta->name . "</b></td>";
		 $i++;
	}
 ?></tr>
<?php
	$rowcount=0;
	while($myrow = mysql_fetch_assoc($rs_p_stat)) {
		$rowcount++;
		$class="";
		if($rowcount % 2) {
			$class="class='odd'";
		}
		echo "<tr $class>";
		$i = 0;
		while($i < mysql_num_fields($rs_p_stat)) {
			$meta = mysql_fetch_field($rs_p_stat, $i);
			$align = "";
		 	if($meta->numeric) {
		 		$align="align='right'";
			 }
			
			echo "<td $align>" . $myrow[$meta->name] . "</td>";
			$i++;
		}
		echo "</tr>";
	}
 ?>
 <tr class="foot">
 	<td colspan="<?= $i ?>"> <?= $rowcount ?> row<?= $rowcount > 1 || $rowcount == 0 ? "s" : "" ?> found.</td>
 </tr>
 </table>
 