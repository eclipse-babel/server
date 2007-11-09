<?
require_once('aptana_global/code/login.code.php');
require_once('aptana_global/head.php');
echo <<< toTheEnd
<p />
<center>
<form method="post">
<div id="title" style='font-weight:bold;font-size:20pt;'>aptana.global</div>

<table cellspacing=4 cellpadding=0 width=500px; border=0>
<tr><td></td><td id="formErr" colspan=2>{$GLOBALS['g_ERRSTRS'][0]}&nbsp;</td></tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">User ID:</td><td style='text-align:left;'><input type="text" name="username" value="$post_username"/></td>
  <td id="formErr" style='width:100px;'>{$GLOBALS['g_ERRSTRS'][1]}&nbsp;</td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td id="formLbl">Password:</td><td style='text-align:left;'><input type="password" name="password" value="$post_password"/></td>
  <td id="formErr" style='width:100px;'>{$GLOBALS['g_ERRSTRS'][2]}&nbsp;</td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="checkbox" name="remember" value="1" $post_remember />remember me </td>
</tr>
<tr>
  <td id="formErr" style='width:100px;'>&nbsp;</td>
  <td></td><td style='text-align:left;'><input type="submit" name="postIT" value="Login" style="font-size:14px;" /></td></tr>
</table>
</form>
<p />
<br />
<br />
<a href="/register.php">Sign up</a>

</center>

toTheEnd;
require_once('aptana_global/foot.php');    
?>
