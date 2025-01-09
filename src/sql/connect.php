<?php
$servername = "localhost";
$username = "DBUSER";
$password = "DBPW";
$dbname = "DBNAME";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306, "/var/lib/mysql/mysql.sock");

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8');
?>
