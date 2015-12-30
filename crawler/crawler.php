<?php

error_reporting(E_ALL);

include_once 'util.php';
include_once 'mkagg.php';


$stateArr = ['서울특별시'=>"11",
    '부산광역시'=>"26",
    '대구광역시'=>"27",
    '인천광역시'=>"28",
    '광주광역시'=>"29",
    '대전광역시'=>"30",
    '울산광역시'=>"31",
    '세종특별자치시'=>"36",
    '경기도'=>"41",
    '강원도'=>"42",
    '충청북도'=>"43",
    '충청남도'=>"44",
    '전라북도'=>"45",
    '전라남도'=>"46",
    '경상북도'=>"47",
    '경상남도'=>"48",
    '제주특별자치도'=>"50"];

$dealType= [
            'houserent'=>['menuGubun'=>'C', 'srhType'=>'LOC', 'houseType' => '2'],
            'officetelsale'=>['menuGubun'=>'E', 'srhType'=>'LOC', 'houseType' => '1'],
            'officetelrent'=>['menuGubun'=>'E', 'srhType'=>'LOC', 'houseType' => '2'],
            'aptlots'=>['menuGubun'=>'F', 'srhType'=>'LOC', 'houseType' => '1'],
            'landsale'=>['menuGubun'=>'G', 'srhType'=>'LOC', 'houseType' => '1'],
            'aptsale'=>['menuGubun'=>'A', 'srhType'=>'LOC', 'houseType' => '1'],
            'aptrent'=>['menuGubun'=>'A', 'srhType'=>'LOC', 'houseType' => '2'],
            'flatsale'=>['menuGubun'=>'B', 'srhType'=>'LOC', 'houseType' => '1'],
            'flatrent'=>['menuGubun'=>'B', 'srhType'=>'LOC', 'houseType' => '2'],
            'housesale'=>['menuGubun'=>'C', 'srhType'=>'LOC', 'houseType' => '1'],
];

crawlNow();

/**
* crawl this month and last months for updates
*/
function crawlNow() {
  date_default_timezone_set("Asia/Seoul");
  $date = date_create();

  // current month
  crawl($date->format('Y'), null, $date->format('m'));

  // last month
  $date->modify('-1 month');
  crawl($date->format('Y'), null, $date->format('m'));
}

// Crawl all upto date
function crawlAll() {
  for ($year = 2006; $year <= 2015; $year++) {
    for ($period=1;$period<=4; $period++) {
      crawl($year, $period, null);
    }
  }
}

function crawl($year, $period, $month) {
   $dealType = $GLOBALS['dealType'];
   $stateArr = $GLOBALS['stateArr'];

  if ($month==null && $period==null) {
    die("monthArr or period must be set!");
  }

  // no month? Then all three in the period
  if ($month==null) {
    $monthArr = [($period*3)-2, ($period*3)-1, ($period*3)];
  } else { // use only one month
    $monthArr = [$month];
  }

  // No period, let's guess from the month
  if ($period==null) {
    $period = intval(($month-1)/3)+1;
  }

  echo ("Working on $year ($period)\n");

  $db = new mysqli("p:localhost", "trend", "only!trend!", "rtrend");
  // Check connection
  if ($db->connect_error) {
      die("Connection failed: " . $db->connect_error);
  }

  // Set utf8
  //$db->set_charset("utf8");

  // get deal type and table name
  foreach ($dealType as $tname => $args) {
    echo ("Getting $tname\n");
    print_r($args);

    foreach ($stateArr as $state => $stateCode) {
      $cities = getCities($year, $period, $stateCode, $args);
      $cityArr = json_decode($cities, true);

      echo ($cities);
      print_r($cityArr);

      foreach ($cityArr['jsonList'] as $city) {
        $counties = getCounties($year, $period, $stateCode, $city['CODE'], $args);
        $countyArr = json_decode($counties, true);

        foreach($countyArr['jsonList'] as $county) {
          $deals = getDeals($year, $period, $stateCode, $city['CODE'], $county['CODE'], $args);
          $dealArr = json_decode($deals, true);

          $r = "";
          $cArr = explode(" ", $county['NAME'], 2);
          if (count($cArr)==2) {
            $r = $cArr[1];
          }

          foreach ($monthArr as $month) {
            echo("Working on $year/$month ($period) on $state " .
                  $city['NAME'] . " " . $cArr[0] . "$r \n");

            $infoArr = ['year'=>$year, 'month'=>$month,
                        'state'=>$state, 'city'=>$city['NAME'],
                        'county'=>$cArr[0], 'region'=>$r];

            // update
            // function update($db, $tname, $metaArr, $json) {
            update($db, $tname, $infoArr, $deals);
          }
        }
      }
    }
  }

  $db->close();
}

function doPost($url, $args) {
  for ($i = 0; $i <= 7; $i++) {
    $out = _doPost($url, $args);
    if ($out!=null) {
      return $out;
    }

    echo ("Post $url nothing. Try again...\n");
  }

  die("Give up on $url\n");
}


function _doPost($url, $args) {

  // create curl resource
  $ch = curl_init();

  // set url
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $url,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36',
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $args
   ]);

  // $output contains the output string
  $output = curl_exec($ch);

  // close curl resource to free up system resources
  curl_close($ch);

  return $output;
}

function getDeals($year, $period, $state, $city, $region, $args) {
    $args =  array_merge($args, [
    'srhYear'=>$year, 'srhPeriod'=>$period, 'gubunCode'=>'LAND',
    'sidoCode'=>$state, 'gugunCode'=>$city, 'dongCode'=>$region,
    'chosung'=>'', 'roadCode'=>'', 'danjiCode' => '', 'rentAmtType' =>'3',
    'fromAmt1'=>'', 'toAmt1'=>'', 'fromAmt2'=>'', 'toAmt2'=>'', 'fromAmt3'=>'',
    'toAmt3'=>'', 'areaCode'=>'', 'jimokCode'=>'', 'useCode'=>'', 'useSubCode'=>''
    ]);

  return doPost('http://rt.molit.go.kr/srh/getListAjax.do', $args);
}

function getCounties($year, $period, $state, $city, $args) {

  $args = array_merge($args, [
    'srhYear'=>$year,
    'srhPeriod'=>$period,
    'gubunCode'=>'LAND',
    'sidoCode'=>$state,
    'gugunCode'=>$city]);

  return doPost('http://rt.molit.go.kr/srh/getDongListAjax.do', $args);
}

function getCities($year, $period, $state, $args) {
  $args = array_merge($args, [
    'srhYear'=>$year,
    'srhPeriod'=>$period,
    'gubunCode'=>'LAND',
   'sidoCode'=>$state]);

  print_r($args);

  return doPost('http://rt.molit.go.kr/srh/getGugunListAjax.do', $args);
}


?>