<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1) . "_reg";

$debug = false;

$k = "";
$i=0;
foreach ($_GET as $key=>$val) {
		if ($i++ !== 0) {
			$k.="::";
		}
		if ($key=='debug') {
			$debug == true;
			continue;
		}

		if ($key=='query') {
			continue;
		}

		if ($val=="") {
			continue;
		}


		$k.=urldecode($val);
}


if($debug) {
	echo ($k);
}

// Persistent Connections
// http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
// http://www.php.net/manual/en/mysqli.persistconns.php
$conn = new mysqli("p:localhost", "trend", "only!trend!", "trend");
// Check connection
if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

$sql = "select v from $tname where k='" . $conn->real_escape_string($k) . "'";
//$sql = "select v from $tname";
//echo $sql;
$result = $conn->query($sql);

$rows=[];
if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
    $rows[] = $row['v'];
	}
}

//print_r($rows);

// JSON_PRETTY_PRINT|
print json_encode($rows,JSON_UNESCAPED_UNICODE);
$conn->close();
?>
