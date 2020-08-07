<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

require_once 'assets/libs/GoogleAuthenticator.php';
require_once 'db_config.php';
global $link;

$email = array_key_exists('email', $_POST) ? $_POST["email"] : null;
$password = array_key_exists('password', $_POST) ? $_POST["password"] : null;
$message = '';

if (strlen($email) == 0 || strlen($password) == 0 ||
    strlen(trim($email)) == 0 || strlen(trim($password)) == 0)
    $message = 'Email and Password cannot be empty!';

$action = 'login_form';
$qrCode = null;
if (strlen($message) == 0) {
    $query = "SELECT * FROM users WHERE email='$email'";

    $result = mysqli_query($link, $query);
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($data != null) {
        $authenticator = new GoogleAuthenticator();

        if (!$data['secret_code']) {
            $action = '2fa_setup';

            try {
                $secret = $authenticator->createSecret();
                $qrCode = $authenticator->getQRCodeGoogleUrl($data['email'], $secret, 'INTE1070_S3493188');

                $query = 'UPDATE users SET secret_code = \''.$secret.'\', qr_code = \''.$qrCode.'\' WHERE email = \''.$data['email'].'\'';

                if (mysqli_query($link, $query)) $_SESSION['pending_email'] = $data['email'];
                else $message = 'An error occurred while initializing your Two-Factor Authentication setup. Please retry login.';
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        else {
            $_SESSION['pending_email'] = $data['email'];
            header("location: verify_twofa.php");
        }
    } else $message = 'That email and password pair is not a match.';
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
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <div class="login-area">
        <h4><i class="fas fa-unlock-alt"></i> Advanced 2FA Login</h4>
        <?php if ($action == 'login_form') { ?>
            <h6>Please enter your login credentials to begin.</h6>
        <?php } else { ?>
            <h6>Please setup your QR code to continue.</h6>
        <?php } ?>

        <div class="login-row">
            <?php if ($action == 'login_form') { ?>
                <form action="./twofa_advance.php" method="post" autocomplete="off">
                    <?php if (array_key_exists('submit', $_POST) && strlen($message) != 0) { ?>
                        <p class="error"><?php echo $message; ?></p>
                    <?php } ?>

                    <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-at" style="font-size: 1.5rem"></i>
                                </span>
                                </div>
                                <input name="email" type="text" class="form-control" placeholder="Email" autocomplete="none">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-key" style="font-size: 1.5rem"></i>
                                    </span>
                                </div>
                                <input name="password" type="password" class="form-control" placeholder="Password" autocomplete="none">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="submit" value="advanced_2fa" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            <?php } else { ?>
                <div class="col-sm-12 text-center">
                    <img src="<?php echo $qrCode; ?>">
                    <div class="instruction">
                        <h3><i class="far fa-question-circle"></i> How to setup?</h3>
                        <p><i class="fas fa-circle"></i> Install Google Authenticator app on your mobile.</p>
                        <p><i class="fas fa-circle"></i> Launch the app, scan the QR image.</p>
                        <p><i class="fas fa-circle"></i> Remember to tap on `Add Account` button after you have scanned the QR image.</p>
                        <p><i class="fas fa-circle"></i> When you are done, click the `Continue` button.</p>
                    </div>
                    <a href="verify_twofa.php" class="btn btn-primary">Continue</a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>
