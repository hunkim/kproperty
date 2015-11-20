#!/usr/bin/php
<?php
error_reporting(E_ALL);

assert_options(ASSERT_BAIL,     true);
if (count($argv) < 2) {
    echo "Usage: $argv[0] <xls_dir> <reload:csvonly>\n\n";
    exit;
}

$reload = (count($argv)==3 && $argv[2]=='reload');
$csvonly = (count($argv)==3 && $argv[2]=='csvonly');

echo("Reload: $reload, CSVONLY: $csvonly\n");

//main($argv[1] . "/");
$colnames = ['housesale', 'aptsale', 'flatsale', 'houserent', 'aptrent', 'flatrent'];
foreach ($colnames as $name) {
    main("$argv[1]/$name/", $name, $reload, $csvonly);
}

function test($dir) {
    readCSV(".", '2015_06_test.csv');

}
/* The main controller */
// $dir should end with '/'
function main($dir, $colname, $reload, $csvonly) {
    $xlsx_njs = "./js/node_modules/xlsx/bin/xlsx.njs";

    echo ($dir);
    $d = dir($dir);
    while (false !== ($entry = $d->read())) {
        if(!is_dir($dir. $entry) && endsWith($entry, ".xls")) {
            $csvFile =  "$entry.csv" ;

            if (file_exists($dir . $csvFile)) {
                if ($reload==1) {
                  echo "$csvFile already exist. Reload it!\n";
                  // process generated CSV
                  readCSV($dir, $csvFile, $colname);
                } else {
                  echo "$csvFile already exist. Skip it!\n";
                }
            } else {
                echo "working on $csvFile...\n";

                $snames = getSheetNames($xlsx_njs, $dir, $entry);

                foreach($snames as $key=>$sname) {
                    if ($key == 0) {
                        $sysStr = "$xlsx_njs '$dir/$entry' $sname > '$dir/$csvFile'";
                    } else {
                        $sysStr = "$xlsx_njs '$dir/$entry' $sname >> '$dir/$csvFile'";
                    }
                    echo ("Writing CSV for $sname \n");
                    system ($sysStr);
                }

                if ($csvonly) {
                  continue;
                }
                // process generated CSV
                readCSV($dir, $csvFile, $colname);
            }
        }
    }
    $d->close();
}


function getSheetNames($cmd, $dir, $xlsName) {
    $cmdStr = "$cmd  -l  '$dir$xlsName'";
    echo("Executing $cmdStr ...");
    $out = exec($cmdStr, $outArr);

    assert(count($outArr));
    print_r($outArr);

    return $outArr;
}


function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function insertDB($db, $tname, $types, $fields, $data) {
  $sql = "INSERT IGNORE INTO $tname SET ";

  foreach ($fields as $i => $field) {
    if ($i != 0) {
      $sql .= ", \n\t";
    }

    $val = str_replace( ',', '', trim($data[$i]));
    $sql .= $field. '=' . typeesc($db, $types[$i], $val);
  }

  $sql .= ";\n";
  if ($db->query($sql) !== TRUE) {
      die ("Error: $sql\n $db->error");
  }
}


