<?php
error_reporting(E_ALL);
MongoCursor->timeout(-1);

testreg();

function testreg() {
  // connect
  $m = new MongoClient();
  // select a database
  $db = $m->selectDB('trend');

  // select a collection (analogous to a relational database's table)
  $colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];

  foreach ($colnames as $colname) {
    $col2name = $colname."_reg";
    $col2 = new MongoCollection($db, $col2name);

    // Let's remove all first
    $col2->drop([]);

    // add agg information
    mkreg($db, $colname);
  }
}

function mkreg($db, $colname) {
  switch ($colname) {
    case 'aptsale':
    case 'flatsale':
      $grouparr['_id'] = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      mkonereg($db, $colname, $grouparr);
    break;

    case 'housesale':

      $grouparr['_id'] = ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region'];

      mkonereg($db, $colname, $grouparr);
      break;

case 'aptrent':
case 'flatrent':
      $grouparr['_id']= ['state'=>'$state',
        'city'=>'$city', 'county'=>'$county', 'region'=>'$region', 'aptName'=>'$aptName',
        'area'=>'$area'];

      mkonereg($db, $colname, $grouparr);
      break;

case 'houserent':
      $grouparr['_id'] = ['state'=>'$state','city'=>'$city', 'county'=>'$county', 'region'=>'$region'];
      mkonereg($db, $colname, $grouparr);
      break;
  }
}

function makegrpIndex($db, $collection, $ids) {
    $dbData = array();
    foreach ($ids as $i => $field) {
      $dbData[$i] = 1;
      // Insert it to DB
      //$r = $collection->createIndex($dbData, ['name'=> 'all']);
      $r = $collection->createIndex($dbData);
      print_r($r);
    }


}

function mkonereg($db, $colname, $grouparr) {
  $ops = array();
//  $ops[] = ['$match' => $query];
//  $ops[] = ['$sort' => ['year'=> -1, 'month'=> -1]];
  $ops[] = ['$group' => $grouparr];

  $option = ['allowDiskUse' => true];

  //print_r($ops);

  $collection = new MongoCollection($db, $colname);
  echo("working on: $colname ... with");

  $col2name = $colname."_reg";
  $col2 = new MongoCollection($db, $col2name);

  print_r($grouparr['_id']);
  makegrpIndex($db, $col2, $grouparr['_id']);

  //print_r($ops);
  try {
    $cursor = $collection->aggregateCursor($ops, $option);
  } catch (MongoException $e) {
    echo "error message: ".$e->getMessage()."\n";
    echo "error code: ".$e->getCode()."\n";
    exit(1);
  }



  //$results = $cursor['result'];
 foreach ($cursor as $result) {
   //print_r($result[_id]);
   $col2->insert($result['_id']);
  }
}
?>
