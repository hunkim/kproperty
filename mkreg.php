<?php
error_reporting(E_ALL);
MongoCursor::$timeout = -1;
testreg();

function runreg() {
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  // select a collection (analogous to a relational database's table)
  $colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

  foreach ($colnames as $colname) {
    $col2name = $colname."_reg";

    // Let's remove all first
    $col2->drop([]);

    // add agg information
    mkregall($conn, $colname);
  }
}

function mkregall($db, $colname) {

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
  mkreg($db, $colname, $grouparr);
}


function mkreg($db, $colname, $grouparr) {
  foreach ($grouparr as $i => $value) {
    if ($i>1) {
      mkonereg($db, $colname, $grouparr, $i);
    }
  }
}

// select distinct CONCAT_WS('::', state, city), county from housesale;
function mkonereg($db, $colname, $grouparr, $last) {
  $val = $grouparr[$last];
  $sql = "select distinct CONCAT_WS('::' "
  for($i=0; i<$last; $i++) {
    $sql .= ", $grouparr[$i]";
  }

  $sql .= "), $val from $colname;";

  echo ($sql);
}

?>
