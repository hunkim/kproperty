<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$debug = false;
MongoCursor::$timeout = -1;

// connect
$m = new MongoClient();

// select a database
$db = $m->trend;
// select a collection (analogous to a relational database's table)
$colname = substr($_SERVER['PATH_INFO'], 1);
$colname .= "_reg";
$collection = $db->$colname;

$query = array();
$dquery = "state";
// TODO: later need to handle key
foreach ($_GET as $key => $value) {
  if ($value == '') {
    continue;
  }

  if ($key == 'debug') {
    $debug = true;
    continue;
  }

  if ($key=='query') {
    $dquery = $value;
    continue;
  }
  $query[$key] = urldecode($value);
}

if ($debug) {
  echo($dquery);
  print_r($query);
}

try{
  $cursor = $collection->distinct($dquery, $query);
} catch (MongoException $e) {
  echo "error message: ".$e->getMessage()."\n";
  echo "error code: ".$e->getCode()."\n";
  exit(1);
}

//echo json_encode(iterator_to_array($cursor),
echo json_encode(($cursor), JSON_UNESCAPED_UNICODE);
?>
