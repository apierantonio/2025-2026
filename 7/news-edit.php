<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'include/dbms.inc.php';
    require_once 'include/template2.inc.php';
        require_once 'include/auth.inc.php';


    $main = new Template('skins/admin/dtml/main');

    $status = $_REQUEST['status'] ?? 0;

    switch ($status) {
        case 0:

            $body = new Template('skins/admin/dtml/tables');
            $body->setContent("pagetitle", "News");

            $result = $conn->query("SELECT * FROM news ORDER BY publication_date DESC");
            if($conn->error) {
                die("Query error: " . $conn->error);
            }
            while($row = $result->fetch_assoc()) {
                $body->setContent('id', $row['id']);
                $body->setContent('title', $row['title']);
                $body->setContent('publication_date', $row['publication_date']);
            }
            
            break;
        case 1:
                $body = new Template('skins/admin/dtml/news-edit');
                $result = $conn->query("SELECT * FROM news WHERE id = {$_GET['id']}");
                if($conn->error) {
                    die("Query error: " . $conn->error);
                }

                if($row = $result->fetch_assoc()) {
                    $body->setContent('id', $row['id']);
                    $body->setContent('title', $row['title']);
                    $body->setContent('body', $row['body']);
                    $body->setContent('publication_date', date('Y-m-d', strtotime($row['publication_date'])));
                } else {
                    die("News not found");
                }
             
            break;
        case 2:
            $body = new Template('skins/admin/dtml/news-edit');

            $result = $conn->query("UPDATE news SET title = '{$_POST['title']}', body = '{$_POST['body']}', publication_date = '{$_POST['publication_date']}' WHERE id = {$_GET['id']}");
            if($conn->error) {
                die("Query error: " . $conn->error);
            }
            $result = $conn->query("SELECT * FROM news WHERE id = {$_GET['id']}");
            if($conn->error) {
                die("Query error: " . $conn->error);
            }

            if($row = $result->fetch_assoc()) {
                $body->setContent('id', $row['id']);
                $body->setContent('title', $row['title']);
                $body->setContent('body', $row['body']);
                $body->setContent('publication_date', date('Y-m-d', strtotime($row['publication_date'])));
            } else {
                die("News not found");
            }
             

            break;
        default:
            die("Invalid status");
    }

    

    
    

    $main->setContent("body", $body->get());
    $main->close();

?>