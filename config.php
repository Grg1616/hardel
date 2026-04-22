<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "hardel"; 

$conn = mysqli_connect($hostname, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>