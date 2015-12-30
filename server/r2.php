<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'dnconn.php';

$tname = substr($_SERVER['PATH_INFO'], 1) . "_reg";


if (!$tname) {
  exit(0);
}

$debug = false;

$k = "";
$i = 0;
foreach ($_GET as $key=>$val) {
		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($key=='query') {
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

$conn = DBConn();

$sql = "select v from $tname where k='" . $conn->real_escape_string($k) . "'";

if($debug) {
	echo $sql;
}

$result = $conn->query($sql);

$rows=[];
if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
    $rows[] = $row['v'];
	}
}

// JSON_PRETTY_PRINT|
print json_encode($rows,JSON_UNESCAPED_UNICODE);
$conn->close();
?>
