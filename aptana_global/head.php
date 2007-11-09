<?
$msg = "&nbsp;";
if (isset($s_userName))
  $msg = "Welcome $s_userName &nbsp;&nbsp;<a href='/logout.php'>Logout</a>&nbsp;";

echo <<< toTheEnd
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>aptana.global</title>
  <link href="/aptana.css" rel="stylesheet" type="text/css" >
  <script type="text/javascript" src="/aptana.js"></script> 
</head>

<body>


<table cellspacing=0 cellpadding=0 widt=100%>
<tr>
<td>
<a style='border=1;' href="/"><img src="background-header.gif" width="522" height="49" border=0/></a>
</td>
<td class=userPanel>
$msg
</td>
</tr>
</table>



</div>
<div style='margin-left:25px;margin-right:25px;'>
toTheEnd;
?>
