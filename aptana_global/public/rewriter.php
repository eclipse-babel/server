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
// ------...------...------...------...------...------...------...------...------...------...------

if (!empty($_SERVER['REQUEST_URI'])) {
  $uri = $_SERVER['REQUEST_URI'];
  if ($pos = strpos($uri,"export_data"))  {
    $lang = substr($uri,$pos+12,2);
    header("Location: /export.php?lang=$lang");
    exit;
  }
  if ($pos = strpos($uri,"entry/add"))  {
    header("Location: /import.php");
    exit;
  }
  if ($pos = strpos($uri,"entry/edit"))  {
    header("Location: /edit.php");
    exit;
  }
}

  header("Location: /index.php");
  exit;



echo "<br>file not found";

// ------...------...------...------...------...------...------...------...------...------...------
?>