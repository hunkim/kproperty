<?php

error_reporting(E_ALL);

include_once 'util.php';

//phpinfo();

date_default_timezone_set("Asia/Seoul");
$date = date_create();
echo ($date->format('Y') . "/" . $date->format('m') . "\n");

$date->modify('-1 month');
echo ($date->format('Y') . "/" . $date->format('m') . "\n");


$fileList = ["aptsale.json", "aptrent.json", "flatsale.json", "flatrent.json", "houserent.json", "housesale.json"
	,"officetelsale.json", "officetelrent.json", "aptlots.json", "landsale.json"];



foreach ($fileList as $value) {
  $json = file_get_contents($value);
  $meta = ['year'=>2015, 'month'=>10, 'state'=>'서울특별시', 'city'=>'강동구', 'county'=>'압구정동'];

  update(null, $value, $meta, $json);
}

?>