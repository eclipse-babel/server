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


$string_id = $App->getHTTPParameter("string_id", "POST");

$query = "select 
			strings.string_id,
			strings.value as string_value,
			translations.value as translation_value,
			max(translations.version)
		  from
		  	strings
		  	left join translations on
		  		(strings.string_id = translations.string_id and translations.is_active != 0)
		  where
		  	strings.is_active != 0
		  and
			  strings.string_id = '".addslashes($string_id)."'
		  group by translations.version
		  order by translations.version desc
		  limit 1
			";

$res = mysql_query($query,$dbh);

$line = mysql_fetch_array($res, MYSQL_ASSOC);

?>
<form id='translation-form'>
	<input type="hidden" name="string_id" value="<?=$line['string_id'];?>">

	<div id="english-area" class="side-component">
		<h4>English String</h4>
		<div id="english-current-area">
			<?= nl2br($line['string_value']);?>
		</div>
	</div>
	
	<div id="translation-textarea" class="side-component">
		<h4>Current Translation</h4>
		<textarea style='display: inline; width: 390px; height: 150px;' name="translation"><?= nl2br($line['translation_value']);?></textarea>
		<br>
		<input type="submit" value="translate">
	</div>
	<div id="translation-history" class="side-component">
		<h4>History of Translations</h4>
		<div id="translation-history">Coming soon!</div>
	</div>
	
</form>
