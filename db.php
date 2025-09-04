<?php
$host = "localhost";
$user = "root";         
$password = "";          
$dbname = "dreamspace";


$conn = mysqli_connect($host, $user, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully";
?>
