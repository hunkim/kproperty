<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$stat_sql = "select year, month, count(*) as c, format(avg(amount),2) as avgAmount, ".
	" REPLACE(format(avg(amount/area)*3.30579,2), ',', '') as avgAmtArea, " .
        " REPLACE(format(avg(amount/landArea)*3.30579,2), ',', '') as avgAmtLand ".
	" from Sale where amount > 0 and year >= ? AND year <= ?"; 

$stat_sql_append = " group by month, year order by year, month, date";

// Basic Sale SQL
$sale_sql = "SELECT * FROM Sale where year >= ? AND year <= ?";
$sale_sql_append = " order by year desc, month desc, date desc limit 500";

if ($_SERVER['PATH_INFO']=="/stat") {
  echo(processQuery($stat_sql, $stat_sql_append));
} else {
  echo(processQuery($sale_sql, $sale_sql_append));
}

/**
*/
function processQuery($sql, $sql_append) {
  // Persistent Connections
  // http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
  // http://www.php.net/manual/en/mysqli.persistconns.php
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");

  $startyear = intval($_GET['startyear']);
  $endyear = intval($_GET['endyear']);

  if ($endyear ==0) $endyear = 3000;

  $key_array = array('city', 'county', 'region', 'region1');
  $params = array(&$startyear, &$endyear);
  $type = "ii";

  foreach ($key_array as $key) {
    if ($val = $_GET[$key]) {
	$sql .= " AND " . $key . "=? ";
	$type .= "s";
	// need array element here, since we need a new variable
	$decoded_val[$key] = urldecode($val);
	$params[] = &$decoded_val[$key];
    }
  }

  $sql .= $sql_append;
  $stmt = $conn->prepare($sql);

  // http://stackoverflow.com/questions/16236395/bind-param-with-array-of-parameters
  call_user_func_array(array($stmt, "bind_param"), array_merge(array($type), $params));

  $stmt->execute();
  $result = $stmt->get_result();

  $rows=array();
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  $conn->close();

  return json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
}
?>