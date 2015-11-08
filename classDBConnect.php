<?php
class DBConnect {

	public $conn = null;

	function connect() {
	 $servername = "localhost";
	$username = "trend";
	 $password = "only!trend!";
	 $dbname = "trend";

		$this->conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($this->conn->connect_error) {
    			die("Connection failed: " . $this->conn->connect_error);
		} 

	}


	function close() {
		if ($this->conn != null) $this->conn->close();
	}

	function query($sql) {
		if ($this->conn->query($sql) === TRUE) {
    			echo "New record created successfully";
		} else {
    			echo "Error: " . $sql . "<br>" . $this->conn->error;
		}
	}

/***
+-----------+--------------+------+-----+---------+-------+
| Field     | Type         | Null | Key | Default | Extra |
+-----------+--------------+------+-----+---------+-------+
| fullCity  | varchar(255) | YES  |     | NULL    |       |
| type      | varchar(255) | YES  |     | NULL    |       |
| area      | varchar(255) | YES  |     | NULL    |       |
| landArea  | varchar(255) | YES  |     | NULL    |       |
| date      | varchar(255) | YES  |     | NULL    |       |
| amount    | bigint(20)   | YES  |     | NULL    |       |
| builtYear | varchar(255) | YES  |     | NULL    |       |
| avenue    | varchar(255) | YES  |     | NULL    |       |
| year      | int(11)      | YES  |     | NULL    |       |
| month     | int(11)      | YES  |     | NULL    |       |
| city      | varchar(255) | YES  |     | NULL    |       |
| county    | varchar(255) | YES  |     | NULL    |       |
| region    | varchar(255) | YES  |     | NULL    |       |
| region1   | varchar(255) | YES  |     | NULL    |       |
| region2   | varchar(255) | YES  |     | NULL    |       |
+-----------+--------------+------+-----+---------+-------+
*///
	function insert($deal) {
		$stmt = $this->conn->prepare(
"INSERT INTO Sale (fullCity, type, area, landArea, date, amount, builtYear, avenue, year, month, city, county, region, region1, region2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssiisiisiisssss", 
  $deal->fullCity,
  $deal->type,
  $deal->area,
  $deal->landArea, 
  $deal->date,
  $deal->amount, 
  $deal->builtYear,
  $deal->avenue,
  $deal->year,
  $deal->month,
  $deal->city,
  $deal->county,
  $deal->region,
  $deal->region1,
  $deal->region2);

	$stmt->execute();
	$stmt->close();
	
	}
}


	
