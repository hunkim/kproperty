<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

switch($tname) {
	case 'housesale':
	case 'aptsale':
	case 'flatsale':
			$stat_sql = "select year, month, count(*) as count, ".
			" REPLACE(format(avg(amount/area)*3.33,2), ',', '') as avgAmtArea ";

			$stat_simple = "select year, month, count, amtArea";
			if ($tname != 'aptsale') {
   			$stat_sql .= ", REPLACE(format(avg(amount/landArea)*3.33,2), ',', '') as avgAmtLand ";
				$stat_simple = ", abgAmtLand ";
			}

			$stat_sql .=	" from $tname where amount > 0 and year >= ? AND year <= ?";

			$stat_simple = "select year, month, count, avgDeposit, avgRent from";
			$stat_simple = "from $tname" . "_agg where year >= ? AND year <= ? order by year, month";
		 break;

	default:
		$stat_sql = "select year, month, count(*) as count, ".
			" REPLACE(format(avg(deposit/area)*3.33,2), ',', '') as avgDeposit ";
		$stat_sql .= ", REPLACE(format(avg(monthlyPay/area)*3.33,2), ',', '') as avgRent ";
		$stat_sql .=	" from $tname where year >= ? AND year <= ?";

		$stat_simple = "select year, month, count, avgDeposit, avgRent ";
		$stat_simple = "from $tname" . "_agg where year >= ? AND year <= ? order by year, month";
}

$stat_sql_append = " group by year, month order by year, month ";

echo(processQuery($stat_sql, $stat_sql_append, $stat_sql));

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

  foreach ($_GET as $key=>$val) {
		if ($key=="startyear" || $key=='endyear')
			continue;

		if ($key=='debug') {
			$debug == true;
			continue;
		}

		if ($val=="") {
			continue;
		}

		if ($i++>0) {
			$searchKey+="::";
		}

  	$sql .= " AND " . $key . "=? ";
		$type .= "s";

		// need array element here, since we need a new variable
		$decoded_val[$key] = urldecode($val);
		$searchKey .= urldecode($val);
		$params[] = &$decoded_val[$key];
  }

  $sql .= $sql_append;

  if ($i<=3) {
		$sql = $simple;
		$params = [$searchKey, &$startyear, &$endyear];
		$type ="sii";
	}

//	if($debug) {
 		print_r($params);
		echo ($sql);
		echo ($type);
//	}

	// Persistent Connections
  // http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
  // http://www.php.net/manual/en/mysqli.persistconns.php
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
	// Check connection
	if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
	}

  $stmt = $conn->prepare($sql);
	if (!$stmt) {
		 die ("Prepare $sql failed: ($conn->errno)  $conn->error");
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
  return json_encode($rows,JSON_UNESCAPED_UNICODE);

	$conn->close();
}
?>
