<?php
error_reporting(E_ALL);
MongoCursor::$timeout = -1;

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
$col = new MongoCollection($db, 'system.profile');
$cursor = $col->find([op =>"query"]);

$allresult=[];
$ordresult=[];

foreach ($cursor as $result) {
  $colname = $result['ns'];

  $query = $result['query']['$query'];
  print_r($query);
  //continue;

  $key = "";
  foreach($query as $k=>$v) {
    $key .= "$k:1,";
  }

  $allresult[$colname][$key]=1;

  $key = "";
  foreach($result['query']['$orderby'] as $k=>$o) {
    $key .= "$k:$o,";
  }

 $ordresult[$colname][$key]=1;

}

foreach($allresult as $k=>$arr) {
  foreach($arr as $k2=>$val) {
    if (strpos($k, "system.profile")===false) {
      $k = str_replace("trend.", "db.", $k);
      echo "$k.createIndex({ $k2 })\n";
    }
  }
}

foreach($ordresult as $k=>$arr) {
  foreach($arr as $k2=>$val) {
    if (strpos($k, "system.profile")===false) {
      $k = str_replace("trend.", "db.", $k);
      echo "$k.createIndex({ $k2 })\n";
    }
  }
}

?>
