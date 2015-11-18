<?php
error_reporting(E_STRICT);
MongoCursor::$timeout = -1;

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
$col = new MongoCollection($db, 'system.profile');
$cursor = $col->find([op =>"query"]);


foreach ($cursor as $result) {
  $colname = $result['ns'];
  $keys = "";

  print_r($result['query']['$query']);


  foreach($result['query']['$query'] as $k=>$v) {
    $key .= "$k:1,";
  }

  $allresult[$colname][$key]=1;
  print_r($allresult);

  $key = "";
  foreach($result['query']['$orderby'] as $k=>$o) {
    $key .= "$k:$o,";
  }


 $ordresult[$colname][$key]=1;
  print_r($ordresult);

}
?>
