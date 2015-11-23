<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Get app name
$tname = substr($_SERVER['PATH_INFO'], 1);

// Basic information SQL
$sale_sql = "SELECT * FROM $tname where year >= ? AND year <= ? ";
$sale_sql_append = " order by year desc, month desc limit 500";

// process and print
processQuery($sale_sql, $sale_sql_append);

/**
* Main function
*/
function processQuery($sql, $sql_append) {
  $startyear = intval($_GET['startyear']);
  $endyear = intval($_GET['endyear']);

	// No end year, give it enough
  if ($endyear ==0) $endyear = 3000;

	// make array and type
  $params = [&$startyear, &$endyear];
  $type = "ii";

	$debug = false;
	foreach ($_GET as $key=>$val) {
		if ($key=="startyear" || $key=='endyear') {
			continue;
		}

		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($val=="") {
			continue;
		}

  	$sql .= " AND " . $key . "=? ";
		$type .= "s";

		// need array element here, since we need a reference
		$decoded_val[$key] = urldecode($val);
		$params[] = &$decoded_val[$key];
  }

	// add the last part
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

	// JSON_PRETTY_PRINT|
  //http://php.net/manual/de/function.gzencode.php
  print gzencode(json_encode($rows,JSON_UNESCAPED_UNICODE));

	$conn->close();
}
?>
