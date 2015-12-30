<?php
error_reporting(E_ALL);
runreg();

include_once '../server/dbconn.php';

function runreg() {
  $conn = DBConn();

  // select a collection (analogous to a relational database's table)
  $colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent', 
  'officetelrent', 'officetelsale', 'aptlots', 'landsale'];

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
    case 'officetelrent':
    case 'officetelsale':
    case 'aptlots':
      $grouparr = ['state', 'city', 'county', 'region', 'aptName','area'];
      break;
    case 'housesale':
    case 'houserent':
      $grouparr = ['state','city', 'county', 'region'];
      break;

    case 'landsale':
      $grouparr = ['state','city', 'county', 'region', 'type', 'usedType'];
      break;

    default:
      assert(false);
 }
  mktable($db, $tname);
  mkreg($db, $colname, $tname, $grouparr);
}

function mktable($db, $tname) {
  if ($db->query("Drop Table IF EXISTS $tname") !== TRUE) {
    die("Error dropping table: $sql\n $db->error");
  }

  $sql = "Create Table IF NOT EXISTS $tname(k varchar(255), v varchar(255))";
  $sql .= " ENGINE = MYISAM;";

  if ($db->query($sql) !== TRUE) {
    die("Error creating table: $sql\n $db->error");
  }

  $sql = "CREATE INDEX k_index ON $tname (k)";
  if ($db->query($sql) !== TRUE) {
    // die("Error creating table: $sql\n $db->error");
  }
}

function mkreg($db, $colname, $tname, $grouparr) {
  $db->query("BEGIN");
  foreach ($grouparr as $i => $value) {
    if ($i != 0) {
      mkonereg($db, $colname, $tname, $grouparr, $i);
    }
  }

  $db->query("COMMIT");
}


// select distinct CONCAT_WS('::', state, city), county from housesale;
function mkonereg($db, $colname, $tname, $grouparr, $last) {
  $val = $grouparr[$last];
  $sql = "select distinct CONCAT_WS('::' ";
  for($i=0; $i<$last; $i++) {
    $sql .= ", $grouparr[$i]";
  }

  $sql .= ") as k, $val as v from $colname;";

  $result = $db->query($sql);

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        // Skip the empty
        if ($row['v']=='') {
          continue;
        }

        print_r($row);

        $sqinsert = "INSERT DELAYED INTO $tname SET k='";
        $sqinsert.= $db->real_escape_string($row['k']) ."'";
        $sqinsert.= ", v='" . $db->real_escape_string($row['v']) . "'";

        if ($db->query($sqinsert) !== TRUE) {
            die ("Error: $sqinsert\n $db->error");
        }
    }
  } else {
      echo "0 results";
  }

  echo ($sql . "\n");
}

?>
