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
  switch($tname) {
    case 'flatsale':
      $sql = "select year, month, count(*) as count, ".
        " REPLACE(format(avg(amount/area)*3.33,2), ',', '') as avgAmtArea ";
      if ($tname != 'aptsale') {
        $sql .= ", REPLACE(format(avg(amount/landArea)*3.33,2), ',', '') as avgAmtLand ";
      }
      $sql .=	" from $tname where amount > 0 and year = $year AND month = $month";
     break;

  default:
    $sql = "select year, month, count(*) as count, ".
      " REPLACE(format(avg(deposit/area)*3.33,2), ',', '') as avgDeposit ";
    $sql .= ", REPLACE(format(avg(monthlyPay/area)*3.33,2), ',', '') as avgRent ";
    $sql .=	" from $tname where year = $year AND month = $month";
  }

  $sql.= " group by year, month";

  $groupkey = ['state','city','county'=>'$county'];

  // insert with empty
  $sql_grp = "";
  mkoneagg($db, $tname, $sql);
  foreach ($groupkey as $key => $va) {
      $sql_grp .= ", $va ";
      mkonegrp($db, $tname, $sql);
  }
}

function mkoneagg($db, $tname, $sql) {
  $tnameagg = $tname."_agg";
  echo ($sql);
}
?>
