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
class HouseSale { 
    public $fullLoc;
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
	
	// parsing full location to city, etc.
	function setDetailedCityNames() {
		//list ($this->city, $this->county, $this->region)
		list ($this->city, $this->county, $this->region, $this->region1, $this->region2)
			= split(" ", $this->fullLoc);
	}

	function parseCSV($year, $month, $data) {
        $this->fullLoc = trim($data[0]);
        $this->type = $data[1];
        $this->area = $data[2];
        $this->landArea = $data[3];
        $this->date = intval($data[4]); //11~20 .. use the first one
        $this->amount = str_replace( ',', '', $data[5]);
        $this->builtYear = $data[6];
        $this->avenue = $data[7];

		$this->year = $year;
		$this->month = $month;
}
	function insertDB($conn) {

		$stmt = $conn->prepare(
			"INSERT INTO HouseSale (fullLoc, type, area, landArea, 
				date, amount, builtYear, avenue, 
				year, month, city, county, region, 
				region1, region2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		$stmt->bind_param("ssiisiisiisssss", 
  			$this->fullLoc,
  			$this->type,
  			$this->area,
  			$this->landArea, 
  			$this->date,
  			$this->amount, 
  			$this->builtYear,
  			$this->avenue,
  			$this->year,
  			$this->month,
  			$this->city,
  			$this->county,
  			$this->region,
  			$this->region1,
  			$this->region2);

		// eecute SQL
		$stmt->execute();
		$stmt->close();
	}
	
	
	// Testing
    function toString() { 
		$this->setDetailedCityNames();

        return "fullLoc: " .  $this->fullLoc
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
