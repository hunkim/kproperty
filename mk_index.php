<?php
error_reporting(E_ALL);
MongoCursor::$timeout = -1;
//error_reporting(E_STRICT);

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)

$keys['housesale'] = ['', 'state', 'city', 'county', 'region'];
$keys['aptsale'] = ['', 'state', 'city', 'county', 'region', 'aptName',  'area'];
$keys['flatsale'] = ['', 'state', 'city', 'county', 'region', 'aptName',  'area'];

$keys['houserent'] = ['', 'state', 'city', 'county', 'region', 'monthlyType'];
$keys['aptrent'] = ['', 'state', 'city', 'county', 'region', 'aptName',  'area', 'monthlyType'];
$keys['flatrent'] = ['', 'state', 'city', 'county', 'region', 'aptName',  'area', 'monthlyType'];


foreach ($keys as $key=>$val) {
  echo 'Adding index $key...\n';
  $col = new MongoCollection($db, $key);
  $col_agg = new MongoCollection($db, $key . '_agg');

  $idx_key=[];
  foreach ($val as $id) {
    if ($id) {
      $idx_key[$id] = 1;
    }
    $added_idx_key = array_merge($idx_key, ['year'=>1]);

    print_r($added_idx_key);

    $r = $col->createIndex($added_idx_key);
    print_r($r);
    $col->createIndex(['year'=>1, 'month'=>1]);
    $col->createIndex(['year'=>-1, 'month'=>-1]);

    $r = $col_agg->createIndex($added_idx_key);
    print_r($r);
    $col_agg->createIndex(['year'=>1, 'month'=>1]);
    $col_agg->createIndex(['year'=>-1, 'month'=>-1]);
  }
}
?>
