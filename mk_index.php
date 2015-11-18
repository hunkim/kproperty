<?php

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)

$keys['housesale'] = ["", state", "city", "county", "region"];
$keys['aptsale'] = ["", "state", "city", "county", "region", "aptName",  "area"];
$keys['flatsale'] = ["", "state", "city", "county", "region", "aptName",  "area"];

$keys['houserent'] = ["", "state", "city", "county", "region", "monthlyType"];
$keys['aptrent'] = ["", "state", "city", "county", "region", "aptName",  "area", "monthlyType"];
$keys['flatrent'] = ["", "state", "city", "county", "region", "aptName",  "area", "monthlyType"];


foreach ($keys as $key=>$val) {
  echo "Adding index $key...\n";
  $col = new MongoCollection($db, $key);
  $col_agg = new MongoCollection($db, $key . '_agg');

  $idx_key=[];
  foreach ($val as $id) {
    $idx_key[$id] = 1;
    $added_idx_key = array_merge($idx_key, ['key'=>1])
    print_r($added_idx_key);
  }
}

function addIndex($idx_key) {

}
?>
