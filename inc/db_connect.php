<?php
    $dsn = 'mysql:host=localhost;dbname=ufodisclosurebulgaria';
    $username = 'root';
    $password = '';

    try {
        $db = new PDO($dsn, $username, $password);
    }
	
	catch (PDOException $e) {
        $error_message = $e->getMessage();
        echo  'Connection error.:$error_message';
    }
?>