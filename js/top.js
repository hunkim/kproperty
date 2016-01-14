'use strict';

var app = angular.module('myApp', ['cgBusy', "googlechart"]);

app.controller('customersCtrl',
  function($scope, $http, $location) {
    $scope.m2top = 3.30579;
    $scope.ptom2 = 0.30259;

    // API Host
    var $rhost = "http://r.kproperty.xyz";

    // API URLs
    var $regionUrl = $rhost + "/r2.php";
    var $top10Url = $rhost + "/top10.php";

    // app type
    $scope.appType = 'aptsale';

    // http error flag
    $scope.errorFlag = false;

    // default action
    $scope.stateArr = [
      "서울특별시", "부산광역시", "대구광역시", "인천광역시", "광주광역시", "대전광역시", "울산광역시",
      "세종특별자치시",
      "경기도", "강원도", "충청북도", "충청남도", "전라북도", "전라남도", "경상북도", "경상남도",
      "제주특별자치도"
    ];

    $scope.years = [2011, 2012, 2013, 2014, 2015];

    // loc selection
    $scope.loc = {
      state: "서울특별시",
      city: "",
      county: "",
      year: $scope.years[$scope.years.length - 1]
    };

    // stat and sale array
    $scope.top = [];
    $scope.delta = [];

    // county and region array
    $scope.cityArr = [];
    $scope.countyArr = [];

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

    $scope.amIActive = function(name) {
      if (name == $scope.appType) {
        return 'active';
      }

      return '';
    }

    $scope.setAppType = function(type) {
      $scope.appType = type;
      $scope.upAll();
      if ($scope.loc.county != "" && $scope.getAptKind()) {
        $scope.getAptName();
      }
    }

    // APT?
    $scope.getAptKind = function() {
      if ($scope.appType == 'housesale' || $scope.appType ==
        'houserent') {
        return false;
      }

      return true;
    }

    $scope.isAptkind = $scope.getAptKind();

    // recovering from network error
    $scope.reconnect = function() {
      $scope.errorFlag = false; //reset the flag and let's hope

      // check if county is loaded
      if ($scope.regionArr.length == 0 && $scope.loc.county != "") {
        $scope.getRegion();
      } else if ($scope.countyArr.length == 0 && $scope.loc.city != "") {
        $scope.getCounty();
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
      return "http://map.daum.net/?q='" + $sale.fullLoc + " " + $sale.avenue +
        "'";
    };


    var m2pFormat = function($area) {
      return ($area * $scope.m2top).toFixed(2);
    };

    $scope.pFormat = function($area) {
      return ($area * scope.p2m2).toFixed(2);
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
      }
    };

    // get county array
    $scope.getCity = function() {
      // clear region
      $scope.clearSelection('city');

      $scope.errorFlag = false;
      $scope.countyPromise = $http.get($regionUrl + "/" +
          $scope.appType + "?state=" +
          koEncode($scope.loc.state) +
          "&query=city")
        .success(function(response) {
          $scope.cityArr = response;
          $scope.cityArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      $scope.upAll();
    };

    // get region array
    $scope.getCounty = function() {
      $scope.clearSelection('county');

      $scope.errorFlag = false;
      $scope.regionPromise = $http.get($regionUrl + "/" +
          $scope.appType + "?state=" +
          koEncode($scope.loc.state) +
          "&city=" + koEncode($scope.loc.city) +
          "&query=county")
        .success(function(response) {
          $scope.countyArr = response;
          $scope.countyArr.unshift("");
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });

      // update sales information
      $scope.upAll();
    };

    $scope.upAll = function() {
      $scope.getMonthly();
      $scope.getTop();
      $scope.getDelta();

      $scope.isAptkind = $scope.getAptKind();
    };

    $scope.makeURL = function($baseUrl, $type) {
      var url = $baseUrl + "/" + $scope.appType + "?";
      
      if ($type!=null) {
        url += $type + "=true&";
      }

      url += "state=" + koEncode($scope.loc.state) +
        "&city=" + koEncode($scope.loc.city) +
        "&county=" + koEncode($scope.loc.county) +
        "&year=" + $scope.loc.year;

      return url;
    }

    $scope.monthlyObject = 
      {type:"ColumnChart",
       data: {
        "cols": [{id: "t", label: "Topping", type: "string"}, 
               {id: "s", label: "평당 가격차(만원)", type: "number"},
               {role: "style", type: "string"}],
        "rows": []}, 
        options: {'title': '월별 평당거래 가격'}
      }

    $scope.getMonthly = function() {
      $scope.errorFlag = false;
      $scope.monthlyPromise = $http.get($scope.makeURL($top10Url, 'monthly'), {cache:true})
        .success(function(response) {
          $scope.monthlyObject.data.rows = response;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };


    $scope.deltaObject = {};
    $scope.deltaObject.type = "ColumnChart";
    $scope.deltaObject.data = {
      "cols": [{id: "t", label: "Topping", type: "string"}, 
               {id: "s", label: "전년비교 평당 가격차(만원)", type: "number"},
               {role: "style", type: "string"}],
      "rows": []
    };

    $scope.deltaObject.options = {
      'title': '가격차이 많은곳',
     // "colors": ['#009900', '#0000FF', '#CC0000', '#DD9900']
    };


    $scope.getDelta = function() {
      //  $scope.delta = [];
      $scope.errorFlag = false;
      $scope.deltaPromise = $http.get($scope.makeURL($top10Url, 'delta'), {cache:true})
        .success(function(response) {
          $scope.deltaObject.data.rows = response;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };


    $scope.topObject = {};
    $scope.topObject.type = "ColumnChart";
    $scope.topObject.data = {
      "cols": [{id: "t", label: "Topping", type: "string"}, 
               {id: "s", label: "평당 가격(만원)", type: "number"},
               {role: "style", type: "string"}],
      "rows": []
    };

    $scope.topObject.options = {
      'title': '높은 가격'
    };

    
    $scope.getTop = function() {
      //  $scope.top = [];
      $scope.errorFlag = false;
      $scope.topPromise = $http.get($scope.makeURL($top10Url, null), {cache:true})
        .success(function(response) {
          $scope.topObject.data.rows = response;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };

    // Show all for the initial screen
    $scope.upAll();

  }
);
