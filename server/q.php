<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

$stat_sql = "select year, month, count(*) as count, ".
	" REPLACE(format(avg(amount/area)*3.33,2), ',', '') as avgAmtArea " .
    //    " REPLACE(format(avg(amount/landArea)*3.33,2), ',', '') as avgAmtLand ".
	" from $tname where amount > 0 and year >= ? AND year <= ?";

$stat_sql_append = " group by month, year order by year, month ";

// Basic Sale SQL
$sale_sql = "SELECT * FROM $tname where year >= ? AND year <= ?";
$sale_sql_append = " order by year desc, month desc limit 500";

$debug = false;

if ($_SERVER['SCRIPT_NAME']=="/s.php") {
  echo(processQuery($stat_sql, $stat_sql_append));
} else {
  echo(processQuery($sale_sql, $sale_sql_append));
}

/**
*/
function processQuery($sql, $sql_append) {
  $startyear = intval($_GET['startyear']);
  $endyear = intval($_GET['endyear']);

  if ($endyear ==0) $endyear = 3000;

  $params = array(&$startyear, &$endyear);
  $type = "ii";

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

  	$sql .= " AND " . $key . "=? ";
		$type .= "s";

		// need array element here, since we need a new variable
		$decoded_val[$key] = urldecode($val);
		$params[] = &$decoded_val[$key];
  }

  $sql .= $sql_append;


if($debug) {
 	print_r($params);
	echo ($sql);
	echo ($type);
}

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
  $conn->close();

  return json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
}
?>
