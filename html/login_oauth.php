<?php
/*******************************************************************************
 * Copyright (c) 2019 Paul Pazderski and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Pazderski - initial API and implementation
 *******************************************************************************/
include ("global.php");
InitPage("");

require_once (dirname(__FILE__) . "/../classes/system/user.class.php");
require_once (dirname(__FILE__) . "/../classes/system/session.class.php");

$pageTitle = "Contribute Translations to Babel";
$pageKeywords = "translation,language,nlpack,pack,eclipse,babel";

$eclipse_oauth_api_url = "https://accounts.eclipse.org/oauth2/authorize";
$eclipse_oauth_token_url = "https://accounts.eclipse.org/oauth2/token";

$OAUTH = getHTTPParameter("oauth", "POST");
$CODE = getHTTPParameter("code", "GET");
$STATE = getHTTPParameter("state", "GET");
$SUBMIT = getHTTPParameter("submit", "GET");
$ERROR = getHTTPParameter("error", "GET");
if (! empty($OAUTH)) {
	global $addon;
	$oauth_params = $addon->callHook("oauth_params");
	$state = createNonce();
	SetSessionVar("oauth_state", $state);
	$params = array(
		'response_type' => 'code',
		'client_id' => $oauth_params["client_id"],
		'redirect_uri' => $oauth_params["client_callback"],
		'scope' => 'openid profile',
		'state' => $state
	);
	exitTo($eclipse_oauth_api_url . "?" . http_build_query($params));
} else if ($ERROR == "consent_required") {
	// do nothing; user aborted login
} else if (! empty($CODE)) {
	// check state
	$saved_state = GetSessionVar("oauth_state");
	if ($STATE !== $saved_state) {
		$GLOBALS['g_ERRSTRS'][0] = "Authentication failed.";
		$GLOBALS['g_ERRSTRS'][1] = "Request was not started from login page.";
	} else {
		global $addon;
		$oauth_params = $addon->callHook("oauth_params");
		$params = array(
			'grant_type' => 'authorization_code',
			'client_id' => $oauth_params["client_id"],
			'client_secret' => $oauth_params["client_secret"],
			'code' => $CODE,
			'redirect_uri' => $oauth_params["client_callback"],
			'state' => $STATE
		);
		$options = array(
			'http' => array(
				'header' => array(
					"Content-type: application/json"
				),
				'method' => 'POST',
				'content' => json_encode($params)
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($eclipse_oauth_token_url, false, $context);
		if ($result === false) {
			$GLOBALS['g_ERRSTRS'][0] = "Login failed.";
			$GLOBALS['g_ERRSTRS'][1] = error_get_last()["message"];
		} else {
			$result = json_decode($result, true, 10);
			if ($result === null) {
				$GLOBALS['g_ERRSTRS'][0] = "Login failed.";
			} else {
				$User = new User();
				$uid = $User->updateUser($result["access_token"]);
				if ($uid <= 0) {
					$GLOBALS['g_ERRSTRS'][0] = "Login failed.";
				} else {
					$User->loadFromID($uid);

					$Session = new Session();
					$Session->create($User->userid, true);
					SetSessionVar('User', $User);
					if (isset($_SESSION['s_pageLast']) && ! empty($_SESSION['s_pageLast'])) {
						exitTo($_SESSION['s_pageLast']);
					}
					exitTo("translate.php");
				}
			}
		}
	}
} else if ($SUBMIT == "Logout") {
	$Session = new Session();
	$Session->destroy();
	// we're logging out, therefore we don't have a user anymore
	$User = null;
	$GLOBALS['g_ERRSTRS'][0] = "You have successfully logged out. You can login again using the button below.";
}

global $addon;
$addon->callHook("head");

include ("content/en_login_oauth.php");

global $addon;
$addon->callHook("footer");

// Function to create a simple unguessable random string.
function createNonce() {
	return md5(openssl_random_pseudo_bytes(20));
}
?>