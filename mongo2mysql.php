#!/usr/bin/php
<?php
//MongoCursor::$timeout = -1;
error_reporting(E_ALL);
assert_options(ASSERT_BAIL, true);

$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');

//main($argv[1] . "/");
$colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];
foreach ($colnames as $name) {
    mongo2mysql($name);
}

/* The main controller */
// $dir should end with '/'
function mongo2mysql($db, $colname, $year, $month) {
  $col = new MongoCollection($db, $col);
  $cursor = $col->find()->timeout(-1)->limit(10);
  foreach ($cursor as $doc) {
    var_dump($doc);
  }
}

?>
