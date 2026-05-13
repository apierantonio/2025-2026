<?php
    session_start();


    print_r($_SESSION);

    function encodePassword($password) {
        return $password;
    }

    if (!isset($_SESSION['user'])) {

        /* username e password inviati dal form di login */

        $result = $conn->query("SELECT * FROM users WHERE username = '{$_POST['username']}' AND password = '" .encodePassword($_POST['password']). "'");
        if ($conn->error) {
            die("Query failed: " . $conn->error);
        }

        if ($result->num_rows == 1) {
            // Login successful
            $user = $result->fetch_assoc();

            $result->free();
            $result = $conn->query("SELECT roles_services.script 
                from users_roles 
                left join roles_services 
                ON roles_services.role_id = users_roles.role_id 
                WHERE users_roles.username = '{$_POST['username']}';");

            if ($conn->error) {
                die("Query failed: " . $conn->error);
            }   
            while ($row = $result->fetch_assoc()) {
                $user['services'][$row['script']] = true;
            }
            
            $_SESSION['user']= $user;
        
            } else {
            // Login failed
            echo "Invalid username or password.";
            exit;
        }
    }

    /* utente gia in sessione o appena autenticato */

    $script = basename($_SERVER['SCRIPT_NAME']);

    if (!isset($_SESSION['user']['services'][$script])) {
        echo "Access denied.";
        exit;
    }


?>