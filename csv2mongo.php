#!/usr/bin/php
<?php

if (count($argv) != 2) {
    echo "Usage: $argv[0] <xls_dir>\n\n";
    exit;
}

//main($argv[1] . "/");
main($argv[1] . '/');

function test($dir) {
    readCSV(".", '2015_06_test.csv');

}
/* The main controller */
// $dir should end with '/'
function main($dir) {
    $xlsx_njs = "./js/node_modules/xlsx/bin/xlsx.njs";

    echo ($dir);
    $d = dir($dir);
    while (false !== ($entry = $d->read())) {
        if(!is_dir($dir. $entry) && endsWith($entry, ".xls")) {
            $csvFile =  "$entry.csv" ;

            if (file_exists($dir . $csvFile)) {
                echo "$csvFile already exist. Skip it!\n";
            } else {
                echo "working on $csvFile...\n";

                $sName = getSheetNames($xlsx_njs, $dir, $entry);
        
                foreach($sName as $key=>$sname) {
                    if ($key == 0) {
                        $sysStr = "$xlsx_njs '$dir/$entry' $sname > '$dir/$csvFile'";
                    } else {
                        $sysStr = "$xlsx_njs '$dir/$entry' $sname >> '$dir/$csvFile'";
                    }

                    system ($sysStr);
                }

                // process generated CSV
                processCSV($dir, $csvFile);
            }
        }
    }
    $d->close();
}


function getSheetNames($cmd, $dir, $xlsName) {
    $cmdStr = "$cmd  -l  '$dir$xlsName' $outArr";
    echo("Executing $cmdStr ...");
    $out = exec($cmdStr);

    assert(count($outArr));
    print_r($outArr);

    return $outArr;
}


function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function insertDB($db, $collection, $types, $fields, $data) {
    $dbData = array();
    foreach ($fields as $i => $field) {
        $val = str_replace( ',', '', trim($data[$i]));
        if ($types[$i]=='i') {
            $dbData[$field] = intval($val);
        } else if ($types[$i]=='f') {
            $dbData[$field] = floatval($val);
        } else {
            $dbData[$field] = $val;
        }
    }

    // Insert it to DB
    $collection->insert($dbData);


    $cursor = $collection->find();
    echo json_encode(iterator_to_array($cursor), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ;
} 

function makeDBIndex($db, $collection, $fields, $data) {
    $dbData = array();
    foreach ($fields as $i => $field) {
        $dbData[$field] = 1;
    }

    // Insert it to DB
    $r = $collection->createIndex($dbData, array ('name'=> 'all', 'unique'=> 'true'));
    echo $r;
} 

function readCSV($dir, $csvFile) {
    // connect
    $m = new MongoClient();

    // select a database
    $db = $m->trend;

    // get table name and table name, type
    $tableName = getTableName($csvFile);

    $collection = $db->$tableName;

    // Get yeat and month from csvFile
    list ($year, $month, $rest) = split("_", $csvFile, 3);
    $year = intval($year);
    $month = intval($month);
    assert($year!=null && $month!=null);

    $row = 0;

    if (($handle = fopen("$dir/$csvFile", "r")) == FALSE) {
        echo ("$dir . $csvFile not found!");
        return;
    }
        
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) {
        // table head field
        if ($row++==0) {
            $thdata = array_values($data);

            $fields = getFields($data);

            $types = getTypes($data);
            // Num of Fields
            $numFields = count($fields);

            // Add meta fields
            $fields[] = "year";
            $fields[] = "month";
            
            $fields[] = "state";
            $fields[] = "city";
            $fields[] = "county";
            $fields[] = "region";

            $fields[] = "xlsrow";

             // types
            $types[] = "i";
            $types[] = "i";
            
            $types[] = "s";
            $types[] = "s";
            $types[] = "s";
            $types[] = "s";

            $types[] = "i";
            
            print_r($fields);

            //make a unique/index index
            makeDBIndex($db, $collection, $fields);

            continue;
        } 

        // Another table head?
        $diff = array_diff($thdata, $data);
        // all same?
        if (count($diff)==0) {
            echo ("Skip another table head");
            continue;
        }

        $num = count($data);
        if($num != $numFields) {
            echo "<!> $num fields in line $row!\n";
            print_r($data);
            continue;
        }

        assert($fields);

        // add year and month
        $data[] = $year;
        $data[] = $month;

        list ($state, $city, $county, $region) = 
            split(" ", trim($data[0]), 4); // data 0 should be the full loc

        $data[] = $state;
        $data[] = $city;
        $data[] = $county;
        $data[] = $region; 
        $data[] = $row; 

        // echo "$data[0] $data[1]";
        // Let's insert
        insertDB($db, $collection, $types, $fields, $data);
    }

    fclose($handle);

    echo "<!> Inserted $row rows!\n";

    $db->close();
}



function getFields($data) {
    $namemap = array('시군구'=>'fullLoc', 
    '주택유형'=>'type',    
    '연면적(㎡)'=>'area',
    '대지면적(㎡)' => 'landArea',
    '계약일' => 'day', 
    '거래금액(만원)' => 'amount',


    '본번' => 'num1',
    '부번' => 'num2',
    '단지명' => 'aptName',
    '전월세구분' => 'monthlyType',  
    '전용면적(㎡)' => 'usedArea',
    '보증금(만원)' => 'deposit',
    '월세(만원)' => 'monthlyPay',
    '층' => 'floor',

    '계약면적(㎡)' => 'contractArea',
    '대지권면적(㎡)' => 'landArea',

    '건축년도'=>'builtYear',    
    '도로명'=>'avenue');

    $fields = array();
    foreach ($data as $value) {
        $th = trim($value);
        assert ($namemap[$th]!='');
        $fields[] = $namemap[$th];
    }

    return $fields;
}

function getTypes($data) {
    $namemap = array('시군구'=>'s', 
    '주택유형'=>'s',    
    '연면적(㎡)'=>'f',
    '대지면적(㎡)' => 'f',
    '계약일' => 'i', 
    '거래금액(만원)' => 'i',
    '건축년도'=>'i',  
    '본번' => 's',
    '부번' => 's',
    '단지명' => 's',
    '전월세구분' => 's',  
    '전용면적(㎡)' => 'f',
    '보증금(만원)' => 'i',
    '월세(만원)' => 'i',
    '층' => 'i',

    '계약면적(㎡)' => 'f',
    '대지권면적(㎡)' => 'f',  
    '도로명'=>'s');

    $types = array();
    foreach ($data as $value) {
        $th = trim($value);
        assert ($namemap[$th]!='');
        $types[] = $namemap[$th];
    }

    return $types;
} 

function getTableName($csvFile) {
    if (strpos($a,'단독-다가구(전월세)') !== false) {
        return "houserent";
    }

    if (strpos($a,'아파트(전월세)') !== false) {
        return "aptrent";
    }

    if (strpos($a,'연립-다세대(전월세)') !== false) {
        return "flatrent";
    }

    if (strpos($a,'단독-다가구(매매)') !== false) {
        return "housesale";
    }

    if (strpos($a,'아파트(매매)') !== false) {
        return "aptsale";
    }

    if (strpos($a,'연립-다세대(매매)') !== false) {
        return "flatsale";
    }

    assert(true);
}

?>

