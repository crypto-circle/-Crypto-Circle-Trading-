<?php

/*
=====================================
DATABASE CONFIGURATION
=====================================
*/

/*
For local development:

Host: localhost
Database: crypto_circle_trading
Username: root
Password: empty
*/

$host = "localhost";

$dbname = "crypto_circle_trading";

$dbuser = "root";

$dbpass = "";


/*
=====================================
DATABASE CONNECTION
=====================================
*/

try {

    $pdo = new PDO(

        "mysql:host=" . $host .
        ";dbname=" . $dbname .
        ";charset=utf8mb4",

        $dbuser,

        $dbpass

    );


    /*
    =====================================
    PDO ERROR MODE
    =====================================
    */

    $pdo->setAttribute(

        PDO::ATTR_ERRMODE,

        PDO::ERRMODE_EXCEPTION

    );


    /*
    =====================================
    FETCH MODE
    =====================================
    */

    $pdo->setAttribute(

        PDO::ATTR_DEFAULT_FETCH_MODE,

        PDO::FETCH_ASSOC

    );


    /*
    =====================================
    PREPARED STATEMENTS
    =====================================
    */

    $pdo->setAttribute(

        PDO::ATTR_EMULATE_PREPARES,

        false

    );


} catch (PDOException $e) {


    /*
    =====================================
    CONNECTION ERROR
    =====================================
    */

    die(

        "Database connection failed."

    );


}

?>