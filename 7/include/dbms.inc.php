<?php

    $host = 'localhost';
    $user = 'root';
    $pass = 'pippo12';
    $db = 'LTDW_2026';

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

?>