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

function emailUser($userEmail,$template,$extra=NULL) {
  $siteName = (empty($GLOBALS['g_SITENAME'])?"Aptana"    :$GLOBALS['g_SITENAME']);
  $siteURL  = (empty($GLOBALS['g_SITEURL']) ?"aptana.com":$GLOBALS['g_SITEURL']);
  // Get the all the templates
  //   email.body is the email header + MIME template
  //   where the HTM and TEXT version is inserted
  $body = file_get_contents(BABEL_BASE_DIR."emails/email.body",true);
  $html = file_get_contents("aptana_global/emails/$template.html",true);
  $html = str_replace("\${g_SITENAME}",$siteName,$html);
  $html = str_replace("\${g_SITEURL}" ,$siteURL,$html);
  $text = file_get_contents("aptana_global/emails/$template.text",true);
  $text = str_replace("\${g_SITENAME}",$siteName,$text);
  $text = str_replace("\${g_SITEURL}" ,$siteURL,$text);

  // build the reply-to field - use full name where possible
  $replyTo = "";
  if (eregi("<reply_to>.*</reply_to>",$html,$reg))
    $replyTo = substr($reg[0],10,strlen($reg[0])-21);

  // pull the subject/from name/address from the html template
  $subject = "<subject>";
  if (eregi("<subject>.*</subject>",$html,$reg))
    $subject = substr($reg[0],9,strlen($reg[0])-19);

  $fromName = $siteName;
  if (eregi("<from_name>.*</from_name>",$html,$reg))
    $fromName = substr($reg[0],11,strlen($reg[0])-23);

  $fromAddr = "dontreply@" . $siteURL;
  if (eregi("<from_address>.*</from_address>",$html,$reg))
    $fromAddr = substr($reg[0],14,strlen($reg[0])-29);

  // skip the header vars.
  if (eregi("<html>.*",$html,$reg))
    $html = $reg[0];
  $html = str_replace("=","=3D",$html);

  // Do the header/MIME replacements
  $msg = str_replace("\${FROM_NAME}",$fromName,$body);
  $msg = str_replace("\${FROM_ADDRESS}",$fromAddr,$msg);
  $msg = str_replace("\${SUBJECT}",$subject,$msg);
  $msg = str_replace("\${REPLY_TO}",$replyTo,$msg);

  $msg = str_replace("\${EMAIL_TEXT}",$text,$msg);
  $msg = str_replace("\${EMAIL_HTML}",$html,$msg);

  // Replace content editors vars
  $msg = str_replace("\${email}",$userEmail,$msg);

  // Replace passed in dev vars
  if (isset($extra)) {
    foreach ($extra as $key => $val)
      $msg = str_replace("\${{$key}}",$val,$msg);
  }

  $pos = strpos($msg,"\n\n")+2;
  $hdr = substr($msg,0,$pos);
  $msg = substr($msg,$pos);

  mail($userEmail,$subject,$msg,$hdr);
  debugLog("emailed: $userEmail [$template]");

}

// ------...------...------...------...------...------...------...------...------...------...------

?>