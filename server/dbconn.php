<?php

function DBconn() {
  // Persistent Connections
  // http://stackoverflow.com/questions/3332074/what-are-the-disadvantages-of-using-persistent-connection-in-pdo
  // http://www.php.net/manual/en/mysqli.persistconns.php
  $conn = new mysqli("p:localhost", "trend", "only!trend!", "rtrend");
	// Check connection
	if ($conn->connect_error) {
      if ($debug) {echo("Connection failed: " . $conn->connect_error);}
	  exit(0);
	}
}