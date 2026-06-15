<?php


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipedo');

function db() {
    static $conn = null;
    static $failed = false;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    if ($failed) {
        return null;
    }


    if (!extension_loaded('mysqli') || !function_exists('mysqli_report') || !class_exists('mysqli')) {
        $failed = true;
        return null;
    }

    @mysqli_report(MYSQLI_REPORT_OFF);

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        $failed = true;
        $conn = null;
        return null;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

function db_ready() {
    $conn = db();

    if (!$conn) {
        return false;
    }

    $res = @$conn->query("SHOW TABLES LIKE 'users'");
    return $res && $res->num_rows > 0;
}
