<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colname = str_replace( '/', '',trim($_SERVER['PATH_INFO']));
if (!$colname) {
  $debug = true;
  $colname = 'housesale';
}

$collection = new MongoCollection($db, $colname . "_agg");

//$query = ['amount'=>['$gt' => 0]] ;
// TODO: later need to handle key
$query = [] ;
foreach ($_GET as $key => $value) {
  //echo "Key: $key; Value: $value<br />\n";
  if ($value == '') {
    continue;
  }

  if ($key == 'debug') {
    $debug = true;
    continue;
  }

  if ($key == 'startyear') {
    $endyear = $_GET['endyear'];
    $query['year'] = ['$gte' => intval($value), '$lte' => intval($endyear)];
  } else if ($key == 'endyear') {
    //
  } else {
    $query[$key] = urldecode($value);
  }
}

//$query = ['amount'=>['$gt' => 0]] ;
//print_r($query);

/*
db.housesale.aggregate(
   [
   { $match: { 'state': '서울특별시', year: { $gt: 2000 } } },
      {
        $group : {
           _id : { "state": "$state", "city":"$city"},
          
           avgAmtArea: { $avg: { $divide: [ "$amount", "$area" ] } },
           avgAmtLand: { $avg: { $divide: [ "$amount", "$landArea" ] } },
          
           avgAmt: { $avg: "$amount" },
           count: { $sum: 1 }
        }
      }
   ]
)
*/

//$query = ['state'=> '서울특별시'];

$grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];
$grouparr['count'] = ['$sum' =>  '$count'];

switch ($colname) {
case 'aptsale':
case 'flatsale':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAmtUsed'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
  break;

case 'housesale':
  $grouparr['avgAmtArea'] = ['$avg' =>  '$avgAmtArea' ] ;
  $grouparr['avgAmtLand'] = ['$avg' =>  '$avgAmtLand' ] ;
  break;

case 'aptrent':
case 'flatrent':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAptRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ]];
  $grouparr['avgAptDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
  break;

case 'houserent':
  $query['contractArea'] = ['$gt' => 0] ;
  $grouparr['avgHouseDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
  $grouparr['avgHouseRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ] ];
  break;
}

$ops = array();
$ops[] = ['$match' => $query];
$ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
$ops[] = ['$group' => $grouparr];

$option = ['allowDiskUse' => true];

if($debug) {
  print_r($ops);
}

try {
 $cursor = $collection->aggregate($ops, $option);
} catch (MongoException $e) {
  echo "error message: ".$e->getMessage()."\n";
  echo "error code: ".$e->getCode()."\n";
  exit(1);
}

//echo json_encode(iterator_to_array($cursor), 	
echo json_encode($cursor, 	
	JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
?>
