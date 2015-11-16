<?php

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colname = 'housesale';

mkgrp($db, $colname);


function mkgrp($db, $colname) {
$collection = new MongoCollection($db, $colname);

$query = [];

$grouparr['_id'] = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
'area'=>'$area',
'monthType'=>'$monthType'
 ];

$grouparr['count'] = ['$sum' =>  1];

switch ($colname) {
case 'aptsale':
case 'flatsale':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAmtUsed'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
  break;

case 'housesale':
  $query['area'] = ['$gt' => 0] ;
  $query['landArea'] = ['$gt' => 0] ;
  $grouparr['avgAmtArea'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
  $grouparr['avgAmtLand'] = ['$avg' => ['$divide' => [ '$amount', '$landArea' ] ] ];
  break;

case 'aptrent':
case 'flatrent':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAptRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ]];
  $grouparr['avgAptDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
  break;

case 'flatrent':
  $query['contractArea'] = ['$gt' => 0] ;
  $grouparr['avgHouseDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
  $grouparr['avgHouseRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ] ];
  break;
}

$ops = array();
$ops[] = ['$match' => $query];
$ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
$ops[] = ['$group' => $grouparr];

$option = [allowDiskUse => true];

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
}
?>
