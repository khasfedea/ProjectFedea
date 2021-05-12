<?php
require_once "functions.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: index.php");
    exit;
}
$currentUser = getUser($_SESSION['id'], $link);
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
    echo '<div class="post-window">';
    echo '<div class="identification">';
    echo '<img class="avatar" src="'.$currentUser->avatar.'"/>';
    echo '<span class="name">'.$currentUser->firstName.' '.$currentUser->lastName.'</span>';
    echo '</div>';
    echo '<form name="post-status" method="POST">';
    echo '<textarea placeholder="What\'s going on in your mind, '.$currentUser->firstName.'?"></textarea>';
    echo '</form>';
    echo '<div class="post-buttons">';
    echo '<a href="#uploadPicture()">Add an Image</a>';
    echo '<button>Submit</button>';
    echo '</div></div>';
    ?>
    <?php
    $myPost = new PostTemplate;
    $myPost->id = 1;
    $myPost->poster->SID = "20181701088";
    $myPost->poster->name = "Furkan Mudanyali";
    $myPost->poster->avatar = "img/avatars/furkan.jpg";
    $myPost->timestamp = "04:58 - May 12";
    $myPost->content = "PS3umu actim.";
    $myPost->image = "img/content/test.jpg";
    $myPost->likeCount = 255;

    $myPost->comments[] = new Comments;
    $myPost->comments[0]->id = 1;
    $myPost->comments[0]->poster->name = "Emir Ozturk";
    $myPost->comments[0]->poster->SID = "20181701062";
    $myPost->comments[0]->poster->avatar = "img/avatars/emir.jpg";
    $myPost->comments[0]->timestamp = "06:12 - May 12";
    $myPost->comments[0]->content = "guzel ps3 cok begendim";
    $myPost->comments[0]->likeCount = 123;
    $myPost->comments[] = new Comments;
    $myPost->comments[1]->id = 2;
    $myPost->comments[1]->poster->name = "Michael Sipser";
    $myPost->comments[1]->poster->SID = "155";
    $myPost->comments[1]->poster->avatar = "img/avatars/sipser.jpg";
    $myPost->comments[1]->timestamp = "07:18 - May 12";
    $myPost->comments[1]->content = "Bunlarla ugrasacaginiza automata calisin";
    $myPost->comments[1]->likeCount = 235;

    $myPost->printPost();
    ?>
    <?php include "templates/footer.html"?>
</body>
</html>