<?php
/**
 * This file is a part of Fedea Project (https://github.com/khasfedea/FedeaProject).
 * Copyright (C) 2021 Furkan Mudanyali, Team FEDEA.
 * 
 * This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU General Public License as published by  
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// Initialize the session
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}
// Include functions file
require_once "functions.php";
$email = $password = "";
$email_err = $password_err = $login_err = "";
// Define variables and initialize with empty values
// Processing form data when form is submitted
if(CheckPostSet('log-in')) {
    $email = GetPostField("email", true);
    $password = GetPostField("password", true);
    // Prepare query
    $sql = "SELECT student_id, email, password FROM users WHERE email = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        // Execute query
        if(mysqli_stmt_execute($stmt)) {
            // Store results
            mysqli_stmt_store_result($stmt);
            // Check if row exists, meaning email is correct.
            if(mysqli_stmt_num_rows($stmt) == 1) {
                // Bind results of the row to these variables. 
                mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password);
                if(mysqli_stmt_fetch($stmt)) {
                    // If password matches the hashed password
                    if(password_verify($password, $hashed_password)) {
                        // Start session and forward to main page.
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;
                        header("location: home.php");
                    } else {
                        $login_err = "Invalid e-mail or password.";
                    }
                }
            } else {
                $login_err = "Invalid e-mail or password.";
            }
        } else {
            $login_err = "Oops! Something went wrong. Please try again later.";
        }
    }       
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang=en>
<head>
    <title>Welcome to Fedea.</title>
    <?php include "templates/headtags.html"?>
</head>
<body>
<?php include "templates/login-header.html"?>
<div class="login-container">
<?php include "templates/login-left.html"?>
<div class="login-right">
<form type="submit" method="post" class="login-form">
    <table>
        <th>Log In</th>
        <tr>
            <td>E-Mail</td>
            <td><input value="<?php GetIfSet($email) ?>" name="email" type="text" placeholder="Enter your E-Mail" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Enter a valid e-mail." required></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input name="password" type="password" placeholder="Enter your Password" required></td>
        </tr>
        <tr>
            <td><a href="register.php" class="login-toggle">Sign Up</a>
            <td><button class="login-button" name="log-in">Log In</button></td>
        </tr>
    </table>
    <span class="invalid-feedback"><?php echo $login_err ?></span>
</form>
</div>
</div>
<?php include "templates/footer.html"?>
</body>
</html>