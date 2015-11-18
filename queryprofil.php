<?php
error_reporting(E_STRICT);
MongoCursor::$timeout = -1;

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
$col = new MongoCollection($db, 'system.profile');
$cursor = $col->find({op:"query"})

//$results = $cursor['result'];
foreach ($cursor as $result) {
  print_r($result);
}
?>
