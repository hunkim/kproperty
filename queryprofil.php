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
  $colname = $result['ns']);
  $keys = "";
  foreach($result['query']['$query'] as $key) {
    $key .= "$key:1,";
  }

  $allresult[$colname][$key]=1;

  print_r($result['query']['$orderby']);
}
?>
