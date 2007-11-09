<?
// if not logged in redirect to login page
// otherwise show choices
require_once("aptana_global/aptana.inc.php");
// ------...------...------...------...------...------...------...------...------...------...------
// should move this out of here
InitPage("get_entry");

$langID  = $_GET['lang'];
$projID  = $_GET['proj'];
$entryN  = $_GET['entry'];
$ctrl1   = $_GET['ctrl1'];
$ctrl2   = $_GET['ctrl2'];
$entr    = new entries_iu(0);

$entr->sqlLoad("name='$entryN' AND project_id=$projID AND language_id=$langID");

if ($entr->_language_id && ($entr->_language_id!=$langID))
exit;
if ($entr->_project_id && ($entr->_project_id!=$projID))
exit;

$entr->_language_id = $langID;
$entr->_project_id  = $projID;
$entr->_rating      = $ctrl2;
$entr->_value       = $ctrl1;
$entr->_name        = $entryN;
$entr->_user_id     = $s_userAcct;
$entr->_updated_on  = date("Y:m:d");
$entr->_updated_at  = date("H:i:s");
if ($entr->_id == 0) {
$entr->_created_on  = date("Y:m:d");
$entr->_created_at  = date("H:i:s");
}

if (($langID>1) && (trim($ctrl1) == '')) {
  if ($entr->_id)
    $entr->sqlCmd("DELETE FROM {SELF} WHERE id='" . $entr->_id . "'");
  exit;
}


echo $entr->selfPost();
print_r( $entr );
echo <<< toTheEnd

$langID $projID  $entryN  $ctrl2 $ctrl1



toTheEnd;

// ------...------...------...------...------...------...------...------...------...------...------

?>
