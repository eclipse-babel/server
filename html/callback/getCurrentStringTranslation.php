<?php
/*******************************************************************************
 * Copyright (c) 2007-2018 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
 *    Kit Lo (IBM) - [281434] Syncup overuses the "possibly incorrect" flag
*******************************************************************************/

require_once("cb_global.php");

$string_id = getHTTPParameter("string_id", "POST");
$stringTableIndex = getHTTPParameter("stringTableIndex", "POST");

if(isset($_SESSION['language']) and isset($_SESSION['version']) and isset($_SESSION['project'])){
	$language = $_SESSION['language'];
	$version = $_SESSION['version'];
	$project_id = $_SESSION['project'];
}else{
	return false;
}


$query = "select 
			strings.string_id,
			strings.non_translatable,
			strings.value as string_value,
			translations.value as translation_value,
			translations.possibly_incorrect as fuzzy,
			files.name,
			strings.name as token,
			max(translations.version)
		  from
		  	files,
		  	strings
		  	left join translations on
		  		(strings.string_id = translations.string_id 
		  		 and 
		  		 translations.is_active != 0 
		  		 and 
		  		 translations.language_id = '".addslashes($language)."')
		  where
		  	strings.is_active != 0
		  and
			  strings.string_id = '".addslashes($string_id)."'
		  and
		  	  strings.file_id = files.file_id
		  and
		  	  files.version = '".addslashes($version)."'
		  group by translations.version
		  order by translations.version desc
		  limit 1";

//print $query;

$res = mysql_query($query,$dbh);

$line = mysql_fetch_array($res, MYSQL_ASSOC);

//print_r($line);

$trans = "";

if($line['translation_value']){
	$trans = " AND translations.value = '".addslashes($line['translation_value'])."' 
				AND 
			  translations.is_active = 1
	";
}

$query = "select 
				strings.string_id, strings.value, strings.name max(translations.translation_id)
			FROM 
				files,
				strings
			left join 
				translations 
			on 
				translations.string_id = strings.string_id 
			where
				files.file_id = strings.file_id 
			AND
				files.project_id = '".addslashes($project_id)."' 
			AND 
				strings.value = '".addslashes($line['string_value'])."'

				$trans
			AND
				files.is_active = 1
				group by translations.string_id";

$query = "SELECT 
			S.*
		  FROM 
		  	strings AS S 
		  inner join files AS F on F.file_id = S.file_id 
		  inner join translations AS T on T.string_id = S.string_id 
		  where 
		  	F.project_id = '".addslashes($project_id)."' 
		  AND 
		  	F.file_id in (SELECT files.file_id FROM files where files.project_id = '".addslashes($project_id)."') 
		  AND 
		  	S.value = '".addslashes($line['string_value'])."'
		  and 
		  	T.value = '".addslashes($line['translation_value'])."' 
		  AND 
		  	T.is_active = 1
		  	";
?>

<form id='translation-form'>
	<input type="hidden" name="string_id" value="<?= $line['string_id'] ?>">
	<input type="hidden" name="stringTableIndex" value="<?= $stringTableIndex ?>">

	<div id="english-area" class="side-component">
		<h4>English String [<a id="copy-english-string-link">Copy</a>]</h4>
		<div style='overflow: auto; height: 75px;'>
			<div id="english-string"><?= nl2br(htmlspecialchars($line['string_value'])); ?></div>
		</div>
		<h4 id="translation-hints-title">Translation Hints [<a id="clear-btn" href="javascript:clearHints();">Clear</a>]</h4>
		<div id="translation-hints" style='overflow-x: hidden; overflow-y: auto; height: 75px;'>
		<b>Select some English text above to find similar translations</b><?php
		# offer up some hints is the string is not translated
		if($line['translation_value'] == "") {
			$q_th = "SELECT DISTINCT t.value
                 FROM translations as t
                 INNER JOIN strings AS s ON s.string_id = t.string_id
                 INNER JOIN files   AS f ON s.file_id = f.file_id
                 WHERE s.value like '" . addslashes(substr($line['string_value'], 0, 15)) . "%'
                 AND t.is_active
                 AND t.language_id = '" . addslashes($language) . "'
                 ORDER BY LENGTH(t.value) ASC LIMIT 10";
			$res_th = mysql_query($q_th, $dbh);
			if(mysql_affected_rows($dbh) > 0) {
				echo "<b>, or use from the following:</b><ul>";
				while($translation_hints = mysql_fetch_array($res_th, MYSQL_ASSOC)){
					echo "<li>", $translation_hints['value'], "</li>";
				}
				echo "</ul>";
			}
		}
		?>
		</div>
		
		<input id='non-translatable-checkbox' type=checkbox name="non_translatable_string" <?= $line['non_translatable'] ? 'checked' : '' ;?>>Non-Translatable		
	</div>
	<div id="translation-textarea" class="side-component">
	<?php if($line['non_translatable'] == 0) {?>
		<h4>
			Current Translation
			[<a id="reset-current-translation-link">Reset</a>]
			[<a id="clear-current-translation-link">Clear</a>]
		</h4>
		
		<textarea id="current-translation" style='display: inline; width: 95%; height: 154px;' name="translation"><?=(($line['translation_value']));?></textarea>
		<br />
		<!-- [281434] Syncup overuses the "possibly incorrect" flag
		<input id='fuzzy' type=checkbox name="fuzzy_checkbox" <?= $line['fuzzy'] ? 'checked' : '' ;?>> Translation is possibly incorrect 
		<br />
		-->
		<button id="allversions" type="submit" name="translateAction" value="All Versions">Submit</button>
		
	<?php }else{?>
		<h4>Non Translatable String</h4>
		<br /><br /><br />
		<div style='text-align:center;'>This string has been marked as <b>'non-translatable'</b>.</div>

	<?php }?>
	</div>	
	<div id="translation-history-area" class="side-component">
		<h4>History of Translations</h4>
		<div id="translation-history">
		<table>
		<?php
			$query = "select value,first_name,last_name,translations.created_on, possibly_incorrect as fuzzy from translations,users where string_id = '".addslashes($line['string_id'])."' and language_id = '".addslashes($language)."' and translations.userid = users.userid order by translations.created_on desc";
			$res_history = mysql_query($query,$dbh);
			
			if(!mysql_num_rows($res_history)){
				print "No history.";
			}else{		
				while($line = mysql_fetch_array($res_history, MYSQL_ASSOC)){
					$fuzzy = "";
					if($line['fuzzy'] == 1) {
						$fuzzy = "<img src='images/fuzzy.png' />";
					}
					print "<tr>";
					print "<td width='40%'>";
					// [281434] Syncup overuses the "possibly incorrect" flag
					// print "<div>$fuzzy".nl2br(htmlspecialchars($line['value']))."</div>";
					print "<div>".nl2br(htmlspecialchars($line['value']))."</div>";
					print "</td>";
					print "<td width='20%'>";
					print $line['first_name']." ".$line['last_name'];
					print "</td>";
					print "<td width='40%'>";
					print $line['created_on'];
					print "</td>";
					print "</tr>";
				}
			}
		?>
		</table>
		</div>
	</div>
</form>