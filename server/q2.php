<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colname = str_replace( '/', '',trim($_SERVER['PATH_INFO']));
$collection = new MongoCollection($db, $colname);

$query = array();
// TODO: later need to handle key
foreach ($_GET as $key => $value) {
//echo "Key: $key; Value: $value<br />\n";
  if ($value == '') {
    continue;
  }

  if ($key == 'debug') {
    $debug = true;
    continue;
  }  

  if ($key == 'usedArea') {
    $query['usedArea'] = floatval($value);
    continue;
  }

  if ($key == 'startyear') {
    $endyear = $_GET['endyear'];
    $query['year'] = ['$gte' => intval($value), '$lte' => intval($endyear)];
  } else if ($key == 'endyear') {
    //
  } else {
    $query[$key] = urldecode($value);
  }
}

if ($debug) {
  print_r($query);
}

// find everything in the collection
$cursor = $collection->find($query)->sort(['year'=>-1, 'month'=>-1]);
$cursor->limit(500);

//echo json_encode(iterator_to_array($cursor), 	
echo json_encode(iterator_to_array($cursor), 	
	JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
?>