function readCSV($dir, $csvFile, $tableName) {
    // mysql
    $db = new mysqli("p:localhost", "trend", "only!trend!", "trend");
    // Check connection
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Get yeat and month from csvFile
    list ($year, $month, $rest) = explode("_", $csvFile, 3);
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

            $types = getTypes($fields);
            // Num of Fields
            $numFields = count($fields);

            // Add meta fields
            $fields[] = "year";
            $fields[] = "month";
            // types
            $types[] = "int";
            $types[] = "int";

            $fields[] = "state";
            $fields[] = "city";
            $fields[] = "county";
            $fields[] = "region";
            $types[] = "varchar(256)";
            $types[] = "varchar(256)";
            $types[] = "varchar(256)";
            $types[] = "varchar(256)";

            //$types[] = "i";

            print_r($fields);

            // makeTable
            createTable($db, $tableName, $types, $fields);
            continue;
        }

        // Another table head?
        $diff = array_diff($thdata, $data);
        // all same?
        if (count($diff)==0) {
            echo ("Skip another table head\n");
            continue;
        }

        $num = count($data);
        if($num != $numFields) {
            echo "<!> $num fields in line $row!\n";
          //  print_r($data);
            continue;
        }

        assert($fields);

        // add year and month
        $data[] = $year;
        $data[] = $month;

        list ($state, $city, $county, $region) =
            array_pad(explode(" ", trim($data[0]), 4), 4, null); // data 0 should be the full loc

        $data[] = $state;
        $data[] = $city;
        $data[] = $county;
        $data[] = $region;
        $data[] = $row;

        // echo "$data[0] $data[1]";
        // Let's insert
        insertDB($db, $tableName, $types, $fields, $data);
    }

    fclose($handle);
    $db->close();

     // mk grpo
     echo "<!> Inserted $row rows!\n";
}

function getFields($data) {
    $namemap = array(
    '시군구'=>'fullLoc',
    '주택유형'=>'type',
    '연면적(㎡)'=>'area',
    '연면적(m2)'=>'area',
    '연면적(M2)'=>'area',
    '대지면적(㎡)' => 'landArea',
    '대지면적(m2)' => 'landArea',
    '대지면적(M2)' => 'landArea',
    '계약일' => 'day',
    '거래금액(만원)' => 'amount',


    '본번' => 'num1',
    '부번' => 'num2',
    '단지명' => 'aptName',
    '전월세구분' => 'monthlyType',
    '전용면적(㎡)' => 'area',
    '전용면적(m2)' => 'area',
    '전용면적(M2)' => 'area',
    '보증금(만원)' => 'deposit',
    '월세(만원)' => 'monthlyPay',
    '층' => 'floor',

    '계약면적(㎡)' => 'area',
    '계약면적(m2)' => 'area',
    '계약면적(M2)' => 'area',
    '대지권면적(㎡)' => 'landArea',
    '대지권면적(M2)' => 'landArea',
    '대지권면적(m2)' => 'landArea',

    '건축년도'=>'builtYear',
    '도로명주소' => 'avenue',
    '도로명'=>'avenue');

    $fields = array();
    foreach ($data as $value) {
        $th = trim($value);
        assert ($namemap[$th]!='');
        $fields[] = $namemap[$th];
    }

    return $fields;
}

function getTypes($fields) {
    $typeArr = array('fullLoc'=>'varchar(256)',
    'type'=>'varchar(256)',
    'area'=>'double',
    'landArea' => 'double',
    'day' => 'int',
    'amount' => 'int',
    'builtYear'=>'int',
    'num1' => 'varchar(256)',
    'num2' => 'varchar(256)',
    'aptName' => 'varchar(256)',
    'monthlyType' => 'varchar(256)',
    'deposit' => 'int',
    'monthlyPay' => 'int',
    'floor' => 'int',
    'avenue'=>'varchar(256)');

    $types = array();
    foreach ($fields as $value) {
        $th = trim($value);
        assert ($typeArr[$th]!='');
        $types[] = $typeArr[$th];
    }

    return $types;
}

function createTable($db, $colname, $types, $fields) {
    $sql = "Create Table IF NOT EXISTS $colname (\n";

    foreach($types as $i => $type) {
      if ($i != 0) {
        $sql .= ",\n";
      }

      $sql .= "\t" . $fields[$i] . " $type";
    }

    $sql .= ");";

    if ($db->query($sql) !== TRUE) {
      die("Error creating table: $sql\n $db->error");
    }
}

function typeesc ($db, $type, $val) {
  switch($type) {
    case "varchar(256)":
      return "'" . $db->real_escape_string($val) . "'";

    case "int":
      return intval($val);

    default:
      return $val;
  }
}

?>
