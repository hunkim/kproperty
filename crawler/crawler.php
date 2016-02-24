<?php

error_reporting(E_ALL);

include_once 'const.php';
include_once 'util.php';
include_once 'mkagg.php';

if ($argc!=2) {
  die ($argv[0] . " <tname>\n");
}

test();

function test() {

  $url = "http://openapi.molit.go.kr:8081/OpenAPI_ToolInstallPackage/service/rest/RTMSOBJSvc/getRTMSDataSvcAptTrade".
          "?type=json&LAWD_CD=" . $regionCode . "&DEAL_YMD=" . $year . $month . "&serviceKey=" . SKEY;

  $resp =  file_get_contents($url);
  if ($resp === FALSE) {
      echo($http_response_header[0]);
      return;
  }
  //$obj = json_decode($json);  
  //$xml = simplexml_load_string($resp);
  exit();
}

$stateArr = [
    '제주특별자치도'=>"50",
    '서울특별시'=>"11",
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
    ];

$dealType= [
           'landsale'=>['menuGubun'=>'G', 'srhType'=>'LOC', 'houseType' => '1'],
            'officetelsale'=>['menuGubun'=>'E', 'srhType'=>'LOC', 'houseType' => '1'],
            'officetelrent'=>['menuGubun'=>'E', 'srhType'=>'LOC', 'houseType' => '2'],
            'aptlots'=>['menuGubun'=>'F', 'srhType'=>'LOC', 'houseType' => '1'],
            
            'aptsale'=>['menuGubun'=>'A', 'srhType'=>'LOC', 'houseType' => '1'],
            'aptrent'=>['menuGubun'=>'A', 'srhType'=>'LOC', 'houseType' => '2'],
            'flatsale'=>['menuGubun'=>'B', 'srhType'=>'LOC', 'houseType' => '1'],
            'flatrent'=>['menuGubun'=>'B', 'srhType'=>'LOC', 'houseType' => '2'],
            'housesale'=>['menuGubun'=>'C', 'srhType'=>'LOC', 'houseType' => '1'],
             'houserent'=>['menuGubun'=>'C', 'srhType'=>'LOC', 'houseType' => '2'],
];

//crawlAll($argv[1], intval($argv[2]));
crawlNow($argv[1]);

/**
* crawl this month and last months for updates
*/
function crawlNow($tname) {
  date_default_timezone_set("Asia/Seoul");
  $date = date_create();

  $thisMonth = $date->format('m');
  $thisYear = $date->format('m');
    
  if ($thisMonth==1 || $thisMonth==4 || $thisMonth==7 || $thisMonth==10) {
    // current month
    crawl($tname, $date->format('Y'), [$date->format('m')]);

    // last month
    $date->modify('-1 month');
    crawl($tname, $date->format('Y'), [$date->format('m')]);
  } else {
    // current month
    crawl($tname, $date->format('Y'), [$thisMonth-1, $thisMonth]);    
  }
  
}

// Crawl all upto date
function crawlAll($tname, $styear) {
  for ($year = $styear; $year <= 2015; $year++) {
    for ($period=1;$period<=4; $period++) {
      crawl($tname, $year, $period, null);
    }
  }
}

function crawl($tname, $year, $monthArr) {
   $dealType = $GLOBALS['dealType'];
   $stateArr = $GLOBALS['stateArr'];

  if ($monthArr==null) {
    die("monthArr must be set!");
  }

  $period = intval(($monthArr[0]-1)/3)+1;
  echo ("Working on $year ($period) " . implode(" ", $monthArr) . "\n");

  $db = new mysqli("p:localhost", "trend", "only!trend!", "rtrend");
  // Check connection
  if ($db->connect_error) {
      die("Connection failed: " . $db->connect_error);
  }

  // Set utf8
  //$db->set_charset("utf8");

  if (!isset($dealType[$tname])) {
    die("No args for $tname\n");
  }
  // get deal type and table name
  $args = $dealType[$tname];
  

  foreach ($stateArr as $state => $stateCode) {
    echo("Working on $year ($period) on $state for $tname\n");

    $cities = getCities($year, $period, $stateCode, $args);
    $cityArr = json_decode($cities, true);

    foreach ($cityArr['jsonList'] as $city) {
      $counties = getCounties($year, $period, $stateCode, $city['CODE'], $args);
      $countyArr = json_decode($counties, true);

      foreach($countyArr['jsonList'] as $county) {
        $deals = getDeals($year, $period, $stateCode, $city['CODE'], $county['CODE'], $args);
        $dealArr = json_decode($deals, true);

        foreach ($monthArr as $month) {
         // echo("Working on $year/$month ($period) on $state " . $city['NAME'] . " " . $county['NAME'] . "\n");

          $infoArr = ['year'=>$year, 'month'=>$month,
                      'state'=>$state, 'city'=>$city['NAME'],
                      'county'=>$county['NAME']];

          // update
          // function update($db, $tname, $metaArr, $json) {
          update($db, $tname, $infoArr, $deals);
        }
      }
    }
  }

  // make aggregation
  foreach ($monthArr as $month) {
    echo ("Make agg $tname on $year/$month");
    mkagg($db, $tname, $year, $month);
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

  return doPost('http://rt.molit.go.kr/srh/getGugunListAjax.do', $args);
}


?>