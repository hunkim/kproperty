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


/*p> 8 fields in line 6579: <br /></p>
 경기도 평택시 신장동<br />
다가구<br />
393.48<br />
500<br />
1~10<br />
55,000<br />
1986<br />
남산로26번길<br />
*/

$row = 1;
if (($handle = fopen($argv[3], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        if($num!=8) {
		echo("!!!Number is " . $num . "\n");
        } else {
        	$row++;
        	storeDB($argv, $data, $db);
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


function storeDB($argv, $data, $db) {
	$deal = new HouseDeal;
        $deal->fullCity = trim($data[0]);
        $deal->type = $data[1];
        $deal->area = $data[2];
        $deal->landArea = $data[3];
        $deal->date = intval($data[4]); //11~20 .. use the first one
        $deal->amount = str_replace( ',', '', $data[5]);
        $deal->builtYear = $data[6];
        $deal->avenue = $data[7];

	$deal->year = $argv[1];
	$deal->month = $argv[2];

	echo ($deal->toString());

	if ($deal->amount) {
	   $db->insert($deal);
        }
}
?>
