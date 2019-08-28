<div id="maincontent">
<div id="midcolumn">
<h1><?= $pageTitle ?></h1>

<div id="index-page">

	<a href="https://accounts.eclipse.org/"><img src="<?php echo imageRoot() ?>/large_icons/categories/preferences-desktop-peripherals.png">	<h2>An Eclipse account is all you need</h2></a>
    <br style='clear: both;'>
	<p>If you don't already have an Eclipse account then <a href="https://accounts.eclipse.org/">create one today</a>.
	If logging in doesn't work after a few minutes, please contact <a href="mailto:webmaster@eclipse.org">webmaster@eclipse.org</a>.</p>

    <br style='clear: both;'>
	<p>If you already have an Eclipse account, then authenticate and start helping Eclipse speak your language.</p>

<form style="margin-left: 35px;" name="frmLogin" method="POST">
<div>

	<?php 
	if($GLOBALS['g_ERRSTRS'][0] || $GLOBALS['g_ERRSTRS'][1]){ 
			?>
			  <img style='margin-left: 70px;' src='<?php echo imageRoot() ?>/small_icons/actions/process-stop.png'>
		      <div style='color: red; font-weight: bold; '><?=$GLOBALS['g_ERRSTRS'][0]?></div>
		      <div style='color: red; font-weight: bold; '><?=$GLOBALS['g_ERRSTRS'][1]?></div>
		      <br style='clear: both;'>
		    <?php
	    }else{
			?>
		    	<br style='clear: both;'>
		   <?php
	    }
	 ?>
</div>

<div style='margin-left: 65px;'>
<input type="submit" name="oauth" value="Authenticate with Eclipse account" style="font-size:14px;" />
</div>

</form>
</div>
</div>
<br class='clearing'>
</div>
