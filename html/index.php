<?php

include("global.php");
InitPage("login");

include("head.php");
echo $User->userid;
echo $User->first_name;

include("foot.php");

?>