<?php
error_reporting(E_ALL);

test();

function test() {
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
	// Check connection
	if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
	}
  mkagg($conn, "housesale", 2015, 10);
}

function mkagg($db, $tname, $year, $month) {
  $groupkey = ['', 'state','city','county'];



  foreach ($groupkey as $key => $value) {
      $arr[] = $value;
      mkoneagg($db, $tname, $year, $month, $arr);
  }
}

function mkoneagg($db, $tname, $year, $month, $arr) {
  $tnameagg = $tname."_agg";

  $keys = "";
  foreach ($arr as $key) {
    if ($key!='') {
      $keys .=", $key";
    }
  }

  switch($tname) {
    case 'flatsale':
      $sql = "select CONCAT_WS('::' $keys) as key, year, month, count(*) as count, ".
        " REPLACE(format(avg(amount/area)*3.33,2), ',', '') as avgAmtArea ";
      if ($tname != 'aptsale') {
        $sql .= ", REPLACE(format(avg(amount/landArea)*3.33,2), ',', '') as avgAmtLand ";
      }
      $sql .=	" from $tname where amount > 0 and year = $year AND month = $month";
     break;

  default:
    $sql = "select CONCAT_WS('::' $keys) as key, year, month, count(*) as count, ".
      " REPLACE(format(avg(deposit/area)*3.33,2), ',', '') as avgDeposit ";
    $sql .= ", REPLACE(format(avg(monthlyPay/area)*3.33,2), ',', '') as avgRent ";
    $sql .=	" from $tname where year = $year AND month = $month";
  }

  $sql.= " group by year, month $keys";

  echo "$sql\n";
}
?>
