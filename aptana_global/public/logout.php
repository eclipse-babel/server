<?
// ------...------...------...------...------...------...------...------...------...------...------
require_once("aptana_global/aptana.inc.php");
unset($_SESSION['s_userAcct']);
unset($_SESSION['s_userName']);
unset($_SESSION['s_userType']);
setcookie("cAPTANAX","",-36000,"/");
$lastURL = GetSessionVar('s_pageLast');
exitTo("/" . ($lastURL?$lastURL:"index.php"));
// ------...------...------...------...------...------...------...------...------...------...------
?>
