<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to HOME Page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

// Include database configuration file
require_once 'db_config.php';
global $link;

// Define variables and initialize with empty values
$email = "";
$password = "";
$email_err = "";
$password_err = "";

// Store Form Data
//if(isset($GET["email"]) && isset($GET["password"])){
$email = array_key_exists('email', $_POST) ? $_POST["email"] : null;
$password = array_key_exists('password', $_POST) ? $_POST["password"] : null;
$demo = array_key_exists('demo', $_POST) ? $_POST['demo'] : false;

// Validate credentials
// Prepare a select statement
$sql = "SELECT * FROM users WHERE email='$email'";

$result = $link->multi_query($sql);//mysqli_query($link, $sql);
$row = $link->store_result();//mysqli_fetch_array($result,MYSQLI_ASSOC);
$data = $row->fetch_row();
$count = $data == null ? 0 : count($data);//mysqli_num_rows($result);

if ($count > 0) {
    // Password is correct, so start a new session
    if (!isset($_SESSION)) session_start();

    // Store data in session variables
    $_SESSION["loggedin"] = true;
    $_SESSION["first_name"] = $data[1];
    $_SESSION["last_name"] = $data[2];
    $_SESSION["email"] = $email;
    $_SESSION['user_id'] = $data[4];
    $_SESSION['demo'] = $demo;
    $_SESSION['login_message'] = 'You have logged in successfully.';

    // Redirect user to welcome page
    header("location: home.php");
} else {
    // Display an error message if password is not valid
    $email_err = "No account found with that email.";
    $password_err = "The password you entered was not valid.";

}

// Close connection
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <div class="login-area">
        <h4>Welcome to E-commerce website</h4>



        <?php if (isset($_SESSION['demo'])) {
            echo '$email = ' . $email . '<br/>';
            echo '$password = ' . $password . '<br/>';
            echo '$result = ' . json_encode($result) . '<br/>';
            echo '$data = ' . json_encode($data) . '<br/>';
            echo '$count = ' . $count . '<br/>';
            echo '$_POST = ' . json_encode($_POST) . '<br/>';
            echo 'SQL Query: ' . $sql . '<br/>';
        }
        ?>

        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="login-row">
                    <?php if ($email_err || $password_err) { ?>
                        <div class="error">
                            <p><?php echo $email_err;?></p>
                            <p><?php echo $password_err;?></p>
                        </div>
                    <?php } ?>

                    <form action="./check_user.php" method="post" autocomplete="off">
                        <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-at" style="font-size: 1.5rem"></i>
                        </span>
                                    </div>
                                    <input name="email" value="<?php echo $email; ?>" type="text" class="form-control" placeholder="Email" autocomplete="none">
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
                                    <input name="password" value="<?php echo $password; ?>" type="password" class="form-control" placeholder="Password" autocomplete="none">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12" style="margin-bottom: 1rem;">
                            <div class="form-check">
                                <input type="checkbox" checked="<?php echo $demo; ?>" class="form-check-input" name="demo" id="demo">
                                <label class="form-check-label" for="demo">For demo purpose</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-6 col-sm-12">
                <div class="login-row text-center">
                    <h6>Or select the following Login with<br/>Two-Factor Authentication.</h6>
                    <div class="icon-bar">
                        <a href="twofa_basic.php" data-toggle="tooltip" data-placement="top" title="Email-based 2FA">
                            <i class="far fa-envelope"></i>
                            <p style="font-size: 1rem; color: black; text-decoration: none;">Mail-based</p>
                        </a>
                        <a href="twofa_advance.php" data-toggle="tooltip" data-placement="top" title="Authenticator App">
                            <i class="fas fa-mobile-alt"></i>
                            <p style="font-size: 1rem; color: black; text-decoration: none;">App-based</p>
                        </a>
                        <a href="twofa_basic.php" data-toggle="tooltip" data-placement="top" title="SMS-based 2FA">
                            <i class="fas fa-sms"></i>
                            <p style="font-size: 1rem; color: black; text-decoration: none;">SMS-based</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="goto-recaptcha">
            <h4>New user? Create an account now using:</h4>
            <br/>

            <a class="btn btn-primary" href="recaptcha_v2.php">Recaptcha V2</a>
            <a class="btn btn-primary" href="recaptcha_v3.php">Recaptcha V3</a>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>
