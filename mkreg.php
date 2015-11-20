<?php
error_reporting(E_ALL);
MongoCursor::$timeout = -1;
runreg();

function runreg() {
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  // select a collection (analogous to a relational database's table)
  $colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

  foreach ($colnames as $colname) {
    $tname = $colname."_reg";

    // Let's remove all first
    //$col2->drop([]);

    // add agg information
    mkregall($conn, $colname, $tname);
  }
}

function mkregall($db, $colname, $tname) {

  switch ($colname) {
    case 'aptsale':
    case 'flatsale':
    case 'aptrent':
    case 'flatrent':
      $grouparr = ['state', 'city', 'county', 'region', 'aptName','area'];
      break;
    case 'housesale':
    case 'houserent':
      $grouparr = ['state','city', 'county', 'region'];
      break;

    default:
      assert(false);
 }
  mktable($db, $tname);
  mkreg($db, $colname, $tname, $grouparr);
}

function mktable($db, $tname) {
  $sql = "Create Table IF NOT EXISTS $tname(key varchar(255), val varchar(255),";
  $sql .= "CONSTRAINT u UNIQUE (key,val));";

  if ($db->query($sql) !== TRUE) {
    die("Error creating table: $sql\n $db->error");
  }

  if ($db->query("ALTER TABLE $tname ADD INDEX (key);") !== TRUE) {
    die("Error creating table: $sql\n $db->error");
  }
}

function mkreg($db, $colname, $tname, $grouparr) {
  foreach ($grouparr as $i => $value) {
    if ($i != 0) {
      mkonereg($db, $colname, $tname, $grouparr, $i);
    }
  }
}


// select distinct CONCAT_WS('::', state, city), county from housesale;
function mkonereg($db, $colname, $tname, $grouparr, $last) {
  $val = $grouparr[$last];
  $sql = "select distinct CONCAT_WS('::' ";
  for($i=0; $i<$last; $i++) {
    $sql .= ", $grouparr[$i]";
  }

  $sql .= "), $val from $colname;";

  echo ($sql . "\n");
}

?>
