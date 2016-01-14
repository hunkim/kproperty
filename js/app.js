'use strict';

var app = angular.module('myApp', ['chart.js', 'cgBusy']);

app.controller('customersCtrl',
  function($scope, $http, $location) {
    $scope.m2top = 3.30579;
    $scope.ptom2 = 0.30259;

    // API Host
    var $rhost = "http://r.kproperty.xyz";

    // API URLs
    var $regionUrl = $rhost + "/r2.php";
    var $saleUrl = $rhost + "/q2.php";
    var $statUrl = $rhost + "/s2.php";

    // app type
    $scope.appType = 'aptsale';

    // show count option in the graph
    $scope.showCount = false;

    // http error flag
    $scope.errorFlag = false;

    // default action
    $scope.stateArr = [
      "", "서울특별시", "부산광역시", "대구광역시", "인천광역시", "광주광역시", "대전광역시", "울산광역시",
      "세종특별자치시",
      "경기도", "강원도", "충청북도", "충청남도", "전라북도", "전라남도", "경상북도", "경상남도",
      "제주특별자치도"
    ];

    // default action
    $scope.rentArr = ["", "월세", "전세"];

    // year min and max
    $scope.minYear = 2006; // rent should be 2010
    $scope.maxYear = new Date().getFullYear();

    // loc selection
    $scope.loc = {
      state: "",
      city: "",
      county: "",
      region: "",
      aptName: "",
      area: "",
      landType: "",
      landUsedType: "",
      monthlyType: "",
      startYear: $scope.minYear,
      endYear: $scope.maxYear
    };

    // get full LOC
    $scope.fullLoc = function(sale) {
      return sale.state + " " + sale.city + " " + sale.county + " " + sale.region;
    };

    // select current location
    $scope.selectLoc = function(sale) {
      $scope.loc.state = sale.state;
      
      $scope.getCity(false);
      $scope.loc.city = sale.city;
      
      $scope.getCounty(false);
      $scope.loc.county = sale.county;
      
      $scope.getRegion(false);
      $scope.loc.region = sale.region;

      $scope.getAptName(false);

      $scope.upAll();
    };    

    // select current location
    $scope.selectApt = function(sale) {
      $scope.loc.state = sale.state;
      
      $scope.getCity(false);
      $scope.loc.city = sale.city;
      
      $scope.getCounty(false);
      $scope.loc.county = sale.county;
      
      $scope.getRegion(false);
      $scope.loc.region = sale.region;

      $scope.getAptName(false);
      $scope.loc.aptName = sale.aptName;

      $scope.getAptArea(false);

      $scope.upAll();
    };    

    // Chart Data
    $scope.series = ['건물총면적 평당 가격', '대지면적 평당 가격', '거래량'];
    $scope.labels = [];
    $scope.data = [
      [],
      [],
      []
    ];

    // stat and sale array
    $scope.statArr = [];
    $scope.saleArr = [];

    // county and region array
    $scope.cityArr = [];
    $scope.countyArr = [];
    $scope.regionArr = [];

    // reset apt related array
    $scope.aptNameArr = [];
    $scope.aptAreaArr = [];


    $scope.amIActive = function(name) {
      if (name == $scope.appType) {
        return 'active';
      }

      return '';
    }

    $scope.appNameList = {
      aptsale: '아파트 매매',
      flatsale: '연립-다세대 매매',
      housesale: '단독-다가구 매매',
      aptrent: '아파트 전/월세',
      flatrent: '연립-다세대 전/월세',
      houserent: '단독-다가구 전/월세',
      officetelsale: '오피스텔 매매',
      officetelrent: '오피스텔 전/월세',
      landsale: '토지 매매',
      aptlots: '분양권'
    };

    $scope.setAppType = function(type) {
      $scope.appType = type;

      if ($scope.loc.county != "" && $scope.getAptKind()) {
        $scope.getAptName();
      }

      $scope.upAll();
    }

    // APT?
    $scope.getAptKind = function() {
      if ($scope.appType == 'housesale' || $scope.appType == 'houserent' || $scope.appType == 'landsale') {
        return false;
      }

      return true;
    }

    $scope.getRentKind = function() {
      if ($scope.appType == 'aptrent' || $scope.appType == 'flatrent' ||
        $scope.appType == 'houserent' || $scope.appType == 'officetelrent') {
        return true;
      }

      return false;
    }

    $scope.isAptkind = $scope.getAptKind();
    $scope.isRentkind = $scope.getRentKind();


    // recovering from network error
    $scope.reconnect = function() {
      $scope.errorFlag = false; //reset the flag and let's hope

      // check if county is loaded
      if ($scope.regionArr.length == 0 && $scope.loc.county != "") {
        $scope.getRegion();
      } else if ($scope.countyArr.length == 0 && $scope.loc.city != "") {
        $scope.getCounty();
      }

      // no statdate? reload!
      if ($scope.statArr.length == 0) {
        $scope.getStat();
      }

      // no sale array? reload!
      if ($scope.saleArr.length == 0) {
        $scope.getSales();
      }
    };

    // local function for KR URL encodning
    var koEncode = function($s) {
      if ($s == null || $s == "") {
        return $s;
      }
      return encodeURI(encodeURIComponent($s));
    };

    // return information for daum map
    $scope.mapEncode = function($sale) {
      if ($sale == null) {
        return "";
      }
      
      var mapStr = "http://map.daum.net/?q='" + $scope.fullLoc($sale);
      if ($sale.avenue!= 0) {
        mapStr +=  " " + parseInt($sale.avenue);
      }
      
      mapStr += "'";

      return mapStr;
    };

    // based on the startYear selection, we adjust the endYear list
    $scope.$watch('loc.startYear', function() {
      $scope.endYears = [];
      for (var i = $scope.loc.startYear; i <= $scope.maxYear; i++) {
        $scope.endYears.push(i);
      }
    });

    // based on the end year selecion, we adjust the start year list
    $scope.$watch('loc.endYear', function() {
      $scope.startYears = [];
      for (var i = $scope.minYear; i <= $scope.loc.endYear; i++) {
        $scope.startYears.push(i);
      }
    });

    var m2pFormat = function($area) {
      return ($area * $scope.m2top).toFixed(2);
    };

    $scope.pFormat = function($area) {
      return ($area * scope.p2m2).toFixed(2);
    };

    $scope.updateGraph = function() {
      switch ($scope.appType) {
        case 'aptsale':
        case 'flatsale':
        case 'officetelsale':
        case 'landsale':
        case 'aptlots':
          $scope.updateAptSaleGraph();
          break;

        case 'housesale':
          $scope.updateHouseSaleGraph();
          break;

        case 'aptrent':
        case 'flatrent':
        case 'officetelrent':
          if ($scope.loc.monthlyType == "") {
            $scope.updateAptRentAllGraph();
          } else {
            $scope.updateAptRentDepositGraph();
          }
          break;

        case 'houserent':
          if ($scope.loc.monthlyType == "") {
            $scope.updateHouseRentAllGraph();
          } else {
            $scope.updateHouseRentDepositGraph();
          }
          break;
      }
    }

    $scope.emptyGraph = function() {
        $scope.series = ['거래 정보가 없습니다.'];
        $scope.label = [0];
        $scope.data[0] = [0];
    }

    // update the graph (based on watch statArr)
    $scope.updateHouseSaleGraph = function() {
      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];
      $scope.data[1] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[2] = [];
      }

      // show count? Then, three series. Otherwise, two
      if ($scope.showCount) {
        $scope.series = ['건물총면적 평당 가격 (만원)', '대지면적 평당 가격 (만원)', '거래량'];
      } else {
        $scope.series = ['건물총면적 평당 가격 (만원)', '대지면적 평당 가격 (만원)'];
      } 

      var statLen = $scope.statArr.length;
      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + $scope.statArr[i]
          .month;
        $scope.data[0][i] = m2pFormat($scope.statArr[i].avgAmtArea); // 평당가격
        $scope.data[1][i] = m2pFormat($scope.statArr[i].avgAmtLand); // 평당가격
        if ($scope.showCount) {
          $scope.data[2][i] = $scope.statArr[i].count;
        }
      }
    };

    // update the graph (based on watch statArr)
    $scope.updateAptSaleGraph = function() {
      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[1] = [];
      }

      var $legend = '전용면적 평당 가격 (만원)';
      if ($scope.appType=="landsale") {
        $legend = '토지 평당 가격 (만원)';
      }
      // show count? Then, three series. Otherwise, two
      if ($scope.showCount) {
        $scope.series = [$legend, '거래량'];
      } else {
        $scope.series = [$legend];
      }

      var statLen = $scope.statArr.length;

      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + $scope.statArr[
            i]
          .month;
        $scope.data[0][i] = m2pFormat($scope.statArr[i].avgAmtArea); // 평당가격
        if ($scope.showCount) {
          $scope.data[1][i] = $scope.statArr[i].count;
        }
      }
    };


    // update the graph (based on watch statArr)
    $scope.updateAptRentAllGraph = function() {
      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];
      $scope.data[1] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[2] = [];
      }

      // show count? Then, three series. Otherwise, two
      if ($scope.showCount) {
        $scope.series = ['전용면적 평당 보증금(만원)', '전용면적 평당 월세(만원)', '거래량'];
      } else {
        $scope.series = ['전용면적 평당 보증금(만원)', '전용면적 평당 월세(만원)'];
      }

      var statLen = $scope.statArr.length;

      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + 
         $scope.statArr[i].month;
        $scope.data[0][i] = m2pFormat($scope.statArr[i].avgDeposit); // 평당가격
        $scope.data[1][i] = m2pFormat($scope.statArr[i].avgRent); // 평당가격

        if ($scope.showCount) {
          $scope.data[2][i] = $scope.statArr[i].count;
        }
      }
    };

    // update the graph (based on watch statArr)
    $scope.updateHouseRentAllGraph = function() {
      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];
      $scope.data[1] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[2] = [];
      }

      // show count? Then, three series. Otherwise, two
      if ($scope.showCount) {
        $scope.series = ['계약면적 평당 보증금(만원)', '계약면적 평당 월세(만원)', '거래량'];
      } else {
        $scope.series = ['계약면적 평당 보증금(만원)', '계약면적 평당 월세(만원)'];
      }

      var statLen = $scope.statArr.length;

      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + $scope.statArr[i]
          .month;
        $scope.data[0][i] = m2pFormat($scope.statArr[i].avgDeposit); // 평당가격
        $scope.data[1][i] = m2pFormat($scope.statArr[i].avgRent); // 평당가격

        if ($scope.showCount) {
          $scope.data[2][i] = $scope.statArr[i].count;
        }
      }
    };

    // update the graph (based on watch statArr)
    $scope.updateAptRentDepositGraph = function() {
      var isDeposit = $scope.loc.monthlyType == "전세";
      //   console.log(isDeposit + " " + $scope.loc.monthlyType);
      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[1] = [];
      }

      // show count? Then, three series. Otherwise, two
      var legend = '전용면적 평당 월세(만원)';

      if (isDeposit) {
        legend = '전용면적 평당 보증금(만원)';
      }
      if ($scope.showCount) {
        $scope.series = [legend, '거래량'];
      } else {
        $scope.series = [legend];
      }

      var statLen = $scope.statArr.length;

      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + $scope.statArr[
            i]
          .month;
        if (isDeposit) {
          $scope.data[0][i] = m2pFormat($scope.statArr[i].avgDeposit); // 평당가격
        } else {
          $scope.data[0][i] = m2pFormat($scope.statArr[i].avgRent); // 평당가격
        }

        if ($scope.showCount) {
          $scope.data[1][i] = $scope.statArr[i].count;
        }
      }
    };

    // update the graph (based on watch statArr)
    $scope.updateHouseRentDepositGraph = function() {
      var isDeposit = $scope.loc.monthlyType == "전세";

      // reset graph array
      $scope.labels = [];
      $scope.data = [];
      $scope.data[0] = [];

      // Init if nedded
      if ($scope.showCount) {
        $scope.data[1] = [];
      }

      // show count? Then, three series. Otherwise, two
      var legend = '계약면적 평당 월세(만원)';
      if (isDeposit) {
        legend = '계약면적 평당 보증금(만원)';
      }
      if ($scope.showCount) {
        $scope.series = [legend, '거래량'];
      } else {
        $scope.series = [legend];
      }

      var statLen = $scope.statArr.length;

      if (statLen==0) {
        $scope.emptyGraph();
        return;
      }

      for (var i = 0; i < statLen; i++) {
        $scope.labels[i] = $scope.statArr[i].year + "/" + $scope.statArr[i]
          .month;
        if (isDeposit) {
          $scope.data[0][i] = m2pFormat($scope.statArr[i].avgDeposit); // 평당가격
        } else {
          $scope.data[0][i] = m2pFormat($scope.statArr[i].avgRent); // 평당가격
        }

        if ($scope.showCount) {
          $scope.data[1][i] = $scope.statArr[i].count;
        }
      }
    };
    // greaph on click. TODO: what should we do?
    $scope.onClick = function(points, evt) {
      //console.log(points, evt);
    };

    $scope.clearSelection = function(level) {
      // clear region
      switch (level) {
        case 'city':
          $scope.cityArr = [];
          $scope.loc.city = "";
        case 'county':
          $scope.countyArr = [];
          $scope.loc.county = "";
        case 'region':
          $scope.regionArr = [];
          $scope.loc.region = "";

          $scope.loc.aptName = "";
          $scope.loc.area = "";
          $scope.aptNameArr = [];
          $scope.aptAreaArr = [];

          $scope.landTypeArr = [];
          $scope.landUsedTypeArr = [];

          $scope.loc.landType = "";
          $scope.loc.landUsedType = "";
      }
    };

    // get county array
    $scope.getCity = function(update) {
      // clear region
      $scope.clearSelection('city');

      $scope.errorFlag = false;
      $scope.countyPromise = $http.get($regionUrl + "/" +
          $scope.appType + "?state=" +
          koEncode($scope.loc.state) +
          "&query=city", {cache:true})
        .success(function(response) {
          $scope.cityArr = response;
          $scope.cityArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      if (update) {
        $scope.upAll();
      }
    };

    // get region array
    $scope.getCounty = function(update) {
      $scope.clearSelection('county');

      $scope.errorFlag = false;
      $scope.regionPromise = $http.get($regionUrl + "/" +
          $scope.appType + "?state=" +
          koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&query=county", {cache:true})
        .success(function(response) {
          $scope.countyArr = response;
          $scope.countyArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      if (update) {
        $scope.upAll();
      }
    };

    // get region array
    $scope.getRegion = function(update) {
      $scope.clearSelection('region');

      $scope.errorFlag = false;
      $scope.regionPromise = $http.get($regionUrl + "/" +
          $scope.appType +
          "?state=" + koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&county=" + koEncode($scope.loc.county) +
          "&query=region", {cache:true})
        .success(function(response) {
          $scope.regionArr = response;
          $scope.regionArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      if (update) {
        $scope.upAll();
      }
    };


    // get region array
    $scope.getAptName = function(update) {
      $scope.aptNameArr = [];
      $scope.aptAreaArr = [];

      $scope.loc.aptName = "";
      $scope.loc.area = "";

      $scope.errorFlag = false;

      $scope.aptNamePromise = $http.get($regionUrl + "/" +
          $scope.appType +
          "?state=" + koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&county=" + koEncode($scope.loc.county) +
          "&region=" + koEncode($scope.loc.region) +
          "&query=aptName", {cache:true})
        .success(function(response) {
          $scope.aptNameArr = response;
          $scope.aptNameArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

       if (update) {
        $scope.upAll();
      }
    };

    // get region array
    $scope.getAptArea = function(update) {
      $scope.aptAreaArr = [];
      $scope.loc.area = "";

      $scope.errorFlag = false;

      $scope.aptArearomise = $http.get($regionUrl + "/" +
          $scope.appType +
          "?state=" + koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&county=" + koEncode($scope.loc.county) +
          "&region=" + koEncode($scope.loc.region) +
          "&aptName=" + koEncode($scope.loc.aptName) +
          "&query=area", {cache:true})
        .success(function(response) {
          $scope.aptAreaArr = response;
          $scope.aptAreaArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      if (update) {
        $scope.upAll();
      }
    };


    // get region array
    $scope.getLandType = function(update) {
      $scope.landTypeArr = [];
      $scope.landUsedTypeArr = [];

      $scope.loc.landType = "";
      $scope.loc.landUsedType = "";

      $scope.errorFlag = false;

      $scope.getLandTypePromise = $http.get($regionUrl + "/" +
          $scope.appType +
          "?state=" + koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&county=" + koEncode($scope.loc.county) +
          "&region=" + koEncode($scope.loc.region) +
          "&query=type")
        .success(function(response) {
          $scope.landTypeArr = response;
          $scope.landTypeArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

       if (update) {
        $scope.upAll();
      }
    };

    // get region array
    $scope.getLandUsedType = function(update) {
      $scope.landUsedTypeArr = [];
      $scope.loc.landUsedType = "";

      $scope.errorFlag = false;

      $scope.landUsedTypePromise = $http.get($regionUrl + "/" +
          $scope.appType +
          "?state=" + koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&county=" + koEncode($scope.loc.county) +
          "&region=" + koEncode($scope.loc.region) +
          "&type=" + koEncode($scope.loc.landType) +
          "&query=usedType")
        .success(function(response) {
          $scope.landUsedTypeArr = response;
          $scope.landUsedTypeArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      if (update) {
        $scope.upAll();
      }
    };


    $scope.upAll = function() {
      $scope.getSales();
      $scope.getStat();

      $scope.isAptkind = $scope.getAptKind();
      $scope.isRentkind = $scope.getRentKind();
    };

    $scope.saleListCount = function() {

      if ($scope.saleArr == null) {
        return 0;
      }

      var s = Object.keys($scope.saleArr).length;


      if (s >= 500) {
        return "500+";
      }

      return s;
    };

    $scope.makeURL = function($baseUrl) {
      var url = $baseUrl +
        "/" + $scope.appType +
        "?state=" + koEncode($scope.loc.state) +
        "&city=" + koEncode($scope.loc.city) +
        "&county=" + koEncode($scope.loc.county) +
        "&region=" + koEncode($scope.loc.region);

      if ($scope.getAptKind()) {
        url += "&aptName=" + koEncode($scope.loc.aptName) +
          "&area=" + koEncode($scope.loc.area);
      }

      if ($scope.appType=='landsale') {
       url += "&type=" + koEncode($scope.loc.landType) +
          "&usedType=" + koEncode($scope.loc.landUsedType); 
      }

      if ($scope.getRentKind()) {
        url += "&monthlyType=" + koEncode($scope.loc.monthlyType);
      }

      url += "&startyear=" + $scope.loc.startYear +
        "&endyear=" + $scope.loc.endYear;

      return url;
    }

    $scope.getStat = function() {
      $scope.statArr = [];

      $scope.errorFlag = false;

      $scope.statPromise = $http.get($scope.makeURL($statUrl), {cache:true})
        .success(function(response) {
          $scope.statArr = response;
          $scope.updateGraph();
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };

    $scope.getSales = function() {
      $scope.saleArr = [];
      $scope.errorFlag = false;
      $scope.salePromise = $http.get($scope.makeURL($saleUrl), {cache:true})
        .success(function(response) {
          $scope.saleArr = response;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };

    // Show all for the initial screen
    $scope.upAll();
  }
);