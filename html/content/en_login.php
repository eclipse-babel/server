<div id="maincontent">
<div id="midcolumn">



<h1><?= $pageTitle ?></h1>

<div id="index-page">

	<a href="https://bugs.eclipse.org/bugs/createaccount.cgi"><img src="http://dev.eclipse.org/large_icons/categories/preferences-desktop-peripherals.png">	<h2>A Bugzilla Account is all you need</h2></a>
    <br style='clear: both;'>
	<p>If you don't already have an Eclipse Bugzilla account then <a href="https://bugs.eclipse.org/bugs/createaccount.cgi">create one today</a>.  
	It takes Babel a few minutes to receive your new Bugzilla account information. 
	If logging in doesn't work right away try again before complaining.</p>

    <br style='clear: both;'>
	<p>If you already have an Eclipse Bugzilla account, then log in and started helping Eclipse speak your language.</p>



<form style="margin-left: 35px;" name="frmLogin" method="post">
<div>

	<?php 
		if($GLOBALS['g_ERRSTRS'][0]){ 
			?>
			  <img style='margin-left: 70px;' src='http://dev.eclipse.org/small_icons/actions/process-stop.png'>
		      <div style='color: red; font-weight: bold; '><?=$GLOBALS['g_ERRSTRS'][0]?></div>
		      <br style='clear: both;'>
		    <?
	    }else{
			?>
	    		<img style='margin-left: 70px;' src="http://dev.eclipse.org/small_icons/emblems/emblem-important.png">	<h2 style='font-size: 14px; margin-top: 0px; background-color: yellow;'>Use your Bugzilla login information</h2>
		    	<br style='clear: both;'>
		   <?
	    }
	 ?>
	
	<div style='width: 70px; float: left;'>Email:</div>
	<input type="text" name="username" value="<?= $USERNAME ?>" size="42" maxlength="255" /> 
	
	<?php if($GLOBALS['g_ERRSTRS'][1]){ print "<div>".$GLOBALS['g_ERRSTRS'][1]."</div>"; } ?>
	
</div>

<div>
	<div style='width: 70px; float: left;'>Password:</div>
	<input type="password" name="password" value="<?= $PASSWORD ?>" size="42" maxlength="255" /> 
	<?= $GLOBALS['g_ERRSTRS'][2] ?>
</div>

<div style='margin-left: 65px;'>
	<input type="checkbox" name="remember" value="1" <?= $REMEMBER ?> />remember me
	<div style='float: right; margin-right: 100px;'><a href="https://bugs.eclipse.org/bugs/index.cgi?GoAheadAndLogIn=1#forgot">Forgot my password</a> </div>
	
</div>

<div style='margin-left: 65px;'>
<input type="submit" name="submit" value="Login" style="font-size:14px;" />
</div>

</form>
	
</div>


<!--  

<p>Welcome to Babel - the Eclipse translation tool. Help globalize Eclipse by providing 
translations for the various messages, dialogs and strings found in Eclipse. Rate existing translations to help us
provide high-quality translation packs.</p>

<p>Use your Eclipse Bugzilla email address and 
password to login. If you don't have a Bugzilla account, you can <a href="https://bugs.eclipse.org/bugs/createaccount.cgi">create one here</a>.</p>
<form name="frmLogin" method="post">

<table cellspacing=4 cellpadding=0 border=0>
<tr><td></td><td id="formErr" colspan=2 style="color:red;"><?= $GLOBALS['g_ERRSTRS'][0] ?></td></tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Email:</td><td style='text-align:left;'><input type="text" name="username" value="<?= $USERNAME ?>" size="42" maxlength="255" /></td>
  <td id="formErr" style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][1] ?></td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Password:</td><td style='text-align:left;'><input type="password" name="password" value="<?= $PASSWORD ?>" size="42" maxlength="255" /> <a href="https://bugs.eclipse.org/bugs/index.cgi?GoAheadAndLogIn=1#forgot">Forgot my password</a></td>
  <td id="formErr" style='width:100px; color:red;'><?= $GLOBALS['g_ERRSTRS'][2] ?></td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="checkbox" name="remember" value="1" <?= $REMEMBER ?> />remember me </td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="submit" name="submit" value="Login" style="font-size:14px;" /></td></tr>
</table>
</form>
<p><br /></p>
<p>The content on this site is governed by the Eclipse.org <a href="http://www.eclipse.org/legal/termsofuse.php">Terms Of Use</a>.
</div><div id="rightcolumn">

<div class="sideitem">
	<h6>Related Links</h6>
	<ul>
		<li><a href="//www.eclipse.org/babel/">Babel project home</a></li>
	</ul>
</div>
-->

</div>

</div>
<script language="javascript">
	document.forms['frmLogin'].username.focus();
</script>