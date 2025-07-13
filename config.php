<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'airportvarna';
$port = 3308;

$conn = new mysqli('localhost', 'root', '', 'airportvarna', 3308); // порт 3307


if ($conn->connect_error) {
    die("Грешка при свързване с базата данни: " . $conn->connect_error);
}
?>
