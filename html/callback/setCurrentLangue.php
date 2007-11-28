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

$language_post = $App->getHTTPParameter("lang", "POST");

if($language_post){
	$lang = explode("=",$language_post);
	if($lang[1] and is_numeric($lang[1])){
		# TODO check database before setting to make sure input is valid choice
		$_SESSION['language'] = $lang[1];
		print getLanguagebyID($lang[1]);
	}
}

?>