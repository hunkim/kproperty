<?php
        // create curl resource
        $ch = curl_init();

        // set url
         curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://rt.molit.go.kr/srh/getListAjax.do',
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36',
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => array(
                'menuGubun'=>'G', 'srhType'=>'LOC', 'houseType' => '1',
                'srhYear'=>'2015', 'srhPeriod'=>'4', 'gubunCode'=>'LAND',
                'sidoCode'=>'11', 'gugunCode'=>'11680', 'dongCode'=>'1168011100',
                'chosung'=>'', 'roadCode'=>'', 'danjiCode' => '', 'rentAmtType' =>'3',
                'fromAmt1'=>'', 'toAmt1'=>'', 'fromAmt2'=>'', 'toAmt2'=>'', 'fromAmt3'=>'',
                'toAmt3'=>'', 'areaCode'=>'', 'jimokCode'=>'', 'useCode'=>'', 'useSubCode'=>''
    )
));

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        print ($output);

        // close curl resource to free up system resources
        curl_close($ch);
?>