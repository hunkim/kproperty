<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$debug = false;
MongoCursor::$timeout = -1;

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

  if ($key == 'area') {
    $query['area'] = floatval($value);
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

try {
    // find everything in the collection
    $cursor = $collection->find($query)->sort(['year'=>-1, 'month'=>-1]);
    $cursor->limit(500);
} catch (MongoException $e) {
  echo "error message: ".$e->getMessage()."\n";
  echo "error code: ".$e->getCode()."\n";
  exit(1);
}

//echo json_encode(iterator_to_array($cursor),
echo json_encode(iterator_to_array($cursor),JSON_UNESCAPED_UNICODE);
?>
