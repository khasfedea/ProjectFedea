<?php
require_once "functions.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: index.php");
    exit;
}
if(CheckPostSet("like")){
    $post_id = GetPostField("post_id");
    $post = new Post($post_id, $_SESSION["id"]);
    $post->likePost();
    echo $post->getLikeCount();
}
?>