<?php

// connect
$m = new MongoClient();

// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

foreach ($colnames as $colname) {
  mkgrp($db, $colname);
}

function mkgrp($db, $colname) {
$col2name = $colname."_agg";
echo("working on: $col2name ...");

$collection = new MongoCollection($db, $colname);

$query = [];

$grouparr['count'] = ['$sum' =>  1];

switch ($colname) {
case 'aptsale':
case 'flatsale':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAmtUsed'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];

  $grouparr['_id'] = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
    'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
    'area'=>'$area'
  ];

  break;

case 'housesale':
  $query['area'] = ['$gt' => 0] ;
  $query['landArea'] = ['$gt' => 0] ;
  $grouparr['avgAmtArea'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
  $grouparr['avgAmtLand'] = ['$avg' => ['$divide' => [ '$amount', '$landArea' ] ] ];

  $grouparr['_id'] = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
    'city'=>'$city', 'county'=>'$county', 'region'=>'$region'
  ];
  break;

case 'aptrent':
case 'flatrent':
  $query['usedArea'] = ['$gt' => 0] ;
  $grouparr['avgAptRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ]];
  $grouparr['avgAptDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];


  $grouparr['_id'] = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
    'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
    'area'=>'$area', 'monthlyType' => '$monthlyType'
  ];
  break;

case 'houserent':
  $query['contractArea'] = ['$gt' => 0] ;
  $grouparr['avgHouseDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
  $grouparr['avgHouseRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ] ];

   $grouparr['_id'] = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
    'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 
    'monthlyType' => '$monthlyType'
  ];

  break;
}

$ops = array();
$ops[] = ['$match' => $query];
$ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
$ops[] = ['$group' => $grouparr];

$option = ['allowDiskUse' => true];

print_r($ops);

try {
 $cursor = $collection->aggregate($ops, $option);
} catch (MongoException $e) {
  echo "error message: ".$e->getMessage()."\n";
  echo "error code: ".$e->getCode()."\n";
  exit(1);
}

/*
[1243] => Array
                (
                    [_id] => Array
                        (
                            [year] => 2006
                            [month] => 3
                            [state] => 경상북도
                            [city] => 영주시
                            [county] => 봉현면
                            [region] => 오현리
                        )

                    [count] => 1
                    [avgAmtArea] => 62.351914203766
                    [avgAmtLand] => 14.409221902017
                )
*/
//echo json_encode(iterator_to_array($cursor), 	
$col2 = new MongoCollection($db, $col2name);

// Let's remove all first
$col2->remove([]);

$results = $cursor['result'];

foreach ($results as $key => $val) {
  foreach($val as $skey=> $sval) {
    if ($skey == '_id') {
      $r = $sval;
    } else {
      $r[$skey] = $sval;
    }
  }
  //print_r($r);
  $col2->insert($r);
}

//  print_r($result);


//echo json_encode($cursor, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
}
?>
