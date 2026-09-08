<?php

session_start();

require "db.php";


/* =====================================
   CHECK LOGIN
===================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: index.php");
    exit;

}


/* =====================================
   PREVENT ADMIN ACCESS
===================================== */

if (
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "admin"
) {

    header("Location: admin.php");
    exit;

}


$userId = (int) $_SESSION["user_id"];


/* =====================================
   DEFAULT VARIABLES
===================================== */

$message = "";
$messageType = "";

$username = "User";
$accountNumber = "Not Available";
$accountType = "Trading Account";
$accountStatus = "Inactive";


/* =====================================
   GET USER INFORMATION
===================================== */

try {

    $userStmt = $pdo->prepare("
        SELECT
            id,
            username,
            password
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $userStmt->execute([$userId]);

    $user = $userStmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$user) {

        session_destroy();

        header("Location: index.php");
        exit;

    }


    $username = $user["username"];


} catch (Exception $e) {

    session_destroy();

    header("Location: index.php");
    exit;

}


/* =====================================
   GET ACCOUNT INFORMATION
===================================== */

try {

    $accountStmt = $pdo->prepare("
        SELECT
            account_number,
            account_type,
            account_status
        FROM accounts
        WHERE user_id = ?
        LIMIT 1
    ");

    $accountStmt->execute([$userId]);

    $account = $accountStmt->fetch(
        PDO::FETCH_ASSOC
    );


    if ($account) {

        $accountNumber =
            $account["account_number"]
            ?? $accountNumber;

        $accountType =
            $account["account_type"]
            ?? $accountType;

        $accountStatus =
            $account["account_status"]
            ?? $accountStatus;

    }


} catch (Exception $e) {

    /* Account information remains default */

}


/* =====================================
   CHANGE PASSWORD
===================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["change_password"])
) {

    $currentPassword =
        $_POST["current_password"] ?? "";

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    if (
        empty($currentPassword)
        ||
        empty($newPassword)
        ||
        empty($confirmPassword)
    ) {

        $message =
            "Please complete all password fields.";

        $messageType =
            "error";

    }

    elseif (
        strlen($newPassword) < 8
    ) {

        $message =
            "Your new password must contain at least 8 characters.";

        $messageType =
            "error";

    }

    elseif (
        $newPassword !== $confirmPassword
    ) {

        $message =
            "The new passwords do not match.";

        $messageType =
            "error";

    }

    elseif (
        !password_verify(
            $currentPassword,
            $user["password"]
        )
    ) {

        $message =
            "Your current password is incorrect.";

        $messageType =
            "error";

    }

    else {

        try {

            $newPasswordHash =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );


            $updatePasswordStmt =
                $pdo->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE id = ?
                ");


            $updatePasswordStmt->execute([
                $newPasswordHash,
                $userId
            ]);


            $message =
                "Password changed successfully.";

            $messageType =
                "success";


        } catch (Exception $e) {

            $message =
                "Unable to change your password.";

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
    Settings | Crypto Circle Trading
</title>


<style>

/* =====================================
   GENERAL
===================================== */

* {

    box-sizing: border-box;

}


html {

    scroll-behavior: smooth;

}


body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:

        radial-gradient(
            circle at 90% 10%,
            rgba(240, 185, 11, 0.12),
            transparent 30%
        ),

        radial-gradient(
            circle at 10% 80%,
            rgba(240, 185, 11, 0.05),
            transparent 35%
        ),

        #0b0e11;

    color: white;

}


/* =====================================
   HEADER
===================================== */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 18px 6%;

    background: #181a20;

    border-bottom:
        1px solid #2b3139;

}


.brand {

    display: flex;

    align-items: center;

    gap: 13px;

}


.logo {

    width: 52px;

    height: 52px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f0b90b;

    color: #111111;

    border-radius: 12px;

    font-size: 25px;

    font-weight: bold;

}


.brand h1 {

    margin: 0 0 5px;

    font-size: 21px;

}


.brand p {

    margin: 0;

    color: #848e9c;

    font-size: 13px;

}


.back-button {

    padding: 12px 18px;

    background: #f0b90b;

    color: #111111;

    text-decoration: none;

    border-radius: 7px;

    font-weight: bold;

}


/* =====================================
   NAVIGATION
===================================== */

