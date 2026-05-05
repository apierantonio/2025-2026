<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'include/dbms.inc.php';
    require_once 'include/template2.inc.php';

    $main = new Template('skins/nevia/dtml/main');

    $result = $conn->query("SELECT * FROM news WHERE id = {$_GET['id']}");
    if($conn->error) {
        die("Query error: " . $conn->error);
    }
    if($result->num_rows == 0) {
        die("News not found");
    }
    $row = $result->fetch_assoc();


    $body = new Template('skins/nevia/dtml/news-single');

    $body->setContent("title", $row['title']);
    $body->setContent("body", $row['body']);
    $body->setContent("publication_date", $row['publication_date']);

    $main->setContent("body", $body->get());
    $main->close();

?>