#!/usr/bin/php
<?php
include 'classHouseDeal.php';
include 'classDBConnect.php';

if (count($argv) != 4) {
	echo "Usage: " . $argv[0] . " <year> <month> <csvFile>\n\n";
	exit;
}

$db = new DBconnect;
$db->connect();

$row = 1;
if (($handle = fopen($argv[3], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        if($num!=8) {
		  echo("!!!Number is " . $num . "\n");
        } else {
        	$row++;
            $deal = new HouseDeal;
        	$deal->parseCSV($argv[1], $argv[2], $data);
            $deal->insertDB($db);
	   }   
	/*
        for ($c=0; $c < $num; $c++) {
            //echo $data[$c] . "<br />\n";
        }
	*/
    }
    fclose($handle);
}

$db->close();

?>
