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

*******************************************************************************/
$pageTitle = "Welcome to Babel";
$pageKeywords = "";

require_once("../aptana.inc.php");
require_once(BABEL_BASE_DIR . 'head.php');

echo <<< toTheEnd
<div id="maincontent">
<div id="midcolumn">

<h1>$pageTitle</h1>
<p>Welcome to Babel. Babel is designed to ease the process
of translating Eclipse into another language.</p>
<p>Ready to begin translating? <a href='index.php'>Let's go!</a></p>
</div>
</div>
</div>

toTheEnd;
require_once('../foot.php');    
?>