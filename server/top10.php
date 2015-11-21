<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

$debug = false;

$k = "";

$preYear=2014;
$year=2015;

//state='?' and city='?' and county='?'
$q = "";
foreach ($_GET as $key=>$val) {
		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($key=='query') {
			continue;
		}

		if ($key=='year') {
			$year = intval($val);
			$preYear = intval($val)-1;
			continue;
		}

		if ($val=="") {
			continue;
		}

		$q.=" and $key='" . urldecode($val) . "'";
}


// Persistent Connections
// http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
// http://www.php.net/manual/en/mysqli.persistconns.php
$conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
// Check connection
if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county) as label,";
//$sql .= "v1.year as year1, v1.a as avg1, v2.year as year2, v2.a as avg2, ";
$sql .= " v2.a-v1.a as value from ";
$sql .= "(select avg(amount/area) as a, state, city, county, year from $tname where year = $preYear $q group by state, city, county) v1,";
$sql .= "(select avg(amount/area) as a, state, city, county, year from $tname where year = $year $q group by state, city, county) v2 ";
$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county order by value desc limit 10;";


if($debug) {
	echo $sql;
}

$result = $conn->query($sql);

$rows=[];
if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
    $rows[] = $row;
	}
}

$result = ["key"=>"Cumulative Return", "values" => $rows];

//print_r($rows);

// JSON_PRETTY_PRINT|
print json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
$conn->close();
?>