.navigation {

    display: flex;

    overflow-x: auto;

    padding-left: 6%;

    background: #181a20;

    border-bottom:
        1px solid #2b3139;

}


.navigation a {

    padding: 18px 20px;

    color: #a9b0bc;

    text-decoration: none;

    white-space: nowrap;

    font-weight: bold;

}


.navigation a:hover {

    color: #f0b90b;

}


.navigation .active {

    color: #f0b90b;

    background:
        rgba(240, 185, 11, 0.06);

}


/* =====================================
   MAIN
===================================== */

main {

    max-width: 1100px;

    margin: auto;

    padding: 50px 25px;

}


.page-heading {

    margin-bottom: 35px;

}


.page-heading h2 {

    margin: 0 0 10px;

    font-size: 32px;

}


.page-heading p {

    margin: 0;

    color: #848e9c;

}


/* =====================================
   SETTINGS GRID
===================================== */

.settings-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 25px;

}


/* =====================================
   CARD
===================================== */

.card {

    padding: 28px;

    background: #181a20;

    border:
        1px solid #2b3139;

    border-radius: 14px;

}


.card.full-width {

    grid-column:
        1 / -1;

}


.card h3 {

    margin-top: 0;

    margin-bottom: 25px;

    color: #f0b90b;

}


.card-description {

    margin-top: -12px;

    margin-bottom: 25px;

    color: #848e9c;

    line-height: 1.6;

}


/* =====================================
   ACCOUNT INFORMATION
===================================== */

.info-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 16px 0;

    border-bottom:
        1px solid #2b3139;

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    color: #848e9c;

    font-size: 13px;

}


.info-value {

    text-align: right;

    font-weight: bold;

    word-break: break-word;

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

    color: #d1d4dc;

    font-size: 14px;

}


.form-group select,

.form-group input {

    width: 100%;

    padding: 14px;

    background: #0b0e11;

    color: white;

    border:
        1px solid #343a43;

    border-radius: 8px;

    outline: none;

}


.form-group select:focus,

.form-group input:focus {

    border-color: #f0b90b;

}


/* =====================================
   BUTTONS
===================================== */

.save-button {

    width: 100%;

    padding: 15px;

    border: none;

    border-radius: 8px;

    background: #f0b90b;

    color: #111111;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

}


.save-button:hover {

    opacity: 0.9;

}


.secondary-button {

    width: 100%;

    padding: 14px;

    background: transparent;

    border:
        1px solid #343a43;

    color: #f0b90b;

    border-radius: 8px;

    cursor: pointer;

    font-weight: bold;

}


/* =====================================
   ALERTS
===================================== */

.message {

    padding: 16px;

    margin-bottom: 25px;

    border-radius: 8px;

}


.success {

    background:
        rgba(14, 203, 129, 0.12);

    border:
        1px solid #0ecb81;

    color: #0ecb81;

}


.error {

    background:
        rgba(246, 70, 93, 0.12);

    border:
        1px solid #f6465d;

    color: #f6465d;

}


/* =====================================
   PREFERENCE MESSAGE
===================================== */

.preference-message {

    display: none;

    margin-top: 18px;

    padding: 13px;

    border-radius: 7px;

    background:
        rgba(14, 203, 129, 0.10);

    color: #0ecb81;

    font-size: 13px;

}


/* =====================================
   DANGER AREA
===================================== */

.danger-card {

    border-color:
        rgba(246, 70, 93, 0.35);

}


.danger-card h3 {

    color: #f6465d;

}


.danger-card p {

    color: #848e9c;

    line-height: 1.6;

}


.logout-button {

    display: inline-block;

    padding: 14px 20px;

    background: #f6465d;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

}


/* =====================================
   FOOTER
===================================== */

footer {

    padding: 30px;

    text-align: center;

    color: #848e9c;

    font-size: 13px;

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 800px) {

    .header {

        flex-direction: column;

        align-items: flex-start;

        gap: 16px;

    }


    .settings-grid {

        grid-template-columns:
            1fr;

    }


    .card.full-width {

        grid-column: auto;

    }

}


@media (max-width: 500px) {

    main {

        padding: 35px 15px;

    }


    .card {

        padding: 22px;

    }


    .info-row {

        flex-direction: column;

        gap: 7px;

    }


    .info-value {

        text-align: left;

    }

}

