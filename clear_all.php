<?php

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

foreach ($colnames as $colname) {
  echo "Removing $colname...\n";
  $col = new MongoCollection($db, $colname);
  $col->drop();
  $col->deleteIndexes();

  $col2name = $colname."_agg";
  $col2 = new MongoCollection($db, $col2name);
  $col2->drop();
  $col2->deleteIndexes();
}
?>
