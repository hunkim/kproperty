<?php
header("Access-Control-Allow-Origin: *");
//header("Accept-Encoding: gzip,deflate");
//header("Content-Encoding: gzip");
header("Content-Type: application/json; charset=UTF-8");

include_once 'dbconn.php';


// get app name
$tname = substr($_SERVER['PATH_INFO'], 1);

if (!$tname) {
  exit(0);
}

$stat_sql = "select year, month, count(*) as count, ";
$stat_simple = "select year, month, count, ";

switch($tname) {
	case 'aptsale':
	case 'officetelsale':
	case 'landsale':
	case 'aptlots':
			$stat_sql .= " REPLACE(format(avg(amount/area),2), ',', '') as avgAmtArea ";
			$stat_sql .=	" from $tname where amount > 0 and year >= ? AND year <= ?";

			$stat_simple .= " REPLACE(format(avgAmtArea,2), ',','') as avgAmtArea ";
			break;

	case 'housesale':
	case 'flatsale':

			$stat_sql .= " REPLACE(format(avg(amount/area),2), ',', '') as avgAmtArea ";
			$stat_sql .= ", REPLACE(format(avg(amount/landArea),2), ',', '') as avgAmtLand ";
			$stat_sql .=	" from $tname where amount > 0 and year >= ? AND year <= ?";


			$stat_simple .= " REPLACE(format(avgAmtArea,2), ',','') as avgAmtArea ";
			$stat_simple .= ", REPLACE(format(avgAmtLand,2), ',', '') as avgAmtLand  ";
		 	break;

 // all others
	default:
			$stat_sql .= " REPLACE(format(avg(amount/area),2), ',', '') as avgDeposit ";
			$stat_sql .= ", REPLACE(format(avg(monthlyPay/area),2), ',', '') as avgRent ";
			$stat_sql .=	" from $tname where year >= ? AND year <= ?";

			$stat_simple .= " REPLACE(format(avgDeposit,2), ',', '') as avgDeposit, ";
			$stat_simple .= " REPLACE(format(avgRent,2), ',', '') as avgRent ";
}

$stat_simple .= " from $tname" . "_agg where k=? AND year >= ? AND year <= ?  order by year, month";

// to append after adding more search keys
$stat_sql_append = " group by year, month order by year, month ";

processQuery($stat_sql, $stat_sql_append, $stat_simple);

/**
*/
function processQuery($sql, $sql_append, $simple) {
  $startyear = intval($_GET['startyear']);
  $endyear = intval($_GET['endyear']);

  if ($endyear ==0) $endyear = 3000;

  $params = [&$startyear, &$endyear];
  $type = "ii";

	$searchKey="";
	$i=0;
	$debug = false;

	$hasMonthlyType = false;
  foreach ($_GET as $key=>$val) {
		if ($key=='startyear' || $key=='endyear') {
			continue;
		}

		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($val=="") {
			continue;
		}

		if ($i++>0) {
			$searchKey .= "::";
		}

		if ($key=='monthlyType') {
			$hasMonthlyType = true;
		}

  	$sql .= " AND " . $key . "=? ";
		$type .= "s";

		// need array element here, since we need a new variable
		$decodedvar = urldecode($val);
		$decoded_val[$key] = $decodedvar;
		$params[] = &$decoded_val[$key];

		$searchKey .= $decodedvar;
  }

	// Add SQL
  $sql .= $sql_append;

	// only three, let's use simple sql
  if ($i<=3 && !$hasMonthlyType) {
		$sql = $simple;
		$params = [&$searchKey, &$startyear, &$endyear];
		$type ="sii";
	}

	if($debug) {
 		print_r($params);
		echo ($sql);
		echo ($type);
	}

  $conn = DBConn($debug);

  $stmt = $conn->prepare($sql);
	if (!$stmt) {
		 if ($debug) {echo ("Prepare $sql failed: ($conn->errno)  $conn->error");}
		 exit(0);
	}

  // http://stackoverflow.com/questions/16236395/bind-param-with-array-of-parameters
  call_user_func_array(array($stmt, "bind_param"), array_merge(array($type), $params));

  $stmt->execute();

	// Need to install
	// sudo apt-get install php5-mysqlnd
  $result = $stmt->get_result();

  $rows=array();
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }

	// JSON_PRETTY_PRINT|
  print json_encode($rows,JSON_UNESCAPED_UNICODE);

	$conn->close();
}
?>
