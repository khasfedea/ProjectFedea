<?php
require_once "functions.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: index.php");
    exit;
}
$currentUser = new User($_SESSION["id"], $_SESSION["id"]);

if(CheckPostSet("post-button")) {
    $sql = "
    INSERT INTO posts(poster_id, post, image)
    VALUES(?, ?, ?);
    ";
    $postText = GetPostField("postText",true);
    $postImage = null;
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION["id"], $postText, $postImage);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
}
if(CheckPostSet("comment-button")) {
    $commentText = GetPostField("commentText", true);
    $postId = GetPostField("post-id", true);
    $sql = "
    INSERT INTO comments(commenter_id, comment, post_id)
    VALUES(?, ?, ?);
    ";
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION["id"], $commentText, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
}
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <title>Home Page</title>
    <?php include "templates/headtags.html"?>
</head>
<body>
    <?php include "templates/header.html"?>
    <script>makeButtonActive("home");</script>
    <?php
    echo '<div class="post-window">'.PHP_EOL;
    echo '<div class="identification">'.PHP_EOL;
    echo '<img class="avatar" src="'.$currentUser->avatarSrc.'"/>'.PHP_EOL;
    echo '<span class="name">'.$currentUser->firstName.' '.$currentUser->lastName.'</span>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    echo '<form method="POST" type="submit">'.PHP_EOL;
    echo '<textarea name="postText" placeholder="What\'s going on in your mind, '.$currentUser->firstName.'?"></textarea>'.PHP_EOL;
    echo '<div class="post-buttons">'.PHP_EOL;
    echo '<a href="#uploadPicture()">Add an Image</a>'.PHP_EOL;
    echo '<button name="post-button">Submit</button>'.PHP_EOL;
    echo '</form>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    ?>
    <?php
    PostThePosts($_SESSION["id"]);
    ?>
    <?php include "templates/footer.html"?>
</body>
</html>