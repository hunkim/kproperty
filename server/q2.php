<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colname = substr($_SERVER['PATH_INFO'], 1);
$collection = new MongoCollection($db, 'aptsale');

$query = array();
// TODO: later need to handle key
foreach ($_GET as $key => $value) {
  //echo "Key: $key; Value: $value<br />\n";
  if ($key == 'startyear') {
    $query[$key] = array('$gte', $value);
  } else if ($key == 'endyear') {
    $query[$key] = array('$lte', $value);
  } else {
    $query[$key] = urldecode($value);
  }
}

// find everything in the collection
$cursor = $collection->find();
$cursor->limit(500);

//echo json_encode(iterator_to_array($cursor), 	
echo json_encode(iterator_to_array($cursor), 	
	JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
?>