</style>

</head>


<body>


<!-- =====================================
     HEADER
===================================== -->

<header class="header">


    <div class="brand">

        <div class="logo">
            ₿
        </div>


        <div>

            <h1>
                Crypto Circle Trading
            </h1>

            <p>
                Account Settings
            </p>

        </div>

    </div>


    <a
        href="dashboard.php"
        class="back-button"
    >
        ← Dashboard
    </a>


</header>


<!-- =====================================
     NAVIGATION
===================================== -->

<nav class="navigation">

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="transactions.php">
        Transactions
    </a>

    <a href="transfer.php">
        Transfer
    </a>

    <a href="withdraw.php">
        Withdraw
    </a>

    <a href="wallet.php">
        Wallet
    </a>

    <a href="messages.php">
        Messages
    </a>

    <a href="profile.php">
        Profile
    </a>

    <a
        href="settings.php"
        class="active"
    >
        Settings
    </a>

</nav>


<!-- =====================================
     MAIN
===================================== -->

<main>


<section class="page-heading">

    <h2>
        Account Settings
    </h2>

    <p>
        Manage your account preferences and security settings.
    </p>

</section>


<?php if ($message !== ""): ?>

<div
    class="message <?php echo htmlspecialchars($messageType); ?>"
>

    <?php
    echo htmlspecialchars($message);
    ?>

</div>

<?php endif; ?>


<div class="settings-grid">


<!-- =====================================
     ACCOUNT INFORMATION
===================================== -->

<section class="card">


<h3>
    Account Information
</h3>


<div class="info-row">

    <span class="info-label">
        Username
    </span>

    <span class="info-value">

        <?php
        echo htmlspecialchars($username);
        ?>

    </span>

</div>


<div class="info-row">

    <span class="info-label">
        Account Number
    </span>

    <span class="info-value">

        <?php
        echo htmlspecialchars($accountNumber);
        ?>

    </span>

</div>


<div class="info-row">

    <span class="info-label">
        Account Type
    </span>

    <span class="info-value">

        <?php
        echo htmlspecialchars($accountType);
        ?>

    </span>

</div>


<div class="info-row">

    <span class="info-label">
        Account Status
    </span>

    <span class="info-value">

        <?php
        echo htmlspecialchars(
            ucfirst($accountStatus)
        );
        ?>

    </span>

</div>


</section>


<!-- =====================================
     DISPLAY PREFERENCES
===================================== -->

<section class="card">


<h3>
    Display Preferences
</h3>


<p class="card-description">

    Choose your preferred country, currency and language.
    These preferences are saved on this device.

</p>


<div class="form-group">

    <label for="country">

        Country

    </label>


    <select id="country">

        <option value="US">
            United States
        </option>

        <option value="NG">
            Nigeria
        </option>

        <option value="GB">
            United Kingdom
        </option>

        <option value="CA">
            Canada
        </option>

        <option value="DE">
            Germany
        </option>

        <option value="FR">
            France
        </option>

        <option value="ZA">
            South Africa
        </option>

        <option value="GH">
            Ghana
        </option>

        <option value="KE">
            Kenya
        </option>

        <option value="AU">
            Australia
        </option>

        <option value="JP">
            Japan
        </option>

        <option value="AE">
            United Arab Emirates
        </option>

    </select>

</div>


<div class="form-group">

    <label for="currency">

        Currency

    </label>


    <select id="currency">

        <option value="USD">
            USD — US Dollar
        </option>

        <option value="EUR">
            EUR — Euro
        </option>

        <option value="GBP">
            GBP — British Pound
        </option>

        <option value="NGN">
            NGN — Nigerian Naira
        </option>

        <option value="CAD">
            CAD — Canadian Dollar
        </option>

        <option value="AUD">
            AUD — Australian Dollar
        </option>

        <option value="ZAR">
            ZAR — South African Rand
        </option>

        <option value="GHS">
            GHS — Ghanaian Cedi
        </option>

        <option value="KES">
            KES — Kenyan Shilling
        </option>

        <option value="JPY">
            JPY — Japanese Yen
        </option>

        <option value="AED">
            AED — UAE Dirham
        </option>

    </select>

