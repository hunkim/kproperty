'use strict';

var app = angular.module('myApp', ['cgBusy', 'nvd3']);

app.controller('customersCtrl',
  function($scope, $http, $location) {
    $scope.m2top = 3.30579;
    $scope.ptom2 = 0.30259;

    // API Host
    var $rhost = "http://k.kproperty.xyz";

    // API URLs
    var $regionUrl = $rhost + "/r.php";
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
      houserent: '단독-다가구 전/월세'
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
      $scope.getTop();
      $scope.getDelta();

      $scope.isAptkind = $scope.getAptKind();
    };

    $scope.makeURL = function($baseUrl, $delta) {
      var url = $baseUrl + "/" + $scope.appType + "?";
      if ($delta) {
        url += "delta=true&";
      }
      url += "state=" + koEncode($scope.loc.state) +
        "&city=" + koEncode($scope.loc.city) +
        "&county=" + koEncode($scope.loc.county) +
        "&year=" + $scope.loc.year;

      return url;
    }


    $scope.getDelta = function() {
      //  $scope.delta = [];
      $scope.errorFlag = false;
      $scope.deltaPromise = $http.get($scope.makeURL($top10Url, true))
        .success(function(response) {
          //$scope.topApi.updateWithData([]);
          $scope.deltaApi.updateWithData(response);
          $scope.deltaPromise = null;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };

    $scope.getTop = function() {
      //  $scope.top = [];
      $scope.errorFlag = false;
      $scope.topPromise = $http.get($scope.makeURL($top10Url, false))
        .success(function(response) {
          //$scope.topApi.updateWithData([]);
          $scope.topApi.updateWithData(response);
          $scope.topPromise = null;
        })
        .error(function(response) {
          $scope.errorFlag = true;
        });
    };


    $scope.deltaOptions = {
      chart: {
        type: 'discreteBarChart',
        height: 550,
        //    forceY: [-1000, 1000],
        margin: {
          top: 50,
          right: 50,
          bottom: 150,
          left: 100
        },
        x: function(d) {
          return d.label;
        },
        y: function(d) {
          return d.value;
        },
        showValues: true,
        valueFormat: function(d) {
          return d3.format(',.2f')(d);
        },
        duration: 100,
        xAxis: {
          axisLabel: '지역이름',
          "rotateLabels": 20,
        },
        yAxis: {
          axisLabel: 'm2당 가격차이(만원)',
          //  axisLabelDistance: -5,
          "showMaxMin": true
        }
      }
    };

    $scope.topOptions = {
      chart: {
        type: 'discreteBarChart',
        height: 550,
        margin: {
          top: 50,
          right: 50,
          bottom: 150,
          left: 100
        },
        x: function(d) {
          return d.label;
        },
        y: function(d) {
          return d.value;
        },
        showValues: true,
        valueFormat: function(d) {
          return d3.format(',.2f')(d);
        },
        duration: 100,
        xAxis: {
          axisLabel: '지역이름',
          "rotateLabels": 20,
        },
        yAxis: {
          axisLabel: 'm2당 가격(만원)',
          //  axisLabelDistance: -5,
          "showMaxMin": true
        }
      }
    };

    // Show all for the initial screen
    $scope.upAll();

  }
);
