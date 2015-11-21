<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

$debug = false;

$k = "";
$i=0;
foreach ($_GET as $key=>$val) {
		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($key=='query') {
			continue;
		}

		if ($val=="") {
			continue;
		}

		if ($i++ !== 0) {
			$k.="::";
		}
		$k.=urldecode($val);
}


if($debug) {
	echo ("Query: $k");
}

// Persistent Connections
// http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
// http://www.php.net/manual/en/mysqli.persistconns.php
$conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
// Check connection
if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

$sql = "select v1.state, v1.city, v1.county, v1.year, v1.a, v2.year, v2.a, v2.a-v1.a as d from
(select avg(amount/area) as a, year,  state, city, county from $tname where year = 2006 group by state, city, county) v1,
(select avg(amount/area) as a, year,  state, city, county from $tname where year = 2007 group by state, city, county) v2
where v1.city=v2.city and v1.county=v2.county and v1.state=v2.state order by d desc;";

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

//print_r($rows);

// JSON_PRETTY_PRINT|
print json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
$conn->close();
?>
