<?php

function update($db, $tname, $metaArr, $json) {
  $arr = json2Arr($json);

  $dealCount = 0;
  foreach ($arr as $deal) {
    // check the month and push only one month
    if ($deal['DEAL_MM'] == $metaArr['month']) {
      $dealCount++;
      }
  }

  if ($dealCount == 0) {
    echo ("Nothing to add\n");
    return;
  }

  $delsql = getDelSQL($db, $tname, $metaArr);

  if ($db!=null) {
    if ($db->query($delsql) !== TRUE) {
        die ("Error: $delsql\n $db->error");
    }
  }
  //print($delsql);

  foreach ($arr as $deal) {
    // check the month and push only one month
    if ($deal['DEAL_MM'] != $metaArr['month']) {
      continue;
    }

    $sql = arr2SQL($db, $tname, $metaArr, $deal);
    if ($db != null) {
      if ($db->query($sql) !== TRUE) {
          die ("Error: $sql\n $db->error");
      }
    }
    print($tname);
  }
}


function getDelSQL($db, $tname, $metaArr) {
  // delete the month/region
  $sqlDel = "delete from $tname where 1=1\n";
  foreach ($metaArr as $key => $value) {
    $sqlDel .= "\tand $key='" . $value . "'\n";
  }

  return $sqlDel;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function arr2SQL($db, $tname, $metaArr, $arr) {
   $tmap = [
    'BOBN' => 'avenue',
    'BLDG_NM' => 'aptName',
    'BUILD_YEAR' => 'builtYear',
    'BLDG_AREA' => 'area',
    'APTFNO' => 'floor',
    'DEAL_DD' => 'day',
//    'DEAL_MM' => 'month',
    'SUM_AMT' => 'amount',
    'RENT_AMT' => 'monthlyPay',
    'TOT_AREA' => 'landArea',
    'RIGHT_AMT' => 'rightAmount',
    'JIMOK_NM' => 'type',
    'USE_REGN_NM' => 'usedType',
    'OBJ_AMT' => 'amount',
    'PRIV_AREA'=>'area',
    'UMD_NM'=> 'region'
  ];


  $res = "insert DELAYED into $tname set year='" . $metaArr['year'] . "'";
  foreach ($metaArr as $key => $value) {
    if ($key!== 'year') {
      $res .= ",\n\t$key='" . $value . "'";
    }
  }

  foreach ($arr as $key => $value) {
    // if county and UMD_MM are the same, skip
    if ($key=='UMD_NM') {
      if($value==$metaArr['county']) {
        $value="";
      } else if (startsWith($value, $metaArr['county'] + " ")) {
        $value =  substr($value, strlen($metaArr['county'])+1);
      }
    }

    if (isset($tmap[$key])) {
      $res .= ",\n\t" . $tmap[$key] . " = '";
      if ($db!=null) {
        $res .= $db->real_escape_string(str_replace(",", "", $value)) . "'";
      } else {
        $res .= (str_replace(",", "", $value)) . "'";
      }

      if ($key=='RENT_AMT') {
        if ($value=='0') {
          $res .= ", monthlyType='전세'"; 
        } else {
          $res .= ", monthlyType='월세'";
        }
      }
    } else {
      // echo("Check $key=$value\n");
    }
  }

  return $res . ";\n\n";
}

function getMonth ($metaArr, $dealArr) {
  $res = [];
  foreach ($dealArr as $value) {
    $dealInfo = array_merge ($value, $metaArr);
    $res[] = $dealInfo;
  }

  return $res;
}

function getMeta($list) {
  $meta = [];

  foreach ($list as $key => $value) {
    if ($key==='month1List' || $key==='month2List' || $key==='month3List') {
      continue;
    }

    $meta[$key] = $value;
  }

  return $meta;
}

function json2Arr($json) {
  $arr = json_decode($json, true);
 // print_r($arr);

  $res = [];

  foreach ($arr['jsonList'] as $list) {
    $metadata = getMeta($list);
    // get some metadata
    $month1 = getMonth($metadata, $list['month1List']);
    $month2 = getMonth($metadata, $list['month2List']);
    $month3 = getMonth($metadata, $list['month3List']);

    $res = array_merge($res, $month1, $month2, $month3);
  }

  return $res;
}

?>