<?php
$is_localhost = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');

if ($is_localhost) {
    $host = "localhost:3308"; 
    $user = "root";
    $pass = "";
    $dbname = "campuswear_db";
} else {
    $host = "sql111.infinityfree.com";
    $user = "if0_40891695";
    $pass = "Your_InfinityFree_Hosting_Password"; 
    $dbname = "if0_40891695_isc_applications";
}

$conn = new mysqli($host, $user, $pass, $dbname);

$conn->set_charset("utf8mb4");

$campuswear_conn = $conn; 
?>