</div>


<div class="form-group">

    <label for="language">

        Language

    </label>


    <select id="language">

        <option value="en">
            English
        </option>

        <option value="fr">
            Français
        </option>

        <option value="es">
            Español
        </option>

        <option value="de">
            Deutsch
        </option>

    </select>

</div>


<button
    type="button"
    class="save-button"
    id="savePreferences"
>

    Save Preferences

</button>


<div
    class="preference-message"
    id="preferenceMessage"
>

    Preferences saved successfully.

</div>


</section>


<!-- =====================================
     PASSWORD
===================================== -->

<section class="card full-width">


<h3>
    Change Password
</h3>


<p class="card-description">

    Use a strong password that you do not use on other websites.

</p>


<form method="POST">


<div class="form-group">

    <label for="current_password">

        Current Password

    </label>


    <input

        type="password"

        id="current_password"

        name="current_password"

        autocomplete="current-password"

        required

    >

</div>


<div class="form-group">

    <label for="new_password">

        New Password

    </label>


    <input

        type="password"

        id="new_password"

        name="new_password"

        minlength="8"

        autocomplete="new-password"

        required

    >

</div>


<div class="form-group">

    <label for="confirm_password">

        Confirm New Password

    </label>


    <input

        type="password"

        id="confirm_password"

        name="confirm_password"

        minlength="8"

        autocomplete="new-password"

        required

    >

</div>


<button
    type="submit"
    name="change_password"
    class="save-button"
>

    Change Password

</button>


</form>


</section>


<!-- =====================================
     SIGN OUT
===================================== -->

<section class="card full-width danger-card">


<h3>
    Sign Out
</h3>


<p>

    You can safely sign out of your account when you have
    finished using this device.

</p>


<a
    href="logout.php"
    class="logout-button"
>

    Sign Out

</a>


</section>


</div>


</main>


<footer>

    © <?php echo date("Y"); ?>

    Crypto Circle Trading

</footer>


<script>

/* =====================================
   GET ELEMENTS
===================================== */

const countrySelect =
    document.getElementById("country");

const currencySelect =
    document.getElementById("currency");

const languageSelect =
    document.getElementById("language");

const savePreferencesButton =
    document.getElementById(
        "savePreferences"
    );

const preferenceMessage =
    document.getElementById(
        "preferenceMessage"
    );


/* =====================================
   LOAD SAVED SETTINGS
===================================== */

const savedCountry =
    localStorage.getItem(
        "dashboardCountry"
    );

const savedCurrency =
    localStorage.getItem(
        "dashboardCurrency"
    );

const savedLanguage =
    localStorage.getItem(
        "dashboardLanguage"
    );


if (savedCountry) {

    countrySelect.value =
        savedCountry;

}


if (savedCurrency) {

    currencySelect.value =
        savedCurrency;

}


if (savedLanguage) {

    languageSelect.value =
        savedLanguage;

}


/* =====================================
   SAVE PREFERENCES
===================================== */

savePreferencesButton.addEventListener(
    "click",
    function() {

        localStorage.setItem(
            "dashboardCountry",
            countrySelect.value
        );


        localStorage.setItem(
            "dashboardCurrency",
            currencySelect.value
        );


        localStorage.setItem(
            "dashboardLanguage",
            languageSelect.value
        );


        preferenceMessage.style.display =
            "block";


        setTimeout(
            function() {

                preferenceMessage.style.display =
                    "none";

            },
            3000
        );

    }
);


/* =====================================
   AUTO COUNTRY / CURRENCY SUGGESTION
===================================== */

const countryCurrencyMap = {

    US: "USD",

    NG: "NGN",

    GB: "GBP",

    CA: "CAD",

    DE: "EUR",

    FR: "EUR",

    ZA: "ZAR",

    GH: "GHS",

    KE: "KES",

    AU: "AUD",

    JP: "JPY",

    AE: "AED"

};


/* =====================================
   CHANGE CURRENCY WITH COUNTRY
===================================== */

countrySelect.addEventListener(
    "change",
    function() {

        const selectedCurrency =
            countryCurrencyMap[
                this.value
            ];


        if (selectedCurrency) {

            currencySelect.value =
                selectedCurrency;

        }

    }
);

</script>


</body>

</html>