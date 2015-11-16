<?php

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');
// select a collection (analogous to a relational database's table)
$colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

foreach ($colnames as $colname) {
  $col2name = $colname."_agg";
  $col2 = new MongoCollection($db, $col2name);

  // Let's remove all first
  $col2->remove([]);

  // add agg information
  mkgrp($db, $colname);
}

function mkgrp($db, $colname) {
  $query = [];

  $grouparr['count'] = ['$sum' =>  1];

  switch ($colname) {
    case 'aptsale':
    case 'flatsale':
      $query['usedArea'] = ['$gt' => 0] ;
      $grouparr['avgAmtUsed'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];

      $groupkey = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];
  
      // insert with empty
      mkonegrp($db, $colname, $query, $grouparr);
      foreach ($groupkey as $key => $value) {
        $grouparr['_id']['$key'] = $value;
        mkonegrp($db, $colname, $query, $grouparr);
      }
    break;

    case 'housesale':
      $query['area'] = ['$gt' => 0] ;
      $query['landArea'] = ['$gt' => 0] ;
      $grouparr['avgAmtArea'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
      $grouparr['avgAmtLand'] = ['$avg' => ['$divide' => [ '$amount', '$landArea' ] ] ];

      $groupkey = ['year' => '$year', 'month'=>'$month', 'state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];
      // insert with empty
      mkonegrp($db, $colname, $query, $grouparr);
      foreach ($groupkey as $key => $value) {
        $grouparr['_id']['$key'] = $value;
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;

case 'aptrent':
case 'flatrent':
      $query['usedArea'] = ['$gt' => 0] ;
      $grouparr['avgAptRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ]];
      $grouparr['avgAptDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];

      $groupkey = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];
      
      // insert with empty
      $grouparr['id']['monthlyType'] = '$monthlyType';
      mkonegrp($db, $colname, $query, $grouparr);
      unset($grouparr['id']['monthlyType']);
      mkonegrp($db, $colname, $query, $grouparr);
      
      foreach ($groupkey as $key => $value) {
        $grouparr['_id']['$key'] = $value;

        $grouparr['id']['monthlyType'] = '$monthlyType';
        mkonegrp($db, $colname, $query, $grouparr);
        unset($grouparr['id']['monthlyType']);
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;

case 'houserent':
      $query['contractArea'] = ['$gt' => 0] ;
      $grouparr['avgHouseDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
      $grouparr['avgHouseRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ] ];

      $groupkey= ['state'=>'$state','city'=>'$city', 'county'=>'$county', 'region'=>'$region'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];

      $grouparr['id']['monthlyType'] = '$monthlyType';
      mkonegrp($db, $colname, $query, $grouparr);
      unset($grouparr['id']['monthlyType']);
      mkonegrp($db, $colname, $query, $grouparr);
      
      foreach ($groupkey as $key => $value) {
        $grouparr['_id']['$key'] = $value;

        $grouparr['id']['monthlyType'] = '$monthlyType';
        mkonegrp($db, $colname, $query, $grouparr);
        unset($grouparr['id']['monthlyType']);
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;
  }
}

function mkonegrp($db, $colname, $query, $grouparr) {
  $ops = array();
  $ops[] = ['$match' => $query];
  $ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
  $ops[] = ['$group' => $grouparr];

  $option = ['allowDiskUse' => true];

  print_r($ops);

  $collection = new MongoCollection($db, $colname);
  echo("working on: $col2name ... with");
  print_r($grouparr['_id']);  

  try {
    $cursor = $collection->aggregate($ops, $option);
  } catch (MongoException $e) {
    echo "error message: ".$e->getMessage()."\n";
    echo "error code: ".$e->getCode()."\n";
    exit(1);
  }

  $col2name = $colname."_agg";
  $col2 = new MongoCollection($db, $col2name);

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
}
?>
