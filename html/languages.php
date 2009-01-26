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
require(dirname(__FILE__) . "/global.php");
require_once(dirname(__FILE__) . "/../classes/system/language.class.php");
InitPage("");

$pageTitle 		= "Babel Project - Eclipse translation";
$pageKeywords 	= "translation,language,nlpack,pack,eclipse,babel,english,french,german,chinese,japanese,spanish,arabic,hebrew,hungarian,polish,italian,russian,dutch,finnish,greek,norwegian,sweedish,turkish";

include("head.php");

?>
<h1 id="page-message">Supported languages</h1>


<h2>Babel supports those languages.</h2>
</br>


<ul>
</tr>
<?php
$languages = Language::all();
foreach ($languages as $lang) {
if ($lang->iso == "en_AA") {
    continue;
}
$row = <<<ROW
<li>$lang->name ($lang->iso)</li>
ROW;
echo $row;
}
?>
</ul>

<p>Please <a href="https://bugs.eclipse.org/bugs/enter_bug.cgi?product=Babel&component=Server&bug_file_loc=<?= $_SERVER['SCRIPT_NAME']; ?>">
contact us</a> if the language you need is missing.</p>

<?php
	include("foot.php");
?>