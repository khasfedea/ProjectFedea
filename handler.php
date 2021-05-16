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
if(CheckPostSet("like_comment")){
    $post_id = GetPostField("post_id");
    $comment_id = GetPostField("comment_id");
    $post = new Post($post_id, $_SESSION["id"]);
    foreach($post->comments as $comment){
        if($comment->id == $comment_id){
            $comment->likeComment();
            echo $comment->getLikeCount();
        }
    }
}
?>