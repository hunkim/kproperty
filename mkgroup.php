<?php

// connect
$m = new MongoClient();
// select a database
$db = $m->selectDB('trend');

function testgrp() {
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
}

function mkgrp($db, $colname, $year, $month) {
  $query = ['year'=>$year, 'month'=>$month];
  $query['area'] = ['$gt' => 0] ;

  $grouparr['count'] = ['$sum' =>  1];

  switch ($colname) {
    case 'aptsale':
    case 'flatsale':
      $grouparr['avgAmtArea'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];

      $groupkey = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];

      // insert with empty
      mkonegrp($db, $colname, $query, $grouparr);
      foreach ($groupkey as $key => $value) {
        $grouparr['_id'][$key] = $value;
        mkonegrp($db, $colname, $query, $grouparr);
      }
    break;

    case 'housesale':
      $query['landArea'] = ['$gt' => 0] ;
      $grouparr['avgAmtArea'] = ['$avg' => ['$divide' => [ '$amount', '$area' ] ] ];
      $grouparr['avgAmtLand'] = ['$avg' => ['$divide' => [ '$amount', '$landArea' ] ] ];

      $groupkey = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];
      // insert with empty
      mkonegrp($db, $colname, $query, $grouparr);
      foreach ($groupkey as $key => $value) {
        $grouparr['_id'][$key] = $value;
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;

case 'aptrent':
case 'flatrent':
      $grouparr['avgRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ]];
      $grouparr['avgDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];

      $groupkey = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];

      // insert with empty
      $grouparr['_id']['monthlyType'] = '$monthlyType';
      mkonegrp($db, $colname, $query, $grouparr);
      unset($grouparr['_id']['monthlyType']);
      mkonegrp($db, $colname, $query, $grouparr);

      foreach ($groupkey as $key => $value) {
        $grouparr['_id'][$key] = $value;

        $grouparr['_id']['monthlyType'] = '$monthlyType';
        mkonegrp($db, $colname, $query, $grouparr);
        unset($grouparr['_id']['monthlyType']);
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;

case 'houserent':
      $grouparr['avgDeposit'] = ['$avg' => ['$divide' => [ '$deposit', '$area' ] ] ];
      $grouparr['avgRent'] = ['$avg' => ['$divide' => [ '$monthlyPay', '$area' ] ] ];

      $groupkey= ['state'=>'$state','city'=>'$city', 'county'=>'$county', 'region'=>'$region'];

      $grouparr['_id'] = ['year' => '$year', 'month'=>'$month'];

      $grouparr['_id']['monthlyType'] = '$monthlyType';
      mkonegrp($db, $colname, $query, $grouparr);
      unset($grouparr['_id']['monthlyType']);
      mkonegrp($db, $colname, $query, $grouparr);

      foreach ($groupkey as $key => $value) {
        $grouparr['_id'][$key] = $value;

        $grouparr['_id']['monthlyType'] = '$monthlyType';
        mkonegrp($db, $colname, $query, $grouparr);
        unset($grouparr['_id']['monthlyType']);
        mkonegrp($db, $colname, $query, $grouparr);
      }
      break;
  }
}

function makegrpIndex($db, $collection, $ids) {
    $dbData = array();
    foreach ($ids as $i => $field) {
      $dbData[$i] = 1;
    }

    // Insert it to DB
    //$r = $collection->createIndex($dbData, ['name'=> 'all']);
    $r = $collection->createIndex($dbData);
    echo $r;
}

function mkonegrp($db, $colname, $query, $grouparr) {
  $ops = array();
  $ops[] = ['$match' => $query];
//  $ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
  $ops[] = ['$group' => $grouparr];

  $option = ['allowDiskUse' => true];

  //print_r($ops);

  $collection = new MongoCollection($db, $colname);
  echo("working on: $colname ... with");
  print_r($grouparr['_id']);
  makegrpIndex($db, $collection, $grouparr['_id']);

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
