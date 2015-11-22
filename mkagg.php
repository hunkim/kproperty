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
  switch($colname) {
    case 'flatsale':
      $stat_sql = "select year, month, count(*) as count, ".
        " REPLACE(format(avg(amount/area)*3.33,2), ',', '') as avgAmtArea ";
      if ($tname != 'aptsale') {
        $stat_sql .= ", REPLACE(format(avg(amount/landArea)*3.33,2), ',', '') as avgAmtLand ";
      }
      $stat_sql .=	" from $tname where amount > 0 and year = $year AND month = $month";
     break;

  default:
    $stat_sql = "select year, month, count(*) as count, ".
      " REPLACE(format(avg(deposit/area)*3.33,2), ',', '') as avgDeposit ";
    $stat_sql .= ", REPLACE(format(avg(monthlyPay/area)*3.33,2), ',', '') as avgRent ";
    $stat_sql .=	" from $tname where year = $year AND month = $month";
  }

  $stat_sql.= " group by year, month";

  $groupkey = ['state','city','county'=>'$county'];

  // insert with empty
  $sql_grp = "";
  mkoneagg($db, $tname, $sql);
  foreach ($groupkey as $key => $va) {
      $sql_grp .= ", $va ";
      mkonegrp($db, $tname, $query, $grouparr);
  }
}

function mkoneagg($db, $tname, $sql) {
  $tnameagg = $tname."_agg";
  echo ($sql);
}
?>
