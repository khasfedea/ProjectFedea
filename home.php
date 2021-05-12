<?php require_once "functions.php" ?>
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
    $myPost = new PostTemplate;
    $myPost->id = 1;
    $myPost->poster->SID = "20181701088";
    $myPost->poster->name = "Furkan Mudanyali";
    $myPost->poster->avatar = "img/avatars/furkan.jpg";
    $myPost->timestamp = "May 12";
    $myPost->content = "This is a test.";
    $myPost->image = "img/content/test.jpg";
    $myPost->likeCount = 255;
    $myPost->commentCount = 1;

    $myPost->comments[] = new Comments;
    $myPost->comments[0]->id = 1;
    $myPost->comments[0]->poster->name = "Emir Ozturk";
    $myPost->comments[0]->poster->SID = "20181701062";
    $myPost->comments[0]->poster->avatar = "img/avatars/emir.jpg";
    $myPost->comments[0]->timestamp = "May 12";
    $myPost->comments[0]->content = "gsdhiosdgh";
    $myPost->comments[0]->likeCount = 123;

    $myPost->printPost();
    ?>
    <?php include "templates/footer.html"?>
</body>
</html>