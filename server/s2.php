<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colname = str_replace( '/', '',trim($_SERVER['PATH_INFO']));

if (!$colname) {
  $debug = true;
  $colname = 'housesale';
}

// New DB
$collection = new MongoCollection($db, $colname . "_agg");

//$query = ['amount'=>['$gt' => 0]] ;
// TODO: later need to handle key
$query = [] ;
foreach ($_GET as $key => $value) {
  //echo "Key: $key; Value: $value<br />\n";
  if ($value == '') {
    $query[$key] = null;
    continue;
  }

  if ($key == 'debug') {
    $debug = true;
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

// Really no region, not null, it should be ""
if($query['aptName']!=null) {
  if ($query['region']==null) {
    $query['region']="";
  }
}
if($debug) {
  print_r($query);
}

// find everything in the collection
$cursor = $collection->find($query, ['_id'=>0])->sort(['year'=>1, 'month'=>1]);

//echo json_encode(iterator_to_array($cursor),
echo json_encode(iterator_to_array($cursor),
  JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

?>
