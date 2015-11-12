#!/usr/bin/php
<?php
include 'classDBConnect.php';

// connect
$m = new MongoClient();

// select a database
$db = $m->trend;

// select a collection (analogous to a relational database's table)
$collection = $db->regions;

insert($db->houseSale, 
	"select DISTINCT city, county, region, region1, region2 from HouseSale");
insert($db->houseRent, 
	"select DISTINCT city, county, region, region1, region2 from HouseRent");
insert($db->APTSale, 
	"select DISTINCT city, county, region, region1, region2, buildName from APTSale");
insert($db->APTRent, 
	"select DISTINCT city, county, region, region1, region2, buildName from APTRent");
insert($db->FlatSale, 
	"select DISTINCT city, county, region, region1, region2, buildName from FlatSale");
insert($db->FlatRent, 
	"select DISTINCT city, county, region, region1, region2, buildName from FlatRent");

function insert($collection, $sql) {
	$mdb = new DBConnect;
	$mdb->connect();
	$result = $mdb->conn->query($sql);

	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		$collection->insert($rs);
	}

	$mdb->close();

	// find everything in the collection
	$cursor = $collection->find();

	// iterate through the results
	/*
	foreach ($cursor as $d) {
		echo $d["city"] . $d["county"]. $d["region"] . $d["region1"] . $d["region2"].   "\n";
	}
	*/
	echo json_encode(iterator_to_array($cursor));
}
?>
