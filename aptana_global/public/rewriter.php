<?
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
