<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'include/dbms.inc.php';
    require_once 'include/template2.inc.php';

    $main = new Template('skins/admin/dtml/main');
    $body = new Template('skins/admin/dtml/news-add');

    $status = isset($_POST['status']) ? 1 : 0;

    switch($status) {
        case 0:
            break;
        case 1:
            $result = $conn->query("INSERT INTO news VALUssES (
                0,
                '{$_POST['title']}', 
                '', 
                '{$_POST['body']}', 
                '{$_POST['publication_date']}')");
            
            $main->setContent('result', $result);

            break;
    }


    $main->setContent("body", $body->get());
    $main->close();

?>