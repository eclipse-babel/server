<?
// if not logged in redirect to login page
// otherwise show choices
require_once("aptana_global/aptana.inc.php");
require_once("aptana_global/agent.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("export");

$project_name = (isset($_POST['project_name'])?$_GET['project_name']:'');
//$package_name = (isset($_POST['package_name'])?$_GET['package_name']:'');
$entry_name   = (isset($_POST['entry_name'])?$_GET['entry_name']:'');
$entry_value  = (isset($_POST['entry_value'])?$_GET['entry_value']:'');
//$repo_path    = (isset($_POST['repo_path'])?$_GET['repo_path']:'');
$lang_code    = (isset($_POST['lang_code'])?$_GET['lang_code']:'en');
$lang_id      = 0;


/*
$lang_code  = "pt";
$project_name = "Aptana Unified Editor";
$entry_name = "FileContextManager_RemovingFileContext";
$entry_value = "Removendo o caminho: {0}";
*/

$cmd = "SELECT * FROM languages WHERE iso_code='$lang_code'";
if ($qry = mysql_query($cmd)) {
  if ($rec = mysql_fetch_object($qry)) {
    $lang_id = $rec->id;
  }
}


if (strlen($lang_code)<1 || $lang_id <= 1) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>invalid language code</error>\n";
  exit;
}

if (empty($project_name)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no project_name</error>\n";
  exit;
}


if (empty($entry_name)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no entry_name</error>\n";
  exit;
}


if (empty($entry_value)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>no entry_value</error>\n";
  exit;
}


$cmd = "SELECT * FROM projects WHERE name='$project_name'";
if ($qry = mysql_query($cmd)) {
  $rec = mysql_fetch_object($qry);
}

if (empty($rec)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>unknown project</error>\n";
  exit;
}


$project_id = $rec->id;
unset($rec);

$cmd = "SELECT * FROM entries WHERE language_id=1 AND name='$entry_name'";
if ($qry = mysql_query($cmd)) {
  $rec = mysql_fetch_object($qry);
}

if (empty($rec)) {
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<error>unknown entry</error>\n";
  exit;
}


$entry = new entries_iu(0);
$entry->sqlLoad("language_id=$lang_id AND project_id=$project_id AND name='$entry_name'");

$entry->_name        = $entry_name;
$entry->_value       = $entry_value;
$entry->_language_id = $lang_id;
$entry->_project_id  = $project_id;

$entry->selfPost();


echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<success>1</success>\n";


// ------...------...------...------...------...------...------...------...------...------...------

echo <<< toTheEnd
toTheEnd;

// ------...------...------...------...------...------...------...------...------...------...------
?>
