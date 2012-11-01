<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
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

$query = "SELECT value, quantity FROM scoreboard WHERE itemid = 'LANGPR' ORDER BY quantity DESC";

$res = mysql_query($query);

?>
<div id="trans-progress-area">
	<h2>Translation Progress</h2>
	<dl>
	<?php
		while($row = mysql_fetch_assoc($res)){
			echo "<dt>" . $row['value'] . "</dt>";
			echo "<dd>" . $row['quantity'] . "</dd>";
		}
	?>
	</dl>
</div>