#!/usr/local/bin/php
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
$hashes = hash_algos();

foreach ($hashes as $hash) {
  print "$hash\n";
  $val = hash("$hash","mustang"); // . "FvPEUG0X");
  //if (strlen($val) == 64)
    echo "$val\n\n";
}  
  
  
  /*
  
  
INSERT INTO users SET 
username='eddieroh',
first_name='eddie',
last_name='rohwedder',
email='eddie_roh@yahoo.com',
primary_language_id='494',
hours_per_week='17',
password_salt='',
password_hash='',
updated_on='',
updated_at='',
created_on='',
created_at='',
type='0',
status='0',
code='933b8a5ac8df8bd59918f9dd3ca35959',created=NOW()  
  
  */

// ------...------...------...------...------...------...------...------...------...------...------
?>