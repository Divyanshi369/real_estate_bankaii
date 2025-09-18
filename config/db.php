<?php
// Database connection using mysqli

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'u348560133_real_estate';
$DB_PASS = getenv('DB_PASS') ?: 'Bankaii!#123';
$DB_NAME = getenv('DB_NAME') ?: 'u348560133_real_estate';
$DB_PORT = intval(getenv('DB_PORT') ?: 3306);

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset('utf8mb4');

function db(): mysqli {
    global $mysqli;
    return $mysqli;
}

?>


