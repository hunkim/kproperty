<?php
error_reporting(E_ALL);

//test();

function test() {
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
	// Check connection
	if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
	}

  // select a collection (analogous to a relational database's table)
  $colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];
  foreach ($colnames as $tname) {
    mkagg($conn, $tname, 2015, 10);
  }
}

function mkagg($db, $tname, $year, $month) {
  echo "Making agg $tname $year $month...\n";
  $tnameagg = $tname."_agg";

  $create = "Create Table IF NOT EXISTS $tnameagg (k varchar(255), year int, month int, count int,";

  switch($tname) {
    case 'housesale':
    case 'flatsale':
      $create .= "avgAmtArea double, avgAmtLand double)";
      break;
    case 'aptsale':
      $create .= "avgAmtArea double)";
      break;
    default:
      $create .= "avgDeposit double, avgRent double)";
      break;
  }
  $create .= " ENGINE = MYISAM;";

  if ($db->query($create) !== TRUE) {
    die("Error creating table: $create\n $db->error");
  }

  $sql = "CREATE INDEX k_index ON $tnameagg (k)";
  if ($db->query($sql) !== TRUE) {
  //  die("Error creating table: $sql\n $db->error");
  }

  $groupkey = ['', 'state','city','county'];
  foreach ($groupkey as $key => $value) {
      $arr[] = $value;
      mkoneagg($db, $tname, $tnameagg, $year, $month, $arr);
  }
}

function mkoneagg($db, $tname, $tnameagg, $year, $month, $arr) {
  $keys = "";
  foreach ($arr as $key) {
    if ($key!='') {
      $keys .=", $key";
    }
  }

  $concat = "CONCAT_WS('::' $keys)";
  if ($keys=="") {
    $concat = "CONCAT_WS('::','')";
  }

  switch($tname) {
    case 'housesale':
    case 'aptsale':
    case 'flatsale':
      $sql = "insert DELAYED into $tnameagg select $concat as k, year, month, count(*) as count, ".
        " avg(amount/area) as avgAmtArea ";
      if ($tname != 'aptsale') {
        $sql .= ", avg(amount/landArea) as avgAmtLand ";
      }
      $sql .=	" from $tname where amount > 0 and year = $year AND month = $month";
     break;

  default:
    $sql = "insert DELAYED into $tnameagg select $concat as k, year, month, count(*) as count, ".
      " avg(amount/area) as avgDeposit ";
    $sql .= ", avg(monthlyPay/area) as avgRent ";
    $sql .=	" from $tname where year = $year AND month = $month";
  }

  $sql.= " group by year, month $keys";

  if ($db->query($sql) !== TRUE) {
    die("Error executing table: $sql\n $db->error");
  }
//  echo "$sql\n";
}
?>
