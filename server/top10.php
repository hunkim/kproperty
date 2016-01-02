<?php

include_once 'dbconn.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tname = substr($_SERVER['PATH_INFO'], 1);

$conn = DBconn();

$debug = false;

$delta = false;
$monthly = false;

$preYear=2014;
$year=2015;

//state='?' and city='?' and county='?'
$q = "";
foreach ($_GET as $key=>$val) {
		if ($key=='debug') {
			$debug = true;
			continue;
		}

		if ($key=='monthly') {
			$monthly = true;
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
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, aptName, year from $tname where amount>0 and monthlyPay=0 and  year = $preYear $q group by state, city, county, aptName) v1,";
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, aptName, year from $tname where amount>0 and monthlyPay=0 and  year = $year $q group by state, city, county, aptName) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county and v1.aptName=v2.aptName order by (v2.a-v1.a) desc;";
	} else if($tname=='landsale' || $tname=='housesale') { // sale
		$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county) as label,";
		$sql .= " replace(format((v2.a-v1.a),2),',', '') as value from ";
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, year from $tname where amount>0 and year = $preYear $q group by state, city, county) v1,";
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, year from $tname where amount>0 and year = $year $q group by state, city, county) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county order by (v2.a-v1.a) desc;";
	} else { // sale
		$sql = "select CONCAT_WS(' ', v1.state, v1.city, v1.county, v1.aptName) as label,";
		//$sql .= "v1.year as year1, v1.a as avg1, v2.year as year2, v2.a as avg2, ";
		$sql .= " replace(format((v2.a-v1.a),2),',', '') as value from ";
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, aptName, year from $tname where amount>0 and year = $preYear $q group by state, city, county, aptName) v1,";
		$sql .= "(select avg(amount/area)*3.30579 as a, state, city, county, aptName, year from $tname where amount>0 and year = $year $q group by state, city, county, aptName) v2 ";
		$sql .= "where v1.state=v2.state and v1.city=v2.city and v1.county=v2.county and v1.aptName=v2.aptName order by (v2.a-v1.a) desc;";
	}
} else if ($monthly) {
	$sql = "select month as label, replace(format(avg(amount/area)*3.30579,2),',', '') as value from $tname where year = $year $q group by month order by month";
} else { // query
	if($tname=='aptrent' || $tname=='flatrent' || $tname=='officetelrent') {
		$sql =  "select CONCAT_WS(' ', state, city, county, aptName) as label, replace(format(avg(amount/area)*3.30579,2),',', '') as value, avg(amount/area)*3.30579 as x from $tname";
	    $sql .= " where amount>0 and year = $year $q ";
		$sql .= " group by state, city, county, aptName ";
		$sql .= " order by x desc limit 20;";
	} else if($tname=='landsale' || $tname=='housesale') {
		$sql =  "select CONCAT_WS(' ', state, city, county) as label, replace(format(avg(amount/area)*3.30579,2),',', '') as value, avg(amount/area)*3.30579 as x from $tname";
	    $sql .= " where amount>0 and year = $year $q ";
		$sql .= " group by state, city, county";
		$sql .= " order by x desc limit 20;";
	} else { // sale
		$sql = "select CONCAT_WS(' ', state, city, county, aptName) as label, replace(format(avg(amount/area)*3.30579,2),',', '') as value, avg(amount/area)*3.30579 as x from $tname";
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


if($debug) {
	echo "got " . $result->num_rows . "results!";
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
	if ($delta) {
		// http://bootstrapbay.com/blog/wp-content/uploads/2014/05/bootflat_sample_r8sbud.png
		$color = '#ED5565';
		if ($val['value'] < 0) {
			$color = '#5D9CEC';
		}
	} else {
		$color = rand_color();
	}

	$arr = ['c'=>[['v'=>$val['label']], ['v'=>$val['value']], ['v'=>$color]]];
	$result [] = $arr;
}

// JSON_PRETTY_PRINT|
print json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
$conn->close();


// http://stackoverflow.com/questions/5614530/generating-a-random-hex-color-code-with-php
function rand_color() {
	$color = ['#5D(CEC', '#4FC11E9', '#4A89DC', '#3BAFDA', '#48CFAD', '#37BC9B', '#A0D468', 
		'#8CC152', '#ED5565', '#DA4453', '#AC92EC', '#967ADC', '#EC87C0', '#D770AD', 
		'#FFCE54', '#F6BB42', '#FC6E51', '#E9573F', '#E6E9ED', '#CCD1D9', '#AAB2BD', '#656d78'];

    return $color[rand(0, count($color)-1)];
}
?>
