<?
// ------...------...------...------...------...------...------...------...------...------...------
require_once("aptana_global/aptana.inc.php");
InitPage("registration_done");

$code = (isset($_GET['code'])?$_GET['code']:0);
// ------...------...------...------...------...------...------...------...------...------...------

include("aptana_global/head.php");
if ($code == 2) {
echo <<< toTheEnd

<div style="height:74px;"></div>

<p>There was an error creating your account. Please contact tech support.
<p /><br /><br />
Thank you for using $g_SITENAME.<br />
&nbsp;&nbsp;&nbsp;&nbsp;- The Crew<br /><br />

toTheEnd;
}
else
{
echo <<< toTheEnd

<div style="height:74px;"></div>

<p>Congratulations! You have successfully completed the 
registration process. Just <a href="/login.php">login</a> to start using and 
contributing to {$g_SITENAME}.<p /><br /><br />
Thank you for using $g_SITENAME.<br />
&nbsp;&nbsp;&nbsp;&nbsp;- The Crew<br /><br />

toTheEnd;
}
include("aptana_global/foot.php");

// ------...------...------...------...------...------...------...------...------...------...------

?>
