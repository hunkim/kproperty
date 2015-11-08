#!/usr/bin/php
<?php

$dir = "./xls/";

$xlsx_njs = "/home/ubuntu/workspace/khometrend/js/node_modules/xlsx/bin/xlsx.njs";

$d = dir($dir);
while (false !== ($entry = $d->read())) {
  if(!is_dir($dir.$entry) && endsWith($entry, ".xls")) {
    $csvFile =  $entry .".csv" ;
    echo "working on ..." . $csvFile . "\n";
    if (file_exists($dir . $csvFile)) {
	echo "Skipping ". $csvFile . "\n";
    } else {
      $regions = getSheetNames($xlsx_njs, $dir, $entry);
      foreach($regions as $key=>$sname) {
        $sysStr = "js ";
        $sysStr .= $xlsx_njs;
        $sysStr .=  " '" . $dir. $entry . "'";
	$sysStr .=  " '" . $sname . "' ";
	if ($key==0) {
  	  $sysStr .= ">";
	} else {
  	  $sysStr .= ">>";
	}
	$sysStr .= " '" . $dir.  $csvFile ."'" ;
	system ($sysStr);
     }
     processCSV($dir, $csvFile);
    }
  }
}

 $d->close();

function getSheetNames($cmd, $dir, $xlsName) {
	$out = exec("js " . $cmd . " -l '" . $dir . $xlsName . "'", $outArr);
	echo($outArr);
	return $outArr;
}

function processCSV($dir, $fname) {
	list ($year, $month, $rest) = split("_", $fname, 3);
	$year = intval($year);
	$month = intval($month);

	echo "Working on " . $year . "/" . $month . "\n";
	$sysStr = "./readcsv.php " . $year . " " . $month . " '" . $dir . $fname ."' ";  
	system ($sysStr);
}


function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

?>
