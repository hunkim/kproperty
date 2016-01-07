<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'dbconn.php';


$conn = DBConn();

$limit = "";

if (isset($_GET['limit'])) {
	$limit = "limit " . $_GET['limit']; 
}

$sql = "select type, loc, count(*) as c from log group by type, loc order by c desc $limit";

$result = $conn->query($sql);

$rows=[];
if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
    $rows[] = $row;
	}
}

// 
print json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

$conn->close();

?>
