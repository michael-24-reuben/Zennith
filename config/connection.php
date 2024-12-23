<?php

// Load configuration from app.properties (you can use a .env parser or custom config loader)
$app_config = parse_ini_file(RESOURCE_PATH.'/config/app.properties');

$servername = $app_config['db.conn.host'];
$username = $app_config['db.conn.user'];
$password = $app_config['db.conn.password'];
$dbname = $app_config['db.conn.name'];
date_default_timezone_set("America/New_York");

$conn = new mysqli($servername, $username, $password, $dbname);
$_SESSION['conn'] = $conn;

if ($conn->connect_error) {
    echo "Error connecting to database";
    die("Connection Failed:" . $conn->connect_error);
}
