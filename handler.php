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
require_once "functions.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    echo '<meta http-equiv="refresh" content="0;url=login.php">';
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
if(CheckPostSet("post_sent")){
    $content_field = GetPostField("content_field");
    $image_field = GetPostField("image_field");
    $sql = "
    INSERT INTO posts(poster_id, post, image)
    VALUES(?, ?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION["id"], $content_field, $image_field);
    mysqli_stmt_execute($stmt);
    $post_id = mysqli_insert_id($link);
    $post = new Post($post_id, $_SESSION["id"]);
    echo $post->printPost();
}
if(CheckPostSet("comment_sent")){
    $comment_field = GetPostField("comment_field");
    $post_id = GetPostField("post_id");
    $sql = "
    INSERT INTO comments(commenter_id, comment, post_id)
    VALUES(?, ?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION["id"], $comment_field, $post_id);
    mysqli_stmt_execute($stmt);
    $comment_id = mysqli_insert_id($link);
    $comment = new Comment($comment_id);
    echo $comment->printComment();
}
?>