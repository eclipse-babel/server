<?
// ------...------...------...------...------...------...------...------...------...------...------
require_once("aptana_global/consts.inc.php");
define("COOKIE_REMEMBER","cAPTANAX");
define("COOKIE_SESSION" ,"sAPTANAX");
// ------...------...------...------...------...------...------...------...------...------...------

function __autoload($class_name) {
  require_once("aptana_global/sql/$class_name.class.php");
}

// ------...------...------...------...------...------...------...------...------...------...------

function SetSessionVar($varName,$varVal) {
  global $_SESSION;

  $GLOBALS[$varName]  = $varVal;
  $_SESSION[$varName] = $varVal;
  return $varVal;
}

// ------...------...------...------...------...------...------...------...------...------...------

function GetSessionVar($varName) {
  if (isset($_SESSION[$varName]))
    return $_SESSION[$varName];
  return 0;
}

// ------...------...------...------...------...------...------...------...------...------...------

function ClearSessionVar($varName) {
  if (isset($_SESSION[$varName]))
    unset($_SESSION[$varName]);
  if (isset($GLOBALS[$varName]))
    unset($GLOBALS[$varName]);
  return "";
}

// ------...------...------...------...------...------...------...------...------...------...------

function GrabSessionVar($varName) {
  $retVal = GetSessionVar($varName);
  ClearSessionVar($varName);
  return $retVal;
}

// ------...------...------...------...------...------...------...------...------...------...------

function esc_str($str) {
  $str = str_replace("%","#",$str);
  $str = str_replace("{SELF","{ SELF",$str);
  return mysql_escape_string($str);
}

// ------...------...------...------...------...------...------...------...------...------...------

function debugLog($str) {
  dump(LOG2DEBUG,$str);
}

// ------...------...------...------...------...------...------...------...------...------...------

function errorLog($str) {
  dump(LOG2ERROR,$str);
}

// ------...------...------...------...------...------...------...------...------...------...------

function userLog($str) {
  dump(LOG2USER,$str);
}

// ------...------...------...------...------...------...------...------...------...------...------

