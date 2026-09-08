<?php

session_start();

require "db.php";


/* =====================================
   IF ALREADY LOGGED IN
===================================== */

if (
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "admin"
) {

    header("Location: admin.php");
    exit;

}


/* =====================================
   VARIABLES
===================================== */

$message = "";

$username = "";


/* =====================================
   PROCESS ADMIN LOGIN
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username =
        trim($_POST["username"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (
        $username === "" ||
        $password === ""
    ) {

        $message =
            "Please enter your username and password.";

    } else {

        try {

            /* =====================================
               FIND ADMIN USER
            ===================================== */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    username,
                    password,
                    role
                FROM users
                WHERE username = ?
                LIMIT 1
            ");


            $stmt->execute([
                $username
            ]);


            $user =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            /* =====================================
               CHECK ACCOUNT
            ===================================== */

            if (!$user) {

                $message =
                    "Invalid administrator login.";

            }

            /*
               CHECK IF ACCOUNT IS ADMIN
            */

            elseif (
                strtolower(
                    $user["role"] ?? ""
                ) !== "admin"
            ) {

                $message =
                    "This account does not have administrator access.";

            }

            /*
               VERIFY PASSWORD
            */

            elseif (
                !password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                $message =
                    "Invalid administrator login.";

            }

            /*
               LOGIN SUCCESS
            */

            else {

                $_SESSION["user_id"] =
                    (int) $user["id"];


                $_SESSION["username"] =
                    $user["username"];


                $_SESSION["role"] =
                    "admin";


                header(
                    "Location: admin.php"
                );

                exit;

            }

        } catch (Exception $e) {

            $message =
                "Unable to connect to the user database.";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Administrator Login | Crypto Circle Trading
</title>


<style>

/* =====================================
   GENERAL
===================================== */

* {

    box-sizing: border-box;

}


body {

    margin: 0;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 25px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:

        radial-gradient(
            circle at top right,
            rgba(240,185,11,.15),
            transparent 30%
        ),

        #0b0e11;

    color: white;

}


/* =====================================
   LOGIN BOX
===================================== */

.login-container {

    width: 100%;

    max-width: 460px;

    padding: 40px;

    background: #181a20;

    border:
        1px solid #2b3139;

    border-radius: 16px;

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.4);

}


/* =====================================
   BRAND
===================================== */

.brand {

    text-align: center;

    margin-bottom: 30px;

}


.logo {

    width: 65px;

    height: 65px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f0b90b;

    color: #111;

    border-radius: 15px;

    font-size: 30px;

    font-weight: bold;

}


.brand h1 {

    margin: 0 0 10px;

    font-size: 25px;

}


.brand p {

    margin: 0;

    color: #848e9c;

}


/* =====================================
   FORM
===================================== */

.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    color: #d1d4dc;

    font-size: 14px;

}


.form-group input {

    width: 100%;

    padding: 15px;

    background: #0b0e11;

    border:
        1px solid #343a43;

    border-radius: 8px;

    color: white;

    outline: none;

    font-size: 15px;

}


.form-group input:focus {

    border-color: #f0b90b;

}


/* =====================================
   BUTTON
===================================== */

.login-button {

    width: 100%;

    padding: 15px;

    border: none;

    border-radius: 8px;

    background: #f0b90b;

    color: #111;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

}


.login-button:hover {

    opacity: .9;

}


/* =====================================
   MESSAGE
===================================== */

.message {

    padding: 14px;

    margin-bottom: 20px;

    background:
        rgba(246,70,93,.12);

    border:
        1px solid #f6465d;

    border-radius: 8px;

    color: #f6465d;

    text-align: center;

}


/* =====================================
   LINKS
===================================== */

.links {

    margin-top: 25px;

    text-align: center;

}


.links a {

    color: #f0b90b;

    text-decoration: none;

    font-size: 14px;

}


/* =====================================
   FOOTER
===================================== */

.footer {

    margin-top: 30px;

    text-align: center;

    color: #848e9c;

    font-size: 12px;

}


</style>

</head>


<body>


<div class="login-container">


<!-- BRAND -->

<div class="brand">


    <div class="logo">
        C
    </div>


    <h1>
        Administrator Login
    </h1>


    <p>
        Crypto Circle Trading
    </p>


</div>



<!-- ERROR MESSAGE -->

<?php if ($message !== ""): ?>

<div class="message">

    <?php
    echo htmlspecialchars($message);
    ?>

</div>

<?php endif; ?>



<!-- LOGIN FORM -->

<form method="POST">


<div class="form-group">

<label>

Administrator Username

</label>


<input
    type="text"
    name="username"
    placeholder="Enter administrator username"
    value="<?php
        echo htmlspecialchars($username);
    ?>"
    required
>


</div>



<div class="form-group">

<label>

Password

</label>


<input
    type="password"
    name="password"
    placeholder="Enter password"
    required
>


</div>



<button
    type="submit"
    class="login-button"
>

    Sign In as Administrator

</button>


</form>



<!-- BACK LINK -->

<div class="links">

<a href="index.php">

← Back to User Login

</a>

</div>



<div class="footer">

© <?php echo date("Y"); ?>

Crypto Circle Trading

</div>


</div>


</body>

</html>