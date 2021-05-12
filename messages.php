<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <title>Messages</title>
    <?php include "templates/headtags.html"?>
</head>
<body>
    <?php include "templates/header.html"?>
    <script>makeButtonActive("mesg");</script>
    <?php include "templates/footer.html"?>
</body>
</html>