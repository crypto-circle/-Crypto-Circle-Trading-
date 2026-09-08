<?php

session_start();

require "db.php";


/* =====================================
   REDIRECT LOGGED-IN USERS
===================================== */

if (isset($_SESSION["user_id"])) {

    if (
        isset($_SESSION["role"]) &&
        $_SESSION["role"] === "admin"
    ) {

        header("Location: admin.php");
        exit;

    }

    header("Location: dashboard.php");
    exit;

}


/* =====================================
   VARIABLES
===================================== */

$message = "";
$messageType = "";


/* =====================================
   CREATE ACCOUNT
===================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $username =
        trim($_POST["username"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    /* =====================================
       VALIDATION
    ===================================== */

    if (
        empty($username) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        $message =
            "Please complete all fields.";

        $messageType =
            "error";

    }

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $messageType =
            "error";

    }

    elseif (
        strlen($username) < 3
    ) {

        $message =
            "Username must contain at least 3 characters.";

        $messageType =
            "error";

    }

    elseif (
        strlen($password) < 8
    ) {

        $message =
            "Password must contain at least 8 characters.";

        $messageType =
            "error";

    }

    elseif (
        $password !== $confirmPassword
    ) {

        $message =
            "Passwords do not match.";

        $messageType =
            "error";

    }

    else {

        try {

            /* =====================================
               CHECK USERNAME
            ===================================== */

            $checkUsername =
                $pdo->prepare("
                    SELECT id
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ");

            $checkUsername->execute([
                $username
            ]);


            if (
                $checkUsername->fetch()
            ) {

                $message =
                    "This username is already taken.";

                $messageType =
                    "error";

            }

            else {

                /* =====================================
                   CHECK EMAIL

                   Remove this section if your
                   users table does not have email.
                ===================================== */

                $checkEmail =
                    $pdo->prepare("
                        SELECT id
                        FROM users
                        WHERE email = ?
                        LIMIT 1
                    ");

                $checkEmail->execute([
                    $email
                ]);


                if (
                    $checkEmail->fetch()
                ) {

                    $message =
                        "This email address is already registered.";

                    $messageType =
                        "error";

                }

                else {

                    /* =====================================
                       HASH PASSWORD
                    ===================================== */

                    $passwordHash =
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );


                    /* =====================================
                       CREATE USER
                    ===================================== */

                    $createUser =
                        $pdo->prepare("
                            INSERT INTO users
                            (
                                username,
                                email,
                                password,
                                role
                            )
                            VALUES
                            (
                                ?,
                                ?,
                                ?,
                                'user'
                            )
                        ");


                    $createUser->execute([
                        $username,
                        $email,
                        $passwordHash
                    ]);


                    $userId =
                        (int) $pdo->lastInsertId();


                    /* =====================================
                       GENERATE ACCOUNT NUMBER
                    ===================================== */

                    $accountNumber =
                        "CCT" .
                        date("Y") .
                        random_int(
                            100000,
                            999999
                        );


                    /* =====================================
                       CREATE ACCOUNT
                    ===================================== */

                    $createAccount =
                        $pdo->prepare("
                            INSERT INTO accounts
                            (
                                user_id,
                                account_number,
                                account_type,
                                account_status,
                                balance
                            )
                            VALUES
                            (
                                ?,
                                ?,
                                'Trading Account',
                                'active',
                                0
                            )
                        ");


                    $createAccount->execute([
                        $userId,
                        $accountNumber
                    ]);


                    /* =====================================
                       SUCCESS MESSAGE
                    ===================================== */

                    $message =
                        "Your account has been created successfully. You can now sign in.";

                    $messageType =
                        "success";

                }

            }

        } catch (Exception $e) {

            $message =
                "Unable to create your account. Please try again.";

            $messageType =
                "error";

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
    Create Account | Crypto Circle Trading
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

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        #0b0e11;

    color:
        #ffffff;

}


/* =====================================
   BACKGROUND
===================================== */

.page {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 40px 20px;

    position: relative;

    overflow: hidden;

}


.page::before {

    content: "₿";

    position: fixed;

    right: -70px;

    top: 50%;

    transform:
        translateY(-50%);

    font-size: 500px;

    font-weight: bold;

    color:
        rgba(
            240,
            185,
            11,
            0.035
        );

    pointer-events: none;

}


/* =====================================
   CONTAINER
===================================== */

.container {

    width: 100%;

    max-width: 1050px;

    display: grid;

    grid-template-columns:
        1fr 1fr;

    background:
        #181a20;

    border:
        1px solid #2b3139;

    border-radius: 18px;

    overflow: hidden;

    position: relative;

    z-index: 1;

    box-shadow:
        0 20px 80px
        rgba(0, 0, 0, 0.45);

}


/* =====================================
   LEFT SIDE
===================================== */

.info-section {

    padding: 65px 55px;

    background:

        linear-gradient(
            135deg,
            rgba(240, 185, 11, 0.13),
            rgba(24, 26, 32, 0.96)
        );

    border-right:
        1px solid #2b3139;

}


.logo {

    width: 62px;

    height: 62px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 30px;

    background:
        #f0b90b;

    color:
        #111111;

    border-radius: 14px;

    font-size: 30px;

    font-weight: bold;

}


.info-section h1 {

    margin: 0 0 15px;

    font-size: 38px;

    line-height: 1.2;

}


.info-section h1 span {

    color:
        #f0b90b;

}


.info-section > p {

    color:
        #a9b0bc;

    line-height: 1.7;

    margin-bottom: 40px;

}


/* =====================================
   FEATURES
===================================== */

.features {

    display: flex;

    flex-direction: column;

    gap: 22px;

}


.feature {

    display: flex;

    gap: 15px;

    align-items: flex-start;

}


.feature-icon {

    min-width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background:
        rgba(
            240,
            185,
            11,
            0.12
        );

    color:
        #f0b90b;

    font-size: 19px;

}


.feature h3 {

    margin: 0 0 6px;

    font-size: 15px;

}


.feature p {

    margin: 0;

    color:
        #848e9c;

    font-size: 13px;

    line-height: 1.5;

}


/* =====================================
   FORM SECTION
===================================== */

.form-section {

    padding: 55px;

    background:
        #181a20;

}


.form-section h2 {

    margin: 0 0 10px;

    font-size: 30px;

}


.form-subtitle {

    margin: 0 0 30px;

    color:
        #848e9c;

}


/* =====================================
   ALERTS
===================================== */

.message {

    padding: 14px 16px;

    margin-bottom: 25px;

    border-radius: 8px;

    line-height: 1.5;

    font-size: 14px;

}


.success {

    background:
        rgba(
            14,
            203,
            129,
            0.12
        );

    border:
        1px solid #0ecb81;

    color:
        #0ecb81;

}


.error {

    background:
        rgba(
            246,
            70,
            93,
            0.12
        );

    border:
        1px solid #f6465d;

    color:
        #f6465d;

}


/* =====================================
   FORM
===================================== */

.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 9px;

    color:
        #d1d4dc;

    font-size: 14px;

}


.input-box {

    position: relative;

}


.input-box span {

    position: absolute;

    left: 15px;

    top: 50%;

    transform:
        translateY(-50%);

    color:
        #848e9c;

}


.form-group input {

    width: 100%;

    padding:
        15px
        15px
        15px
        46px;

    background:
        #0b0e11;

    color:
        white;

    border:
        1px solid #343a43;

    border-radius: 8px;

    font-size: 14px;

    outline: none;

    transition:
        0.2s;

}


.form-group input:focus {

    border-color:
        #f0b90b;

    box-shadow:
        0 0 0 3px
        rgba(
            240,
            185,
            11,
            0.08
        );

}


.form-group input::placeholder {

    color:
        #5e6673;

}


/* =====================================
   PASSWORD RULE
===================================== */

.password-note {

    margin-top: 8px;

    color:
        #848e9c;

    font-size: 12px;

}


/* =====================================
   BUTTON
===================================== */

.create-button {

    width: 100%;

    padding: 16px;

    margin-top: 8px;

    border: none;

    border-radius: 8px;

    background:
        #f0b90b;

    color:
        #111111;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition:
        0.2s;

}


.create-button:hover {

    background:
        #f8c31a;

    transform:
        translateY(-1px);

}


/* =====================================
   LOGIN LINK
===================================== */

.login-link {

    margin-top: 28px;

    text-align: center;

    color:
        #848e9c;

    font-size: 14px;

}


.login-link a {

    color:
        #f0b90b;

    text-decoration: none;

    font-weight: bold;

}


.login-link a:hover {

    text-decoration:
        underline;

}


/* =====================================
   FOOTER
===================================== */

.copyright {

    margin-top: 40px;

    text-align: center;

    color:
        #5e6673;

    font-size: 12px;

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 850px) {

    .container {

        grid-template-columns:
            1fr;

        max-width: 550px;

    }


    .info-section {

        padding: 45px 35px;

        border-right: none;

        border-bottom:
            1px solid #2b3139;

    }


    .form-section {

        padding: 45px 35px;

    }

}


@media (max-width: 500px) {

    .page {

        padding: 15px;

    }


    .info-section,

    .form-section {

        padding: 35px 25px;

    }


    .info-section h1 {

        font-size: 30px;

    }


    .form-section h2 {

        font-size: 26px;

    }

}

</style>

</head>


<body>


<div class="page">


<div class="container">


<!-- =====================================
     INFORMATION SECTION
===================================== -->

<section class="info-section">


    <div class="logo">
        ₿
    </div>


    <h1>

        Start your
        <span>
            crypto journey
        </span>

    </h1>


    <p>

        Create your Crypto Circle Trading account
        and access your personal trading dashboard.

    </p>



    <div class="features">


        <div class="feature">

            <div class="feature-icon">
                🔒
            </div>

            <div>

                <h3>
                    Secure Account Access
                </h3>

                <p>
                    Your password is securely encrypted
                    before being stored.
                </p>

            </div>

        </div>



        <div class="feature">

            <div class="feature-icon">
                ₿
            </div>

            <div>

                <h3>
                    Crypto Trading Dashboard
                </h3>

                <p>
                    Manage your account and view your
                    trading information in one place.
                </p>

            </div>

        </div>



        <div class="feature">

            <div class="feature-icon">
                ⚡
            </div>

            <div>

                <h3>
                    Quick Registration
                </h3>

                <p>
                    Create your account in just a few
                    simple steps.
                </p>

            </div>

        </div>


    </div>


</section>



<!-- =====================================
     FORM SECTION
===================================== -->

<section class="form-section">


    <h2>
        Create Account
    </h2>


    <p class="form-subtitle">

        Enter your details to create your account.

    </p>



    <?php if ($message !== ""): ?>


        <div
            class="
                message
                <?php echo htmlspecialchars($messageType); ?>
            "
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>


    <?php endif; ?>



    <form method="POST">


        <!-- USERNAME -->

        <div class="form-group">

            <label for="username">
                Username
            </label>


            <div class="input-box">

                <span>
                    👤
                </span>


                <input

                    type="text"

                    id="username"

                    name="username"

                    placeholder="Choose a username"

                    minlength="3"

                    value="<?php
                        echo htmlspecialchars(
                            $_POST["username"] ?? ""
                        );
                    ?>"

                    required

                >

            </div>

        </div>



        <!-- EMAIL -->

        <div class="form-group">

            <label for="email">
                Email Address
            </label>


            <div class="input-box">

                <span>
                    ✉
                </span>


                <input

                    type="email"

                    id="email"

                    name="email"

                    placeholder="Enter your email address"

                    value="<?php
                        echo htmlspecialchars(
                            $_POST["email"] ?? ""
                        );
                    ?>"

                    required

                >

            </div>

        </div>



        <!-- PASSWORD -->

        <div class="form-group">

            <label for="password">
                Password
            </label>


            <div class="input-box">

                <span>
                    🔒
                </span>


                <input

                    type="password"

                    id="password"

                    name="password"

                    placeholder="Create a strong password"

                    minlength="8"

                    autocomplete="new-password"

                    required

                >

            </div>


            <div class="password-note">

                Use at least 8 characters.

            </div>

        </div>



        <!-- CONFIRM PASSWORD -->

        <div class="form-group">

            <label for="confirm_password">
                Confirm Password
            </label>


            <div class="input-box">

                <span>
                    🔒
                </span>


                <input

                    type="password"

                    id="confirm_password"

                    name="confirm_password"

                    placeholder="Confirm your password"

                    minlength="8"

                    autocomplete="new-password"

                    required

                >

            </div>

        </div>



        <!-- CREATE BUTTON -->

        <button
            type="submit"
            class="create-button"
        >

            Create Account

        </button>


    </form>



    <!-- LOGIN -->

    <div class="login-link">

        Already have an account?

        <a href="index.php">

            Sign In

        </a>

    </div>



    <div class="copyright">

        © <?php echo date("Y"); ?>

        Crypto Circle Trading

    </div>


</section>


</div>


</div>


</body>

</html>