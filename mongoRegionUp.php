#!/usr/bin/php
<?php
include 'classDBConnect.php';

// connect
$m = new MongoClient();

// select a database
$db = $m->trend;

// select a collection (analogous to a relational database's table)
$collection = $db->regions;

$mdb = new DBConnect;
$mdb->connect();
$result = $mdb->conn->query("select DISTINCT city, county, region, region1, region2 from Sale");

while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
	$collection->insert($rs);
}

$mdb->close();

// find everything in the collection
$cursor = $collection->find();

// iterate through the results
foreach ($cursor as $d) {
    echo $d["city"] . $d["county"]. $d["region"] . $d["region1"] . $d["region2"].   "\n";
}

echo json_encode(iterator_to_array($cursor));

?>
