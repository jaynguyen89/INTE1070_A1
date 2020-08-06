<?php
session_set_cookie_params(['samesite' => 'None']);
header('Set-Cookie: cross-site-cookie=SID; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=HSID; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=SSID; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=APISID; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=SAPISID; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=OTZ; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=DV; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=SIDCC; SameSite=None; Secure');
header('Set-Cookie: cross-site-cookie=1P_JAR; SameSite=None; Secure');

require_once 'db_config.php';

session_start();
global $link;

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

$email = array_key_exists('email', $_POST) ? $_POST['email'] : null;
$password = array_key_exists('password', $_POST) ? $_POST['password'] : null;
$confirm = array_key_exists('confirm', $_POST) ? $_POST['confirm'] : null;
$fname = array_key_exists('fname', $_POST) ? $_POST['fname'] : null;
$lname = array_key_exists('lname', $_POST) ? $_POST['lname'] : null;
$token = array_key_exists('g-recaptcha-response', $_POST) ? $_POST['g-recaptcha-response'] : null;

$message = '';
if ($email == null || strlen($email) == 0)
    $message .= 'Email is missing.';

if ($password == null || strlen($password) == 0)
    $message .= '<br/>Password is missing';

if (!preg_match('@[A-Z]@', $password))
    $message .= '<br/>Password must contain at least 1 uppercase character.';

if (!preg_match('@[a-z]@', $password))
    $message .= '<br/>Password must contain at least 1 lowercase character.';

if (!preg_match('@[0-9]@', $password))
    $message .= '<br/>Password must contain at least 1 number.';

if ($password != $confirm)
    $message .= '<br/>Password and Confirm do not match.';

if ($fname == null || strlen($fname) == 0 ||
    $lname == null || strlen($lname) == 0 ||
    strlen(trim($fname)) == 0 || strlen(trim($lname)) == 0
)
    $message .= '<br/>First Name or Last Name is empty or missing.';

if (strlen($token) == 0)
    $message .= '<br/>Recaptcha is not verified.';

$response = '';
if (strlen($message) == 0) {
    $url = 'https://www.google.com/recaptcha/api/siteverify?response='.$token.'&secret=6LdHoboZAAAAAG8422PPFoCAPNYSn_I4fQ7P6dOf';
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (!$response) $message = 'Unable to validate your Recaptcha response. Please check network connection and retry.';
    else {
        $jresponse = json_decode($response);

        if (!property_exists($jresponse, 'success') || !$jresponse->success)
            $message = 'Your humanity validation was failed. Please retry the Recaptcha.';
    }


    if (strlen($message) == 0) {
        $query = "INSERT INTO users(email, first_name, last_name, password)
                  VALUES ('$email', '$fname', '$lname', '$password')";

        try {
            if (mysqli_query($link, $query)) $message = 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
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

    <!-- Integrate Google ReCaptcha API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="assets/custom.js" async defer></script>
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <div class="login-area">
        <h4><i class="fas fa-user-plus"></i> Create Account V2</h4>
        <h6>Please fill in your account details.</h6>

        <div class="login-row">
            <form action="./recaptcha_v2.php" method="post" autocomplete="off">
                <p id="validation" class="error"></p>
                <?php if (isset($_POST['submit']) && $message != '') { ?>
                    <p id="validation" class="<?php echo $message == 'success' ? 'success' : 'error' ?>">
                        <?php echo $message == 'success' ? 'Congrat! You have successfully registered a new account.<br/>Please <a href="index.html">click here</a> to login.' : $message; ?>
                    </p>
                <?php } ?>

                <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-at" style="font-size: 1.5rem"></i>
                            </span>
                            </div>
                            <input id="email" name="email" value="<?php echo $email; ?>" type="text" class="form-control" placeholder="Enter your email" oninput="validate()" autocomplete="none" />
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
                            <input id="password" name="password" value="<?php echo $password; ?>" type="password" class="form-control" placeholder="Set your password" oninput="validate()" autocomplete="none" />
                        </div>
                        <p class="subtitle">Minimum 10 characters. Must include uppercase, lowercase and numbers.</p>
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
                            <input id="confirm" name="confirm" value="<?php echo $confirm; ?>" type="password" class="form-control" placeholder="Confirm your password" oninput="validate()" autocomplete="none" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-id-badge" style="font-size: 1.5rem"></i>
                                </span>
                                    </div>
                                    <input id="fname" name="fname" value="<?php echo $fname; ?>" type="text" class="form-control" placeholder="First name" autocomplete="none" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-id-badge" style="font-size: 1.5rem"></i>
                                </span>
                                    </div>
                                    <input id="lname" name="lname" value="<?php echo $lname; ?>" type="text" class="form-control" placeholder="Last name" autocomplete="none" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                    <div class="g-recaptcha"
                         data-callback="onSuccess"
                         data-expired-callback="onExpiry"
                         data-error-callback="onError"
                         data-sitekey="6LdHoboZAAAAAAf7C-kp-ok1Zv6fxn_SL_9RKgaC"></div>
                    <p id="recaptcha-info" style="text-align: left"></p>

                    <input id="recaptcha-token" type="hidden" name="recaptcha-token" />
                </div>
                <button id="submit" name="submit" value="recaptcha-v2" type="submit" class="btn btn-primary" disabled>
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
                <button class="btn btn-warning" onclick="clearForm()">Reset</button>
            </form>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>