function dump($where,$str) {
  switch ($where) {
    case LOG2USER:
      log2File("php_user.log",$str,"#");
      break;
    case LOG2ERROR:
      log2File("php_error.log",$str,"*");
      break;
    case LOG2DEBUG:
      log2File("php_debug.log",$str,"-");
      break;
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function log2File($fileName,$str,$delim) {
  $fileName = "/var/log/" . $fileName;
  $date     = date('D.M.d H:i:s');
  $date     = date('H:i:s Y.m.d');
  $addr     = getenv("REMOTE_ADDR");
  $acct     = GetSessionVar('c_acctID');
  $user     = GetSessionVar('c_userID');

  $file = fopen($fileName,"a+");
  fwrite($file,"$date $addr [$acct:$user] $delim $str\n");
  fclose($file);
}

// ------...------...------...------...------...------...------...------...------...------...------

function getHtmlTmpl($fileName) {
  $html = file_get_contents($fileName);
  $html = str_replace("\"","\\\"",$html);
  return $html;
}

// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------
// ------...------...------...------...------...------...------...------...------...------...------

function sqlOpen($database) {

  if (!($ini = @parse_ini_file('base.conf'))) {
    errorLog("Failed to find/read database conf file - aborting.");
    exitTo("/error.php?errNo=101300","error: 101300 - database conf can not be found");
  }
  $ini['db_read_pass'] = "aptana$$1";
  if (!mysql_connect($ini['db_read_host'],$ini['db_read_user'],$ini['db_read_pass'])) {
    errorLog("Failed attempt to connect to server - aborting.");
    exitTo("/error.php?errNo=101301","error: 101301 - data server can not be found");
  }

  if (empty($database))
    $database = $ini['db_read_name'];
  if (isset($database)) {
    if (!mysql_select_db($database)) {
      errorLog("Failed attempt to open database: $database - aborting \n\t" . mysql_error());
      exitTo("/error.php?errNo=101303","error: 101303 - unknown database name");
    }
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlQuery($cmd) {
  if (!($qry = mysql_query($cmd))) 
    errorLog($cmd . "\n" . mysql_error());
  return $qry;
}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlGetRec($statement) {
  if ($qry = sqlQuery($statement)) {
    if ($rec = mysql_fetch_assoc($qry))
      return $rec;
  }
  else
    errorLog($statement . "\n" . mysql_error());
  return 0;

}

// ------...------...------...------...------...------...------...------...------...------...------

function sqlGetCnt($statement) {
  if ($qry = sqlQuery($statement)) {
    if ($rec = mysql_fetch_array($qry))
      return $rec[0];
  }
  else
    errorLog($statement . "\n" . mysql_error());
  return 0;

}

// ------...------...------...------...------...------...------...------...------...------...------

function exitTo() {
  if (func_num_args() == 1) {
    $url = func_get_arg(0);
    header("Location: $url");
    exit;
  }
  else if (func_num_args() == 2) {
    $url  = func_get_arg(0);
    $arg1 = func_get_arg(1);
    SetSessionVar("errStr",$arg1);
    header("Location: $url");
    exit;
  }
  else if (func_num_args() == 3) {
    $url  = func_get_arg(0);
    $arg1 = func_get_arg(1);
    $arg2 = func_get_arg(2);
    SetSessionVar($arg1,$arg2);
    header("Location: $url");
    exit;
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function flushTo() {
  if (func_num_args() == 1) {
    $url = func_get_arg(0);
    //debugLog("exitTo: $url");
    header("Location: $url");
    flush();
    ob_flush();
    ob_end_flush();
  }
  if (func_num_args() == 3) {
    $url  = func_get_arg(0);
    $arg1 = func_get_arg(1);
    $arg2 = func_get_arg(2);
    //debugLog("exitTo: $url");
    SetSessionVar($arg1,$arg2);
    header("Location: $url");
    flush();
    ob_flush();
    ob_end_flush();
  }
}

// ------...------...------...------...------...------...------...------...------...------...------

function getCookie($name) {
  $retVal = "";
  if (isset($_COOKIE[$name])) {
    $retVal = $_COOKIE[$name];
    setcookie($name,"",time()-3600,"/");
    unset($_COOKIE[$name]);
    //userLog("$name = $retVal");
  }
  return $retVal;
}

// ------...------...------...------...------...------...------...------...------...------...------

function isValidDate($dat) {
  if (empty($dat) || (substr($dat,0,10) == "0000-00-00"))
    return true;
 if ((strlen($dat)<10) || (strlen($dat)>10))
   return false;
 if (substr_count($dat,"/")!=2)
   return false;
 list($mon,$day,$yer) = @split("/",$dat);
 if (($mon < 1) || ($mon > 12))
   return false;
 if (($day < 1) || ($day > 31))
   return false;
 if (($yer < 1901) || ($yer > 2200))
   return false;
 return true;
}

// ------...------...------...------...------...------...------...------...------...------...------

function GetFormID($idStr) {
  if ($idNbr = getCookie("c_$idStr"))
    SetSessionVar("s_$idStr",($idNbr == -1)?0:$idNbr);
  return GetSessionVar("s_$idStr");
}

// ------...------...------...------...------...------...------...------...------...------...------

function guidNbr() {
  return md5(uniqid(rand(),true));
}

// ------...------...------...------...------...------...------...------...------...------...------

function guidStr() {
  $str = strtoupper(md5(uniqid(rand(),true)));
  $str = substr($str,0,8) . "-" .
         substr($str,8,4) . "-" .
         substr($str,12,4). "-" .
         substr($str,16,4). "-" .
         substr($str,20);
  return $str;
}

// ------...------...------...------...------...------...------...------...------...------...------
?>
