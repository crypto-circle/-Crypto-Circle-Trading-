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
   GET SELECTED PLAN
===================================== */

$selectedPlan = isset($_GET["plan"])
    ? (int) $_GET["plan"]
    : 0;


/* =====================================
   INVESTMENT PLANS
===================================== */

$plans = [

    100 => "Starter Plan",
    250 => "Growth Plan",
    400 => "Advanced Plan",
    500 => "Premium Plan",
    650 => "Professional Plan",
    1000 => "Platinum Plan",
    2000 => "Elite Plan"

];


/* =====================================
   VALIDATE PLAN
===================================== */

if (!isset($plans[$selectedPlan])) {

    header("Location: dashboard.php");
    exit;

}


$planName = $plans[$selectedPlan];


/* =====================================
   WALLET ADDRESS
===================================== */

$walletAddress =
    "<p id="depositAddress">
    bc1quehtlkycrau4kvc67nx0neme6r7dzvgcgswhxh
</p>

<button type="button" onclick="copyAddress()">Copy Address</button>

<script>
function copyAddress() {
    const address = document.getElementById("depositAddress").innerText;

    navigator.clipboard.writeText(address).then(() => {
        alert("Address copied successfully!");
    });
}
</script>";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Deposit Wallet</title>


<style>

* {

    box-sizing: border-box;

}


body {

    margin: 0;

    min-height: 100vh;

    font-family: Arial, Helvetica, sans-serif;

    background: #0b0e11;

    color: white;

}


.header {

    padding: 18px 6%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    background: #181a20;

    border-bottom: 1px solid #2b3139;

}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

}


.logo {

    width: 45px;

    height: 45px;

    display: flex;

    justify-content: center;

    align-items: center;

    background: #f0b90b;

    color: #111;

    border-radius: 10px;

    font-size: 23px;

    font-weight: bold;

}


.brand h1 {

    margin: 0;

    font-size: 20px;

}


.back-btn {

    padding: 10px 15px;

    color: #f0b90b;

    text-decoration: none;

    border: 1px solid #f0b90b;

    border-radius: 7px;

}


/* MAIN */

.container {

    max-width: 700px;

    margin: auto;

    padding: 50px 20px;

}


/* TITLE */

.title {

    text-align: center;

    margin-bottom: 30px;

}


.title h2 {

    font-size: 32px;

    margin-bottom: 10px;

}


.title p {

    color: #848e9c;

}


/* WALLET CARD */

.wallet-card {

    padding: 30px;

    background: #181a20;

    border: 1px solid #2b3139;

    border-radius: 15px;

}


/* INFO ROW */

.info-row {

    display: flex;

    justify-content: space-between;

    padding: 18px 0;

    border-bottom: 1px solid #2b3139;

}


.info-label {

    color: #848e9c;

}


.info-value {

    font-weight: bold;

}


.amount {

    color: #f0b90b;

}


/* ADDRESS BOX */

.address-section {

    margin-top: 30px;

    padding: 25px;

    text-align: center;

    background: #0b0e11;

    border: 1px solid #343a43;

    border-radius: 12px;

}


.address-section h3 {

    margin-top: 0;

    color: #f0b90b;

}


.wallet-address {

    margin: 20px 0;

    padding: 18px;

    overflow-wrap: anywhere;

    background: #181a20;

    border: 1px solid #f0b90b;

    border-radius: 8px;

    color: #f0b90b;

    font-family: monospace;

    font-size: 14px;

}


/* NOTICE */

.notice {

    padding: 15px;

    margin-top: 20px;

    text-align: left;

    background: rgba(240,185,11,0.08);

    border-left: 4px solid #f0b90b;

    border-radius: 6px;

    color: #d1d4dc;

    line-height: 1.6;

    font-size: 13px;

}


/* BUTTON */

.dashboard-btn {

    display: block;

    width: 100%;

    margin-top: 25px;

    padding: 16px;

    text-align: center;

    text-decoration: none;

    background: #f0b90b;

    color: #111;

    border-radius: 8px;

    font-weight: bold;

}


/* MOBILE */

@media (max-width: 600px) {

    .info-row {

        flex-direction: column;

        gap: 7px;

    }


    .wallet-card {

        padding: 20px;

    }

}

</style>

</head>


<body>


<header class="header">


    <div class="brand">

        <div class="logo">
            ₿
        </div>

        <h1>
            Crypto Circle Trading
        </h1>

    </div>


    <a
        href="invest.php?plan=<?php echo $selectedPlan; ?>"
        class="back-btn"
    >
        ← Back
    </a>


</header>



<main class="container">


    <section class="title">

        <h2>
            Deposit Wallet
        </h2>

        <p>
            Deposit information for your selected plan.
        </p>

    </section>



    <section class="wallet-card">


        <div class="info-row">

            <span class="info-label">
                Selected Plan
            </span>

            <span class="info-value">

                <?php
                echo htmlspecialchars($planName);
                ?>

            </span>

        </div>



        <div class="info-row">

            <span class="info-label">
                Selected Amount
            </span>

            <span class="info-value amount">

                $

                <?php
                echo number_format(
                    $selectedPlan,
                    2
                );
                ?>

            </span>

        </div>



        <div class="address-section">


            <h3>
                Wallet Address
            </h3>


            <div class="wallet-address">

                <?php
                echo htmlspecialchars(
                    $walletAddress
                );
                ?>

            </div>


            <div class="notice">

                <strong>
                    Account Transaction Record
                </strong>

                <br><br>

                Please verify the payment address carefully before initiating a transaction.
            </div>


        </div>



        <a
            href="dashboard.php"
            class="dashboard-btn"
        >
            Return to Dashboard
        </a>


    </section>


</main>


</body>

</html>