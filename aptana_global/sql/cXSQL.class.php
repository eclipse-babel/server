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

class cXSQL {
  public $sql_Tab = "";
  public $sql_Qry = 0;
  public $sql_Cnt = 0;
  public $sql_Nbr = 0;
  public $sql_Dif = 0;
  public $sql_Dlt = 0;
  public $pag_Srt = "";
  public $pag_Whr = "";
  public $pag_Asc = 1;
  public $pag_Cnt = 0;
  public $pag_Rec = 0;
  public $pag_Nbr = 0;
  public $pag_Amt = 0;
  public $pag_Def = 0;

// ------...------...------...------...------...------...------...------...------...------...------

final function cXSQL($arg) {
  $this->sql_Tab = substr(get_class($this),0,strlen(get_class($this))-3);

  if (!$arg)
    return;

  if (func_num_args() == 1)
    $this->sqlLoad($arg);
  else if (func_num_args() == 2) {
    $arg1 = func_get_arg(0);
    $arg2 = func_get_arg(1);
    $this->sqlLoad($arg1,$arg2);
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlCmd($cmd) {
  $cmd = str_replace("{SELF}",$this->sql_Tab,$cmd);
  $cmd = str_replace("{SELFID}",$this->_id,$cmd);
  $qry = @mysql_query($cmd);
  if (!$qry)
    dump(LOG2ERROR,mysql_error() . "\n\t$cmd\n");
  return $qry;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlQry($cmd) {
  $this->sql_Qry = $this->sqlCmd($cmd);
  $this->sql_Cnt = @mysql_num_rows($this->sql_Qry);
  return $this->sql_Qry;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlRec($cmd) {
  if ($qry = $this->sqlCmd($cmd))
    if ($rec = mysql_fetch_object($qry)) {
      return $rec;
	  }
  return 0;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlLoad($arg) {

  if (is_array($arg)) {
    $rec = $arg;
  }
  else {
    if (is_numeric($arg))
      $arg = "SELECT * FROM {SELF} WHERE id='$arg'";
    else if (func_num_args() == 1)
      $arg = "SELECT * FROM {SELF} WHERE $arg";
    else if (func_num_args() == 2) {
      $arg1 = func_get_arg(0);
      $arg2 = func_get_arg(1);
      $arg = "SELECT * FROM {SELF} WHERE $arg1='$arg2'";
    }
    if (!($qry = $this->sqlQry($arg)))
      return false;

    if (!($rec = mysql_fetch_assoc($qry)))
      return false;
  }

  if ($vars = get_object_vars($this)) {
    foreach ($rec as $key => $val) {
      $val = htmlspecialchars($val);
      $key = "_" . $key;
      eval("\$this->$key = \$val;");
    }
  }    
  return true;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlList($cmd) {
  $this->sql_Nbr = 0;
  $this->sqlQry($cmd);
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlNext() {
  if ($this->sql_Qry && ($rec = mysql_fetch_assoc($this->sql_Qry))) {
    $this->sql_Nbr++;
    return $this->sqlLoad($rec);
  }
  return false;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlGetCnt($cmd) {
  if ($qry = $this->sqlCmd($cmd))
    if ($rec = mysql_fetch_array($qry)) {
      return $rec[0];
    }
  return 0;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlUpdate($sets) {
  if (!$this->_id) {
    $this->sqlCmd("INSERT INTO {SELF} SET $sets");
    $this->_id = mysql_insert_id();
    $this->sqlLoad($this->_id);
  }
  else if (isset($sets) && strlen($sets)) {
    $this->sqlCmd("UPDATE {SELF} SET $sets WHERE id={SELFID}");
    $this->sqlLoad($this->_id);
  }
  return $this->_id;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlTouch($fld) {
  if ($this->_id) {
    $this->sqlCmd("UPDATE {SELF} SET $fld=NOW() WHERE id='$this->_id'");
    $this->sqlLoad($this->_id);
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlGetPassword($str) {
  if ($rec = $this->sqlRec("SELECT PASSWORD('$str') AS password"))
    return $rec->password;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlReplace() {
  $sets = "";
  foreach (get_object_vars($this) as $key => $val)  {
    if (($key{0} != "_") || ($key == '_created'))
      continue;
    if (($key = substr($key,1)) == "id") 
      continue;
    $sets = $this->addSET($sets,$key,$val);
  }
  if ($this->sqlCmd("REPLACE INTO {SELF} SET $sets")) {
    debugLog("REPLACE INTO {SELF} SET $sets");
    $this->_id = mysql_insert_id();
    $this->sqlLoad($this->_id);
    return true;
  }
  return false;
}

// ------...------...------...------...------...------...------...------...------...------...------

function formDifArray($_POST) {
  $ary = array();

  foreach (get_object_vars($this) as $key => $val)  {
    $postKey = $this->sql_Tab . $key;

    if ($key{0} != "_")
      continue;
    if ($key == "_id") 
      continue;

    if (isset($_POST[$postKey])) {
      //debugLog("+++++++++  $postKey");
      $postVal = trim($_POST[$postKey]);
    }
    else if (isset($_POST["un_$postKey"])) {
      //debugLog("=========  $postKey");
      $postVal = trim($_POST["un_$postKey"]);
    }
    else  {
      //debugLog("---------  $postKey");
      continue;
    }

    if ($postVal != $val)
      $ary[substr($key,1)] = $postVal;
  }
  return $ary;
}

// ------...------...------...------...------...------...------...------...------...------...------

function addSET($sets,$key,$val) {
  if (!isset($sets))
    $sets = "";
  $val = esc_str($val);
  if ($val == "NOW()")
    $sets .= ($sets?",":"") . "$key=$val";
  else
    $sets .= ($sets?",":"") . "$key='$val'";
  return $sets;
}

// ------...------...------...------...------...------...------...------...------...------...------

function selfPost() {
  $sets = "";
  foreach (get_object_vars($this) as $key => $val)  {
    if ($key{0} != "_")
      continue;
    if (($key = substr($key,1)) == "id") 
      continue;
    $sets = $this->addSET($sets,$key,$val);
  }
  return $this->sqlUpdate($sets);
}

// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

function formBuildData($post) {
  $ary = array();

  foreach (get_object_vars($this) as $key => $val)  {
    // only add db fields - which start with and '_'
    if ($key{0} != "_") 
      continue;
    $fld = "$this->sql_Tab$key";

    if (!empty($post[$fld]))
      $val = $post[$fld];

    $ary["{$fld}_name"] = $fld;
    $ary[$fld] = str_replace("\"","&quot;",($val?$val:""));

    if (is_numeric($val)) {
      $nbr = ($val?$val:"0");
      $ary[$fld]               = $nbr;
      $ary["{$fld}_check"]     = ($val?"checked":"");
      $ary["{$fld}_radio$nbr"] = "checked";
      $ary["{$fld}_combo$nbr"] = "selected";
      $ary["{$fld}_uncheck"]   = "<input type='hidden' name='un_$fld' value='0'>";
    }
  }
  return $ary;
}

// ------...------...------...------...------...------...------...------...------...------...------

function formHandleCmd($cmd,$lastPage,$idName) {
  $retVal = true;

  if ($cmd == 'cancel')
    exitTo($lastPage);

  if ($cmd == 'apply')
    return $this->formSavePost();

  if (($cmd == 'save') && ($retVal = $this->formSavePost()))
    exitTo($lastPage);
  
  if (($cmd == 'new') && ($retVal = $this->formSavePost())) {
    SetSessionVar($idName,0);
    exitTo($lastPage);
  }

  if (($cmd == 'next') && ($retVal = $this->formSavePost())) {
    SetSessionVar($idName,getCookie('id'));
    exitTo($GLOBALS['g_PHPSELF']);
  }

  if (($cmd == 'prev') && ($retVal = $this->formSavePost())) {
    SetSessionVar($idName,getCookie('id'));
    exitTo($GLOBALS['g_PHPSELF']);
  }

  if ($cmd == 'delete') {
  }

  return $retVal;
}

// ------...------...------...------...------...------...------...------...------...------...------

function formSavePost() {
  if ($this->formValidate()) {
    $sets = "";
    $this->sql_Dif = $this->formDifArray($_POST);
    foreach ($this->sql_Dif as $key => $val)
      $sets = $this->addSET($sets,$key,$val);
    return $this->sqlUpdate($sets);
  }
  return false;
}

// ------...------...------...------...------...------...------...------...------...------...------


function formGatherData($_POST) {
  $this->sql_Dif = $this->formDifArray($_POST);
  foreach ($this->sql_Dif as $key => $val)
    eval("\$this->_$key = \$val;");
  return $this->_id;
}

// ------...------...------...------...------...------...------...------...------...------...------

function formValidate() {
  return true;
}

// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

function paginateHTML($pageAmt) {
  $link = "";

  // It all fits on one page so no pagination needed;
  if ($this->pag_Rec<=$pageAmt)
    return $link;

  // If view all (or 1000 max) offer to return to page mode
  if (($pageAmt < $this->pag_Amt) && ($this->pag_Rec<1000)) {
    $link .= "<table class=page cellspacing=0 cellpadding=0><tr>\n";
    $link .= "<td class='pagemenu mgryro' onmouseover='menuro(this);' onmouseout='menuro(this);' ";
    $link .= "onclick=\"return rwc('c_pageAmt','$pageAmt');\">View&nbsp;Pages";
    $link .= "</td></tr></table>\n";
    return $link;
  }

  $link = "<div class=pages>\n";
  $tmpl = "<a class='pagemenu mgryro%s' onclick=\"return rwc('c_pageNbr','%d');\">%s</a>\n";
  $blnk = "<a class='pagemenu mgryro%s'>%s</a>\n";

  $cnt = $this->pag_Cnt;
  $beg = max($this->pag_Nbr-5,0);
  $end = min($this->pag_Nbr+5,$cnt-3);
  
  
  if ($end-$beg < 10)
    $beg = max(0,($beg-(10-($end-$beg)))); 
  if ($end-$beg < 10)
    $end = min($cnt,($beg+13)); 


  $link .= sprintf($tmpl,"",0,"Prev");
  for ($i=0;($i<3)&&($i<$cnt);$i++)
    if ($i == $this->pag_Nbr)
      $link .= sprintf($blnk,"sel",($i+1));
    else
      $link .= sprintf($tmpl,"",$i,($i+1) ); 
    
  if ($beg>3)      
    $link .= "<span>...</span>\n";

  for ($i=max($i,$beg);$i<min($end,$cnt);$i++)
    if ($i == $this->pag_Nbr)
      $link .= sprintf($blnk,"sel",($i+1));
    else
      $link .= sprintf($tmpl,"",$i,($i+1) ); 
    
  if ($end<$cnt-3)      
    $link .= "<span>...</span>\n";

  for ($i=$cnt-3;$i<$cnt;$i++)
    if ($i == $this->pag_Nbr)
      $link .= sprintf($blnk,"sel",($i+1));
    else
      $link .= sprintf($tmpl,"",$i,($i+1) ); 
  $link .= sprintf($tmpl,"",($cnt-1),"Next");  
  $link .= "</div>\n";
    
/*    
//  $link = "<table class=page cellspacing=0 cellpadding=0><tr>\n";
//  $tmpl = "<td class='mntd menu mgryro%s' onmouseover='menuro(this);' onmouseout='menuro(this);' onclick=\"return rwc('c_pageNbr','%d');\">%s</td>\n";
//  $blnk = "<td  class='mntd menu mgryro%s'>%s</td>\n";

  if ($this->pag_Nbr>8)      
    $link .= "<td class='mntd menu'>...</td>\n";

  for ($i=max(0,($this->pag_Nbr-5));($i<$this->pag_Nbr+5)&&($i<$this->pag_Cnt);$i++)
    if ($i == $this->pag_Nbr)
      $link .= sprintf($blnk,"sel",($i+1));
    else
      $link .= sprintf($tmpl,"",$i,($i+1));

  if (($this->pag_Nbr) < ($this->pag_Cnt-8))      
    $link .= "<td class='mntd menu'>...</td>\n";
  for ($i=max(($this->pag_Nbr+5),($this->pag_Cnt-3));$i<$this->pag_Cnt;$i++)
    $link .= sprintf($tmpl,"",$i,($i+1));
*/
//  for ($i=0;$i<$this->pag_Cnt;$i++) {
//    $link .= sprintf($tmpl,"",$i,($i+1));
//  }
  
/*
  $x = max(0,($this->pag_Nbr-2));
  $x = min($x,max(0,($this->pag_Cnt-5)));
  $link .= sprintf($tmpl,"",0,"<");
  for ($i=$x;($i<($x+5))&&($i<$this->pag_Cnt);$i++) {
    if ($i == $this->pag_Nbr)
      $link .= sprintf($blnk,"sel",($i+1));
    else
      $link .= sprintf($tmpl,"",$i,($i+1));
  }
  $link .= sprintf($tmpl,"",($this->pag_Cnt-1),">");
*/

  
//  $link .= "<td class='mntd menu mgryro' onmouseover='menuro(this);' onmouseout='menuro(this);' ";
//  $link .= "onclick=\"return rwc('c_pageAmt','0');\">View&nbsp;All</td>\n";
//  $link .= "</tr></table>\n";
  return $link;
}

// ------...------...------...------...------...------...------...------...------...------...------

function paginateLoad($defSort,$where) {
  // Grab View All/ View Paginated cookie
  if (isset($_COOKIE['c_pageAmt'])) {
    $this->pag_Amt = getCookie('c_pageAmt');
    unset($_COOKIE['c_pageNbr']);
    $this->pag_Nbr = 0;
  }
  if ($this->pag_Amt == 0)
    $this->pag_Amt = 1000;  

  // Grab Sort by cookie
  if (isset($_COOKIE['c_sortOn'])) {
    $srt = getCookie('c_sortOn');
    if ($this->pag_Srt == $srt) {
      $this->pag_Asc = !$this->pag_Asc;
    }
    else 
       $this->pag_Asc = true;
    $this->pag_Srt = $srt;
    $this->pag_Nbr = 0;
  }
  else if (empty($this->pag_Srt))
    $this->pag_Srt = $defSort;

  if (empty($this->pag_Whr))
    $this->pag_Whr = $where;   
  else if ($this->pag_Whr != $where)
    $this->pag_Whr = $where;   
  
  // Grab Page number cookie
  if (isset($_COOKIE['c_pageNbr']))
    $this->pag_Nbr = getCookie('c_pageNbr');

  // Refresh Page count
  $cmd = "SELECT COUNT(id) FROM {SELF}" . (($this->pag_Whr)?" WHERE $this->pag_Whr":"");
  $this->pag_Rec = $this->sqlGetCnt($cmd);
  $this->pag_Cnt = ceil($this->pag_Rec/max(1,$this->pag_Amt));
}

// ------...------...------...------...------...------...------...------...------...------...------

function paginate($pageAmt,$defSort,$where) {
  // Should handle all prev|next page sort of table list views
  //   $pageAmt - number of rows per pages
  //   $defSort - default sort field
  //   $where   - where clause

  $this->pag_Amt = $pageAmt;  
  if ($page = GetSessionVar("s_pageX$this->sql_Tab"))
    list($this->pag_Srt,$this->pag_Asc,$this->pag_Nbr,$this->pag_Amt,$this->pag_Whr) = split(":",$page);
  $this->paginateLoad($defSort,$where);
  SetSessionVar("s_pageX$this->sql_Tab","$this->pag_Srt:" . (empty($this->pag_Asc)?"0":"1") . ":$this->pag_Nbr:$this->pag_Amt:$this->pag_Whr");

  $retVal = array();
 
  if ($this->pag_Srt) {
    // So we don't get 'not defined' errors
    foreach (get_object_vars($this) as $key => $val)  {
      if ($key{0} == "_")
        $retVal["sort$key"] = "";
    }
    // Set the class name (sortUp/sortDn) for the current $sort_field    
    if (($pos = strpos(($key = $this->pag_Srt),",")) > 0)
      $key = substr($key,0,$pos);
    $retVal["sort_$key"] = "sort" . ($this->pag_Asc?"Up":"Dn");
  }
  $retVal["sort_link"] = $this->paginateHTML($pageAmt);
  return $retVal;
}


// ------...------...------...------...------...------...------...------...------...------...------
/*
function prevNext($id,$pageAmt,$defSort,$where) {
  // Should handle all prev|next page sort of table list views
  //   $pageAmt - number of rows per pages
  //   $defSort - default sort field
  //   $where   - field for where clause

  if ($page = GetSessionVar("s_pageX$this->sql_Tab"))
    list($this->pag_Srt,$this->pag_Asc,$this->pag_Nbr,$this->pag_Amt) = split(":",$page);
  $this->paginateLoad($defSort,$where);
  SetSessionVar("s_pageX$this->sql_Tab","$this->pag_Srt:" . (empty($this->pag_Asc)?"0":"1") . ":$this->pag_Nbr:$this->pag_Amt");

  $retVal = array();
 
  if ($this->pag_Srt) {
    // So we don't get 'not defined' errors
    foreach (get_object_vars($this) as $key => $val)  {
      if ($key{0} == "_")
        $retVal["sort$key"] = "";
    }
    // Set the class name (sortUp/sortDn) for the current $sort_field    
    if (($pos = strpos(($key = $this->pag_Srt),",")) > 0)
      $key = substr($key,0,$pos);
    $retVal["sort_$key"] = "sort" . ($this->pag_Asc?"Up":"Dn");
  }
  $retVal["sort_link"] = $this->paginateHTML($pageAmt);
  return $retVal;
}
*/

// ------...------...------...------...------...------...------...------...------...------...------

function format_date($fmt,$dat) {
  if (is_numeric($fmt)) {
  }
  else {
    if (empty($dat))
      return "";
    if (($dat == "0000-00-00") || ($dat == "0000-00-00 00:00:00"))
      return "";
    return date($fmt,strtotime($dat));
  }
}











































/*

// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------
///DETETE DE:LETETE
function formPost($_POST) {
  $sets = "";
  $this->sql_Dif = $this->formDifArray($_POST);
  foreach ($this->sql_Dif as $key => $val) {
    $sets = $this->addSET($sets,$key,$val);
  }
  return $this->sqlUpdate($sets);
}

// ------...------...------...------...------...------...------...------...------...------...------

function GetDATA($_POST) {
  $this->sql_Dif = $this->formDifArray($_POST);
  foreach ($this->sql_Dif as $key => $val) 
    eval("\$this->_$key = \$val;"); 
  return $this->_id;
}

// ------...------...------...------...------...------...------...------...------...------...------

function SetDATA() {
  if (count($this->sql_Dif)) {
    $sets = "";
    foreach ($this->sql_Dif as $key => $val)
      $sets = $this->addSET($sets,$key,$val);
    $this->_id = $this->sqlUpdate($sets);
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function PostSAVE($cmd,$lastPage,$idName) {
  // remove after replaced with formHandleCmd
  switch ($cmd) {
    case 'apply':
      return $this->SaveDATA();
      break;

    case 'save':
      if ($this->SaveDATA())
        exitTo($lastPage);
      break;

    case 'cancel':
      exitTo($lastPage);
      break;

    case 'new':
      if ($this->SaveDATA()) {
        SetSessionVar($idName,0);
        exitTo($GLOBALS['g_PHPSELF']);
      }
      break;

    case 'delete':
      break;

    case 'prev':
      if ($this->SaveDATA()) {
        SetSessionVar($idName,getCookie('id'));
        exitTo($GLOBALS['g_PHPSELF']);
      }
      break;

    case 'next':
      if ($this->SaveDATA()) {
        SetSessionVar($idName,getCookie('id'));
        exitTo($GLOBALS['g_PHPSELF']);
      }
      break;
    default:
      return true;
  }
  return false;
}

// ------...------...------...------...------...------...------...------...------...------...------

function PostDATA2() {
  //remove after replaced by builodformdata
  $ary = array();

  if (func_num_args() == 1) 
    $post = func_get_arg(0);

  foreach (get_object_vars($this) as $key => $val)  {
    if ($key{0} != "_")
      continue;

    $base                 = "form_$this->sql_Tab$key";
    $ary[$base . "_name"] = $this->sql_Tab . $key;

    if (!empty($post["$this->sql_Tab$key"]))
      $val = $post["$this->sql_Tab$key"];

    if (is_numeric($val)) {
      $nbr = ($val?$val:"0");
      $ary[$base]                = $nbr;
      $ary[$base . "_check"]     = ($val?"checked":"");
      $ary[$base . "_radio$nbr"] = "checked";
      $ary[$base . "_combo$nbr"] = "selected";
      $ary[$base . "_uncheck"]   = "<input type='hidden' name='un_" . $this->sql_Tab . $key . "' value='0'>";
    }
    else {
      $ary[$base] = str_replace("\"","&quot;",($val?$val:""));
    }
  }
  return $ary;
}

// ------...------...------...------...------...------...------...------...------...------...------

function clear() {
  foreach (get_object_vars($this) as $key => $val)  {
    if ($key{0} != "_")
      continue;
    eval("\$this->$key = '';");
  }
}
*/
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

// ------...------...------...------...------...------...------...------...------...------...------
}
// ------...------...------...------...------...------...------...------...------...------...------
?>