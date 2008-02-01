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


require_once("frag_global.php");

$query = "select b.name, count(*) as StringCount from translations as a inner join languages as b on b.language_id = a.language_id where a.value <> '' and a.is_active = 1 group by a.language_id order by StringCount desc";

$res = mysql_query($query);

?>
<div id="trans-progress-area">
	<h2>Translation Progress</h2>
	<dl>
	<?
		while($row = mysql_fetch_assoc($res)){
			?><dt><?=$row['name'];?></dt><?
			?><dd><?=$row['StringCount'];?></dd><?
		}
	?>
	</dl>
</div>