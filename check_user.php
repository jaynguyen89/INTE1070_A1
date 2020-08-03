<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to HOME Page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

// Include database configuration file
require_once "db_config.php";

// Define variables and initialize with empty values
$email = "";
$password = "";
$email_err = "";
$password_err = "";

// Store Form Data
//if(isset($GET["email"]) && isset($GET["password"])){
$email = $_POST["email"];
$password = $_POST["password"];

// Validate credentials
// Prepare a select statement
$sql = "SELECT * FROM users where email='$email'";

$result = mysqli_query($link, $sql); // $link is obtained
$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
$count = mysqli_num_rows($result);
if($count > 1){
    // Password is correct, so start a new session
    session_start();

    // Store data in session variables
    $_SESSION["loggedin"] = true;
    $_SESSION["first_name"] = $row['first_name'];
    $_SESSION["last_name"] = $row['last_name'];
    $_SESSION["email"] = $email;

    // Redirect user to welcome page
    header("location: home.php");
} else{
    // Display an error message if password is not valid
    $email_err = "No account found with that email.";
    $password_err = "The password you entered was not valid.";

}

// Close connection
mysqli_close($link);

if($email_err !="" || $password_err !=""){
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>User Login</title>
        <link rel="stylesheet"
              href=https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css >
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <style>
            .page-style{
                margin: 20px;
            }
        </style>
    </head>
    <body>
    <div class="page-style">
        <h2>Welcome to E-Commerce Site</h2>
    </div>
    <div class="page-style">
        <h4>User Login</h4>
    </div>
    <div class="page-style">
        <h4>Error Occured!</h4>
    </div>
    <div class="page-style">
        <h4><?php echo $email_err;?></h4>
    </div>
    <div class="page-style">
        <h4><?php echo $password_err;?></h4>
    </div>
    <div class="page-style">
        <form action="check_user.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control" id="email" placeholder="Enter your registered Email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Enter your Password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
    </body>
    </html>
    <?php
}
?>