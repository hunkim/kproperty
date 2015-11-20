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
    mongo2mysql($db, $name, 2015, 10);
}

/* The main controller */
// $dir should end with '/'
function mongo2mysql($db, $colname, $year, $month) {
  $col = new MongoCollection($db, $colname);
  $cursor = $col->find()->timeout(-1)->limit(10);
  $idx = 0;
  foreach ($cursor as $doc) {
    if ($idx++===0) {
        $table = createTable($colname, $doc);
        echo ($table);
    }

    foreach($doc as $key => $val) {
      echo ("[$idx] k: $key, v: $val (" . gettype($val) .")\n");
    }
  }
}

function createTable($colname, $doc) {
    $sql = "Create Table $colname (\n";

    foreach($doc as $key => $val) {
      $sqltype = getSQLType($val);
      $sql .= "\t%key $sqltype,\n";
    }

    $sql .= ");";
    return $sql;
}

function getSQLType ($val) {
  $type = gettype($val);
  switch($type)) {
    case "integer":
      return "int";
    case "boolean":
      return "int";
    case "double":
      reutrn "duble";
    case "float":
      retrun "float";
    case "string":
      return "varchar(255)";

    default:
    return "no support ($type)";
  }
}

?>
