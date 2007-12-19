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

require_once("cb_global.php");

print_r($_POST);

$string_id = $App->getHTTPParameter("string_id", "POST");
$translation = $App->getHTTPParameter("translation", "POST");
$language_id = $_SESSION["language"];
$user_id =	$User->userid;

$query = "insert into 
			translations
		  set
		  	string_id = '".addslashes($string_id)."',
		  	language_id = '".addslashes($language_id)."',
		  	value = '".addslashes($translation)."',
		  	userid = '".addslashes($user_id)."',
		  	created_on = NOW()
		  	";
//print $query;

$res = mysql_query($query,$dbh);

?>