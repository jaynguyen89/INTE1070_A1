<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once 'assets/libs/GoogleAuthenticator.php';
require_once 'db_config.php';
global $link;

$user_id = $_SESSION['user_id'];

$query = 'SELECT * FROM users WHERE id='.$user_id;
$result = mysqli_query($link, $query);
$user = mysqli_fetch_array($result, MYSQLI_ASSOC);

$message = '';
if (array_key_exists('submit', $_POST) && $_POST['submit'] == 'user_details') {
    $email = array_key_exists('email', $_POST) ? $_POST['email'] : null;
    $phone = array_key_exists('phone', $_POST) ? $_POST['phone'] : null;

    if (preg_match("/^\d+$/", $phone) && strlen($phone) == 10) {
        $phone = '61'.substr($phone, 1);
        $query = 'UPDATE users SET email = \''.$email.'\', phone = \''.$phone.'\' WHERE id = '.$user['id'];

        if (mysqli_query($link, $query)) {
            $message = 'success';

            $query = 'SELECT * FROM users WHERE id='.$user_id;
            $result = mysqli_query($link, $query);
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
        }
        else $message = 'An error happened while updating your security details. Please retry.';
    }
    else $message = 'Phone Number must follow the format.';
}

$qrCode = null;
if (array_key_exists('tfa_form', $_POST)) {
    $authenticator = new GoogleAuthenticator();
    try {
        $secret = $_POST['tfa_form'] == 'new_2fa' ? $authenticator->createSecret() : (
            $user['secret_code'] ? $user['secret_code'] : $authenticator->createSecret()
        );
        $qrCode = $authenticator->getQRCodeGoogleUrl($user['email'], $secret, 'INTE1070_S3493188');

        $query = 'UPDATE users SET secret_code = \''.$secret.'\', qr_code = \''.$qrCode.'\' WHERE id = '.$user['id'];
        if (mysqli_query($link, $query)) {
            $user['qr_code'] = $qrCode;
            $message = 'success';
        }
        else $message = 'We ran into issue while initializing your Two-Factor Token. Please retry.';
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

mysqli_close($link);
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
    <script src="assets/custom.js"></script>
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <?php if ($message && array_key_exists('submit', $_POST)) { ?>
        <div class="alert <?php echo $message == 'success' ? 'alert-success' : 'alert-danger'; ?>" style="margin-top: 1rem;">
            <?php echo $message == 'success' ? 'Your security details have been saved successfully.' : $message; ?>
        </div>
    <?php } ?>

    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h4>Update your security settings for Two-Factor Authentification</h4>

    <div class="form-group" style="margin: 2.5rem auto;">
        <form action="security_setting.php" method="post">
            <div class="col-sm-6" style="margin-bottom: 0.5rem;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-at" style="font-size: 1.5rem"></i>
                            </span>
                        </div>
                        <input name="email" value="<?php echo $user['email']; ?>" type="text" class="form-control" placeholder="Email" />
                    </div>
                </div>
            </div>
            <div class="col-sm-6" style="margin-bottom: 0.5rem;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-phone" style="font-size: 1.5rem"></i>
                            </span>
                        </div>
                        <input name="phone" type="text" value="<?php echo '0'.substr($user['phone'], 2); ?>" class="form-control" placeholder="Phone Number" />
                    </div>
                    <p class="subtitle">Phone Number must follow this format: 0422333444. No space, no non-number characters.</p>
                </div>
            </div>
            <button class="btn btn-primary" type="submit" name="submit" value="user_details">Update</button>
        </form>
    </div>

    <div class="row">
        <?php if ($user['qr_code']) { ?>
            <h5>Your Two-Factor QR:</h5>

            <div style="height: 200px"><img src="<?php echo $user['qr_code']; ?>" class="img-fluid" /></div>
            <div class="instruction">
                <h6 style="text-align: left"><i class="far fa-question-circle"></i> How to setup?</h6>
                <p class="subtitle"><i class="fas fa-circle"></i> Install Google Authenticator app on your mobile.</p>
                <p class="subtitle"><i class="fas fa-circle"></i> Launch the app, scan the QR image.</p>
                <p class="subtitle"><i class="fas fa-circle"></i> Remember to tap on `Add Account` button after you have scanned the QR image.</p>
                <p class="subtitle"><i class="fas fa-circle"></i> Next time when you login, open the app and enter the 6-digit PIN.</p>
            </div>

            <button class="btn btn-success col-sm-4" onclick="confirmTwoFa(true)">Renew Two-Factor Authentification</button>
        <?php } else { ?>
            <h5>Enable Two-Factor Authentification:</h5>

            <div class="instruction">
                <h6 style="text-align: left"><i class="far fa-question-circle"></i> What is this for?</h6>
                <p class="subtitle"><i class="fas fa-circle"></i> Setup an Authorization Token.</p>
                <p class="subtitle"><i class="fas fa-circle"></i> When you login, just enter a 6-digit PIN to confirm your identity.</p>
                <p class="subtitle"><i class="fas fa-circle"></i> Simply click the button below to setup.</p>
            </div>

            <button class="btn btn-primary col-sm-4" onclick="confirmTwoFa()">Set up Two-Factor Authentification</button>
        <?php } ?>
    </div>

    <br/>
    <a role="button" class="btn btn-warning" href="home.php" style="width: 100px">Back</a>
</div>

<!--<div class="footer">S3493188 Le Kim Phuc Nguyen</div>-->
</body>
</html>