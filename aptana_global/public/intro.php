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

require_once('../head.php');
echo <<< toTheEnd
<p />
<center>
<p /><br /><br /><br /><br /><br />
<div style='width:600px;font-family:Arial;font-size:16px;font-weight:bold;'>
Welcome to aptana.global. Aptana.global is designed to ease the process
of translating Aptana into another language.
<p />
Ready to begin translating? Let's go!

<p /><br /><br />
<a href='/index.php'><img src='/get-started.gif' border=0></a>
</div>
</center>

toTheEnd;
require_once('../foot.php');    
?>