<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'include/dbms.inc.php';
    require_once 'include/template2.inc.php';

    $main = new Template('skins/admin/dtml/main');
    $body = new Template('skins/admin/dtml/tables');
    $body->setContent("pagetitle", "News");

    $result = $conn->query("SELECT * FROM news ORDER BY publication_date DESC");
    if($conn->error) {
        die("Query error: " . $conn->error);
    }
    while ($row = $result->fetch_assoc()) {
        $body->setContent("id", $row['id']);
        $body->setContent("title", $row['title']);
        $body->setContent("publication_date", $row['publication_date']);
    }

    $main->setContent("body", $body->get());
    $main->close();

?>