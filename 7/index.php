<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'include/dbms.inc.php';
    require_once 'include/template2.inc.php';

    $main = new Template('skins/nevia/dtml/main');
    $body = new Template('skins/nevia/dtml/home');

    $main->setContent("body", $body->get());
    $main->close();

?>