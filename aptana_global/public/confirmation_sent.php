<?
// ------...------...------...------...------...------...------...------...------...------...------
require_once("aptana_global/aptana.inc.php");
InitPage('confirmation_sent');
$email = GrabSessionVar('s_code');
// ------...------...------...------...------...------...------...------...------...------...------

include("aptana_global/head.php");
echo <<< toTheEnd

<div style="height:74px;"></div>

A confirmation email has been send to <span style="color:#222288;">$email</span> Please 
follow the enclosed instructions to complete your account registration.<p /><br /><br />
<a href='/index.php'>return to home page</a>
<p /><br /><br />
Thank you for using $g_SITENAME.<br />
&nbsp;&nbsp;&nbsp;&nbsp;- The Crew

toTheEnd;
include("aptana_global/foot.php");

// ------...------...------...------...------...------...------...------...------...------...------

?>
