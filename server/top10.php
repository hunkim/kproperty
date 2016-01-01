<?php

include_once 'dbconn.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

$conn = DBconn();

$debug = false;

$delta = false;

$preYear=2014;
$year=2015;

//state='?' and city='?' and county='?'
$q = "";
foreach ($_GET as $key=>$val) {
		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($key=='delta') {
			$delta = true;
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

		$q.=" and $key='" . $conn->real_escape_string(urldecode($val)) . "'";
}


if ($delta) {
  if($tname=='aptrent' || $tname=='flatrent' || $tname=='officetelrent') {
		$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county, v1.aptName) as label,";
		//$sql .= "v1.year as year1, v1.a as avg1, v2.year as year2, v2.a as avg2, ";
		$sql .= " replace(format((v2.a-v1.a),2),',', '') as value from ";
		$sql .= "(select avg(amount/area) as a, state, city, county, aptName, year from $tname where amount>0 and monthlyPay=0 and  year = $preYear $q group by state, city, county, aptName) v1,";
		$sql .= "(select avg(amount/area) as a, state, city, county, aptName, year from $tname where amount>0 and monthlyPay=0 and  year = $year $q group by state, city, county, aptName) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county and v1.aptName=v2.aptName order by (v2.a-v1.a) desc;";
	} else if($tname=='landsale' || $tname=='housesale') { // sale
		$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county) as label,";
		$sql .= " replace(format((v2.a-v1.a),2),',', '') as value from ";
		$sql .= "(select avg(amount/area) as a, state, city, county, year from $tname where amount>0 and year = $preYear $q group by state, city) v1,";
		$sql .= "(select avg(amount/area) as a, state, city, county, year from $tname where amount>0 and year = $year $q group by state, city) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county order by (v2.a-v1.a) desc;";
	} else { // sale
		$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county, v1.aptName) as label,";
		//$sql .= "v1.year as year1, v1.a as avg1, v2.year as year2, v2.a as avg2, ";
		$sql .= " replace(format((v2.a-v1.a),2),',', '') as value from ";
		$sql .= "(select avg(amount/area) as a, state, city, county, aptName, year from $tname where amount>0 and year = $preYear $q group by state, city, county, aptName) v1,";
		$sql .= "(select avg(amount/area) as a, state, city, county, aptName, year from $tname where amount>0 and year = $year $q group by state, city, county, aptName) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county and v1.aptName=v2.aptName order by (v2.a-v1.a) desc;";
	}
} else { // query
	if($tname=='aptrent' || $tname=='flatrent' || $tname=='officetelrent') {
		$sql =  "select CONCAT_WS(' ', state, city, county, aptName) as label, replace(format(avg(amount/area),2),',', '') as value, avg(amount/area) as x from $tname";
	  $sql .= " where amount>0 and year = $year $q ";
		$sql .= " group by state, city, county, aptName ";
		$sql .= " order by x desc limit 20;";
	} else if($tname=='landsale' || $tname=='housesale') {
		$sql =  "select CONCAT_WS(' ', state, city, county) as label, replace(format(avg(amount/area),2),',', '') as value, avg(amount/area) as x from $tname";
	    $sql .= " where amount>0 and year = $year $q ";
		$sql .= " group by state, city";
		$sql .= " order by x desc limit 20;";
	} else { // sale
		$sql = "select CONCAT_WS(' ', state, city, county, aptName) as label, replace(format(avg(amount/area),2),',', '') as value, avg(amount/area) as x from $tname";
		$sql .= " where amount>0 and year = $year $q ";
		$sql .= " group by state, city, county, aptName ";
		$sql .= " order by x desc limit 20;";
	}
}


if($debug) {
	echo $sql;
}

$result = $conn->query($sql);
if (!$result) {
	 echo ("Quwey $sql failed: ($conn->errno)  $conn->error");
	 exit(0);
}

if ($result->num_rows > 0) {
	// output data of each row
	$i = 0;
	while($row = $result->fetch_assoc()) {
		// select first 10 and last 10
		if ($i++ < 10) {$rows[] = $row;}
		if ($i> ($result->num_rows-10)) {$rows[] = $row;}
	}
}

$result = [];
foreach ($rows as $key => $val) {
	$color = 'red';
	if ($val['value'] < 0) {
		$color = 'blue';
	}
	$arr = ['c'=>[['v'=>$val['label']], ['v'=>$val['value']], ['v'=>$color]]];
	$result [] = $arr;
}

// JSON_PRETTY_PRINT|
print json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
$conn->close();
?>
