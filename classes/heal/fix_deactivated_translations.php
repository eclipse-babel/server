<?php
/*******************************************************************************
 * Copyright (c) 2007-2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/
require("global.php");
InitPage("");


$query = "select * from translations";
$res = mysql_query($query);
print "<pre>";

while($row = mysql_fetch_assoc($res)){
	$found[$row['string_id']][$row['language_id']][] = $row;
}










foreach($found as $string_id => $v){
	foreach($v as $language_id => $langs){
		$found_active = 0;
		foreach($langs as $foo => $trans){
			if($trans['is_active'] == 1){
				$found_active++;		
			}
		}
		if(	$found_active == 0){
//			print "0 - $string_id - $language_id<br>\n";
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysql_fetch_assoc(mysql_query($query));
			$max = $max['max'];
			$query = "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";			
			print $query."\n";
			mysql_query($query);			
			print mysql_error();
			
		}elseif($found_active > 1){
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysql_fetch_assoc(mysql_query($query));
			$max = $max['max'];
			$query = "update translations set is_active = 0 where string_id = $string_id and language_id = $language_id and version != $max";
			print $query."\n";
			mysql_query($query);			
			print mysql_error();
			
			$query =  "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";
			print $query."\n";
			mysql_query($query);
			print mysql_error();
			
		}
	}
}

?>