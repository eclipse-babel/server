<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

if(defined(BABEL_BASE_DIR)){
	require_once(BABEL_BASE_DIR."html/global.php");
}else{
	define('BABEL_BASE_DIR', "../../");
	require_once("../global.php");
}

InitPage("login");

$query = "select * from languages where is_active = 1";

$res = mysql_query($query,$dbh);

while($line = mysql_fetch_array($res, MYSQL_ASSOC)){
	$return .= "<li><a href='?language_id=".$line['language_id']."'>".$line['iso_code']. " - ". $line['name']. "</a>";
}

?>

<ul id='language-choices'>
	Please select a langue to translate:<br>
	<?=$return;?>
</ul>