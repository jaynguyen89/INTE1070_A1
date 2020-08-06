<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>INTE1070</title>

    <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/custom.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
    <script src="assets/custom.js"></script>
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <div class="login-area text-center">
        <h4><i class="fas fa-unlock-alt"></i> Verify Two-FA PIN</h4>
        <h6>Enter the 6-digits PIN you get in your email to verify.</h6>
        <p class="error" id="pin-error"></p>

        <?php echo json_encode($_POST); ?>

        <div class="row login-row" id="pin-row">
            <div class="col-md-2 col-sm-4">
                <input id="pin1" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(1)" />
            </div>
            <div class="col-md-2 col-sm-4">
                <input id="pin2" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(2)" />
            </div>
            <div class="col-md-2 col-sm-4">
                <input id="pin3" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(3)" />
            </div>
            <div class="col-md-2 col-sm-4">
                <input id="pin4" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(4)" />
            </div>
            <div class="col-md-2 col-sm-4">
                <input id="pin5" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(5)" />
            </div>
            <div class="col-md-2 col-sm-4">
                <input id="pin6" type="number" max="9" min="0" class="pin-cell" oninput="collectPin(6)" />
            </div>
        </div>

        <div id="waiting" style="margin-top: 5rem; display: none">
            <div class="spinner"></div>
            <h6>Validating...</h6>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>
