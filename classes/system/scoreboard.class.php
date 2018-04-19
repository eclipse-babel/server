<?php
/*******************************************************************************
 * Copyright (c) 2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Foundation - initial API and implementation
 *    Satoru Yoshida - [470121] scoreboard could be removed if no needed
*******************************************************************************/

class Scoreboard {

	public function refresh($forceRefresh) {
		global $dbh;
		$sql = "SELECT quantity FROM scoreboard " .
			"WHERE itemid = 'LASGEN' " .
			"AND quantity < (SELECT MAX(translation_id) as t FROM translations)";

		$result = mysqli_query($dbh, $sql);
		if(($result && mysql_num_rows($result) > 0) || $forceRefresh) {

			# "lock" the scoreboard so that 2 clients don't update it simultaneously
			mysqli_query($dbh, "UPDATE scoreboard SET quantity = 9999999999 WHERE itemid = 'LASGEN'");

			# rebuilding the scoreboard takes time ... dump stuff to tmp
			mysqli_query($dbh, "CREATE TEMPORARY TABLE _tmp_scoreboard LIKE scoreboard");
			$sql = "INSERT INTO _tmp_scoreboard SELECT NULL, 'LANGPR', IF(ISNULL(b.locale),b.name,CONCAT(b.name, CONCAT(' (', CONCAT(b.locale, ')')))), count(a.string_id) as cnt from translations as a inner join languages as b on b.language_id = a.language_id where a.created_on > (NOW() - INTERVAL 1 YEAR) and a.value <> '' and a.is_active = 1 group by a.language_id order by cnt desc limit 20";
			mysqli_query($dbh, $sql);
			$sql = "INSERT INTO _tmp_scoreboard SELECT NULL, 'TOPTR', CONCAT(first_name, IF(ISNULL(last_name),'',CONCAT(' ', last_name))), count(t.string_id) as cnt from translations as t inner join users as u on u.userid = t.userid where t.created_on > (NOW() - INTERVAL 1 YEAR) and t.value <> '' and t.is_active=1 group by first_name, last_name order by cnt desc limit 20";
			mysqli_query($dbh, $sql);

			$sql = "INSERT INTO _tmp_scoreboard SELECT NULL, 'LASGEN', 'Scoreboard Last Generated', MAX(translation_id) FROM translations";
			mysqli_query($dbh, $sql);

			$sql = "INSERT INTO _tmp_scoreboard SELECT NULL, 'LGNOW', 'Scoreboard Last Generated Date/Time', NOW()";
			mysqli_query($dbh, $sql);
			
			mysqli_query($dbh, "LOCK TABLES scoreboard WRITE");
			mysqli_query($dbh, "DELETE FROM scoreboard");
			mysqli_query($dbh, "INSERT INTO scoreboard SELECT * FROM _tmp_scoreboard");
			mysqli_query($dbh, "UNLOCK TABLES");
			mysqli_query($dbh, "DROP TABLE _tmp_scoreboard");
		}
	}
}
?>