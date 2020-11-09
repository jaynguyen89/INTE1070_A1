<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

require_once 'assets/libs/GoogleAuthenticator.php';
require_once 'assets/libs/phpmailer/Exception.php';
require_once 'assets/libs/phpmailer/PHPMailer.php';
require_once 'assets/libs/phpmailer/SMTP.php';
require_once 'db_config.php';
global $link;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$email = array_key_exists('email', $_POST) ? $_POST["email"] : null;
$password = array_key_exists('password', $_POST) ? $_POST["password"] : null;
$message = '';

if (strlen($email) == 0 || strlen($password) == 0 ||
    strlen(trim($email)) == 0 || strlen(trim($password)) == 0)
    $message = 'Email and Password cannot be empty!';

if (strlen($message) == 0) {
    $query = "SELECT * FROM users WHERE email='$email'";

    $result = mysqli_query($link, $query);
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($data != null) {
        $authenticator = new GoogleAuthenticator();

        if (!$data['secret_code']) {
            try {
                $secret = $authenticator->createSecret();
                $query = 'UPDATE users SET secret_code = \''.$secret.'\' WHERE email = \''.$data['email'].'\'';

                if (mysqli_query($link, $query)) {
                    $code = $authenticator->getCode($secret);

                    if (sendCodeToEmail($code, $data['email'])) {
                        $_SESSION['pending_email'] = $data['email'];
                        $_SESSION['code'] = $code;
                        header("location: verify_twofa.php");
                    }
                    else
                        $message = 'We ran into an issue while sending 2FA PIN to your email. Please retry login.';
                }
                else $message = 'An error occurred while initializing your Two-Factor Authentication setup. Please retry login.';
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        else {
            $code = $authenticator->getCode($data['secret_code']);
            if (sendCodeToEmail($code, $data['email'])) {
                $_SESSION['pending_email'] = $data['email'];
                $_SESSION['code'] = $code;
                header("location: verify_twofa.php");
            }
            else
                $message = 'We ran into an issue while sending 2FA PIN to your email. Please retry login.';
        }
    } else
        $message = 'That email and password pair is not a match.';
}

function sendCodeToEmail($code, $email) {
    $message = file_get_contents('./assets/twofa_email_template.html');
    $message = str_replace('[CODE]', $code, $message);

    $mail = new PHPMailer();
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Host = 'smtp.google.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sample@gmail.com';
        $mail->Password = 'password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('s3493188@student.rmit.edu.au', 'Inte1070');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Inte1070 - 2FA verification';
        $mail->Body = $message;

        $mail->send();
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }

    return true;
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
        <h4><i class="fas fa-unlock-alt"></i> Basic 2FA Login</h4>
        <h6>Please enter your login credentials to begin.</h6>

        <div class="login-row">
            <form action="./twofa_basic.php" method="post" autocomplete="off">
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
                <button type="submit" name="submit" value="basic_2fa" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>
