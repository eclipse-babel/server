<div id="maincontent">
<div id="midcolumn">

<style>
#container{
	width: 100%;
	cursor: auto;
	margin-left:5px;
}

.head {
	background-color: SteelBlue;
  	color: white;
	margin: 0px;	
	font-size: 14px;
	padding: 2px;
}
li {
	padding-bottom: 5px;
}
</style>

<?include 'en_recent_html_common.php' ?>
<ul>
<?php
	$prev_date = "";
	$rowcount=0;
	while($myrow = mysql_fetch_assoc($rs_p_stat)) {
		$rowcount++;
		if($prev_date != substr($myrow['created_on'],0,10)) {
			$prev_date = substr($myrow['created_on'],0,10);
			echo "<h2>$prev_date</h2>";
		}
		echo "<li>" . substr($myrow['created_on'],11,5) . " " . $myrow['string_value'] . " -> " . $myrow['translation'] . " [<a href='#'>" . $myrow['string_key'] . "</a>] <b>" . $myrow['project_id'] . " " . $myrow['version'] . "</b> (" . $myrow['who'] . ")"; 
		echo "</li>";
		
		// $myrow['string_key'] . " " . 
	}
 ?>
 </ul>
 <?= $rowcount ?> row<?= $rowcount > 1 || $rowcount == 0 ? "s" : "" ?> found.</td>