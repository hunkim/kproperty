#!/usr/bin/php
<?php
include 'classDBConnect.php';

if (count($argv) != 2) {
    echo "Usage: " . $argv[0] . " <xls_dir>\n\n";
    exit;
}

main($argv[1] . "/");

/* The main controller */
// $dir should end with '/'
function main($dir) {}
    $xlsx_njs = "./js/node_modules/xlsx/bin/xlsx.njs";

    $d = dir($dir);
    while (false !== ($entry = $d->read())) {
        if(!is_dir($dir. . $entry) && endsWith($entry, ".xls")) {
            $csvFile =  $entry .".csv" ;

            if (file_exists($dir . $csvFile)) {
                echo "$csvFile already exist. Skip it!\n";
            } else {
                echo "working on $csvFile...\n";

                $regions = getSheetNames($xlsx_njs, $dir, $entry);
        
                foreach($regions as $key=>$sname) {
                    $sysStr = "js ";
                    $sysStr .= $xlsx_njs;
                    $sysStr .=  " '" . $dir. $entry . "'";
                    $sysStr .=  " '" . $sname . "' ";
    
                    if ($key == 0) {
                        $sysStr .= ">";
                    } else {
                        $sysStr .= ">>";
                    }

                    $sysStr .= " '" . $dir . $csvFile ."'" ;
                    system ($sysStr);
                }

                // process generated CSV
                processCSV($dir, $csvFile);
        }
    }
    $d->close();
}


function getSheetNames($cmd, $dir, $xlsName) {
    $out = exec("js " . $cmd . " -l '" . $dir . $xlsName . "'", $outArr);
    echo($outArr);
    return $outArr;
}


function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function insertDB($db, $tableName, $fieldNameArr, $type, $CSVdata) {
    $sql = "INSERT INTO ";
    $sql .= $tableName;
    $sql .= " (";

    for (...) {
        if ($index != 0) {
            $sql.= ",";
        }
        $sql .= name;
    }

    $sql .=  " ) VALUES (";
    for (...) {
        if ($index != 0) {
            $sql.= ",";
        }
        $sql .= "?";
    }

    $sql .= ")";

    // set values
    $values = array();
    $tmpVal = array();

    for (...) {
        $tmpVal[i] = str_replace( ',', '', trim($CSVdata[i]);
        $values[]= &$tmpVal[i];
    }

        // eecute SQL
        $stmt->execute();
        $stmt->close();


} 

function readCSV($dir, $csvFile) {
    $db = new DBconnect;
    $db->connect();

    // get table name and table name, type
    list($tableName, $fieldNameArr, $type) = getTableInfo($csvFile);

    // Get yeat and month from csvFile
    list ($year, $month, $rest) = split("_", $csvFile, 3);
    $year = intval($year);
    $month = intval($month);
    assert($year!=null && $month!=null);

    // Num of Fields
    $numFields = $fieldNameArr.length;

    // add type and fieldNameArr
    $fieldNameArr[] = "year";
    $fieldNameArr[] = "month";

    $type .= "ii";

    $row = 1;

    if (($handle = fopen($dir ."/". "$csvFile", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) {
        $num = count($data);
        if($num != $numFields) {
            echo "<!> $num fields in line $row!\n";
        } else {
        	$row++;
            // add year and month
            $data[] = $year;
            $data[] = $month;

            // Let's insert
            insertDB($db, $tableName, $fieldNameArr, $type, $data);
	   }   
	/*
        for ($c=0; $c < $num; $c++) {
            //echo $data[$c] . "<br />\n";
        }
	*/
    }
    fclose($handle);

    echo "<!> Inserted $row rows!\n";

    $db->close();
}

function getTableInfo($csvFile) {
    /* 
    단독-다가구(매매)
    연립-다세대(매매)
    아파트(매매)
    단독-다가구(전월세)
    연립-다세대(전월세)
    아파트(전월세)
    */
}

?>
