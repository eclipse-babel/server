#!/usr/bin/php
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
// if not logged in redirect to login page
// otherwise show choices
require_once("aptana_global/aptana.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("get_entry");

$langID  = $_GET['lang'];
$projID  = $_GET['proj'];
$entryN  = $_GET['entry'];
$nextId  = 0;
$nextIdx = 1;
$entr  = new entries_iu(0);


$cmd = " select * from entries where language_id=490 and name='PreferenceInitializer_IntitialFileContents'";

$qry = mysql_query($cmd);
$row = mysql_fetch_object($qry);
echo $row->value;
// ------...------...------...------...------...------...------...------...------...------...------

?>