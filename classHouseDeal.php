<?php 
/*
시군구 =>  제주특별자치도 제주시 화북일동
주택유형 => 단독
연면적(㎡) => 63.8
대지면적(㎡) => 165
계약일 => 1~10
거래금액(만원) => 10,400
건축년도 => 1973
도로명 => 동제원길
*/
class HouseDeal { 
    	public $fullCity;
	public $type;
	public $area;
	public $landArea;
	public $date;
	public $amount;
	public $builtYear;
	public $avenue;
	
	// year & month 
	public $year;
	public $month;

	// processed data
	public $city;
	public $county;
	public $region;
	public $region1;
	public $region2;
	
	function setDetailedCityNames() {
		//list ($this->city, $this->county, $this->region)
		list ($this->city, $this->county, $this->region, $this->region1, $this->region2)
			= split(" ", $this->fullCity);
	}

    	function toString() { 
		$this->setDetailedCityNames();

        	return "fullCity: " .  $this->fullCity
        	. "\n\t city: " .  $this->city
        	. "\n\t county: " .  $this->county
        	. "\n\t region: " .  $this->region
        	. "\n\t region1: " .  $this->region1
        	. "\n\t region2: " .  $this->region2
        	. "\n year: " .  $this->year
        	. "\n month: " .  $this->month
        	. "\n type: " .  $this->type
        	. "\n area: " .  $this->area
        	. "\n landArea: " .  $this->landArea
        	. "\n date: " .  $this->date
        	. "\n amount: " .  $this->amount
        	. "\n buildYear: " .  $this->builtYear
        	. "\n avenue: " .  $this->avenue;
    	} 
} 
