<?php
session_start();
require_once "functions.php";
$email_reg = $password_reg = $confirm_password_reg = $firstName_reg = $lastName_reg = $id_reg = "";
$register_err = "";
// Processing form data when form is submitted
if(CheckPostSet("register")) {
    try {
        $error = false;
        $email = GetPostField("email_reg", true, "email");
        $firstName = GetPostField("firstName_reg",true);
        $lastName = GetPostField("lastName_reg",true);
        $id = GetPostField("id_reg", true, "student_id");
        $password = GetPostField("password_reg", true);
        $confirmPassword = GetPostField("confirm_password_reg", true);
        if($password != $confirmPassword) {
            throw new Exception("Passwords do not match.");
        }
    } catch (Exception $e) {
        $register_err = $e->getMessage();
        $error = true;
    }
    if(!$error){
        $sql = "INSERT INTO users (student_id, firstName, lastName, email, password) VALUES (?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssss", $id, $firstName, $lastName, $email, $password);
            // Set parameters
            $password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                $_SESSION["loggedin"] = true;
                header("location: index.php");
            } else {
                $register_err = handleQueryError($link);
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
        // Close connection
        mysqli_close($link);
    }
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
<form type="submit" method="post" class="register-form">
    <table>
        <th>Sign Up</th>
        <tr>
            <td>First Name</td>
            <td><input value="<?php GetIfSet($firstName_reg) ?>" name="firstName_reg" type="text" placeholder="Enter your First Name" pattern="[A-Za-z]+" title="Enter a valid Name." required></td>
        </tr>
        <tr>
            <td>Last Name</td>
            <td><input value="<?php GetIfSet($lastName_reg) ?>" name="lastName_reg" type="text" placeholder="Enter your Last Name" pattern="[A-Za-z]+" title="Enter a valid Name." required></td>
        </tr>
        <tr>
            <td>E-Mail</td>
            <td><input value="<?php GetIfSet($email_reg) ?>" name="email_reg" type="email" placeholder="Enter your E-Mail" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Enter a valid e-mail." required></td>
        </tr>
        <tr>
            <td>Student ID</td>
            <td><input value="<?php GetIfSet($id_reg) ?>" name="id_reg" type="text" placeholder="Enter your Student ID" pattern="[0-9]+" title="Enter a valid Student ID." required></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input name="password_reg" type="password" placeholder="Enter your Password" pattern=".{8,}" title="Please enter 8 or more characters." required></td>
        </tr>
        <tr>
            <td>Confirm Password</td>
            <td><input name="confirm_password_reg" type="password" placeholder="Enter your Password again" title="Enter your password again." required></td>
        </tr>
        <tr>
            <td><button class="login-button" name="register">Sign Up</button></td>
            <td><a href="login.php" class="login-toggle">Log In</a>
        </tr>
    </table>
    <span class="invalid-feedback"><?php echo $register_err ?></span>
</form>
</div>
</div>
<?php include "templates/footer.html"?>
</body>
</html>