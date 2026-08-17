<?php

$host = "localhost";
$db   = "u353399544_wiuphp";
$user = "u353399544_wiuphp";
$pass = "!69^C=cIa";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
