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

  // mysql
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $cursor = $col->find()->timeout(-1)->limit(10);

  $idx = 0;
  foreach ($cursor as $doc) {
    if ($idx++===0) {
        $table = createTable($conn, $colname, $doc);
        echo ($table);
    }

    //insrt DB
    insert($conn, $colname, $doc);
  }

  $conn->close();
}

function insert($conn, $colname, $doc) {
  $sql = "INSERT IGNORE INTO $colname SET ";

  $idx = 0;
  foreach($doc as $key => $val) {
    if ($idx++ != 0) {
      $sql .= ", \n\t";
    }
    $sql .= $key. '=' . typeesc($val);
  }

  $sql .= ";\n";

  echo $sql;
}

function createTable($conn, $colname, $doc) {
    $sql = "Create Table IF NOT EXISTS $colname (\n";
    $idx = 0;
    foreach($doc as $key => $val) {
      $sqltype = getSQLType($val);

      if ($idx++ != 0) {
        $sql .= ",\n";
      }
      if ($key=="_id") {
        $sql .= "\t$key $sqltype NOT NULL UNIQUE";
      } else {
        $sql .= "\t$key $sqltype";
      }


    }

    $sql .= ");";

    echo ($sql);

    if ($conn->query($sql) === TRUE) {
      echo "Table MyGuests created successfully";
    } else {
      die("Error creating table: " . $conn->error);
    }
}

function getSQLType ($val) {
  $type = gettype($val);
  switch($type) {
    case "integer":
      return "int";
    case "boolean":
      return "int";
    case "double":
      return "double";
    case "float":
      return "float";
    case "string":
    case "object":
      return "varchar(255)";

    default:
    return "no support ($type)";
  }
}

function typeesc ($val) {
  $type = gettype($val);
  switch($type) {
    case "integer":
    case "boolean":
    case "double":
    case "float":
      return $val;
    default:
      return "'" . $val . "'";
  }
}
?>
