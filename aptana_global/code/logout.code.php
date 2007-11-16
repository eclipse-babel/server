<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 * 	  Eclipse Foundation - Initial API and implementation
*******************************************************************************/
require_once(BABEL_BASE_DIR."aptana.inc.php");

extract(LoadVars());

function LoadVars() {
  
  InitPage("login");
  logoutUser();
}

function logoutUser() {
  $session = new sessions_iu(0);
  $session->destroy();
  setcookie(COOKIE_REMEMBER, "", -36000, "/");
}



?>
