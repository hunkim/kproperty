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
}


	
