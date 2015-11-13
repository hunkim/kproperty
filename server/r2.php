<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// connect
$m = new MongoClient();

// select a database
$db = $m->trend;
// select a collection (analogous to a relational database's table)
$colname = substr($_SERVER['PATH_INFO'], 1);
$collection = $db->$colname;

$query = array();
// TODO: later need to handle key
foreach ($_GET as $key => $value) {
//  echo "Key: $key; Value: $value<br />\n";
  $query[$key] = urldecode($value);
}

// find everything in the collection
$dquery = "state";

if ($_GET["state"]) {
  $dquery = "city";
  if ($_GET["city"]) {
    $dquery = "county";
    if ($_GET["county"]) {
      $dquery = "region";
    }
  }
}

$cursor = $collection->distinct($dquery, $query);

//echo json_encode(iterator_to_array($cursor), 	
echo json_encode(($cursor), 	
	JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
?>
