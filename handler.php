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
if(CheckPostSet("content_field")){
    $content_field = GetPostField("content_field");
    if(isset($_FILES['file'])){
        $file_tmppath = $_FILES['file']['tmp_name'];
        $timestamp = time();
        $file_path = 'img/content/'.$timestamp.'/'.$_FILES['file']['name'];
        mkdir('img/content/'.$timestamp);
        CompressImage($file_tmppath,$file_path);
    } else {
        $file_path = "";
    }
    $sql = "
    INSERT INTO posts(poster_id, post, image)
    VALUES(?, ?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION["id"], $content_field, $file_path);
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
if(CheckPostSet("delete_comment_id")){
    $comment_id = GetPostField("delete_comment_id");
    $comment = new Comment($comment_id);
    if($comment->commenter->student_id !== $_SESSION["id"]){
        echo 'unauthorized';
        return;
    }
    $sql = "
    DELETE FROM comments WHERE id = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $comment_id);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("delete_post_id")){
    $post_id = GetPostField("delete_post_id");
    $post = new Post($post_id, $_SESSION["id"]);
    if($post->poster->student_id !== $_SESSION["id"]){
        echo 'unauthorized';
        return;
    }
    $sql = "
    DELETE FROM posts WHERE id = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $post_id);
    mysqli_stmt_execute($stmt);
    $sql = "
    DELETE FROM comments WHERE post_id = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $post_id);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("friend_request_id")){
    $friend_id = GetPostField("friend_request_id");
    $sql = "
    SELECT (SELECT COUNT(*) FROM friendship WHERE firstUser = ? AND friendedUser = ?)
    + (SELECT COUNT(*) FROM friendship_req WHERE target = ? AND destination = ?)
    + (SELECT COUNT(*) FROM friendship_req WHERE target = ? AND destination = ?)
    FROM DUAL;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $_SESSION["id"], $friend_id, $_SESSION["id"], $friend_id, $friend_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $count_result);
    mysqli_stmt_fetch($stmt);
    if($count_result > 0){
        echo "An error occurred.";
        return;
    }
    $sql = "
    INSERT INTO friendship_req(target, destination) VALUES(?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("cancel_request_id")){
    $friend_id = GetPostField("cancel_request_id");
    $sql = "
    SELECT (SELECT COUNT(*) FROM friendship WHERE firstUser = ? AND friendedUser = ?)
    + (SELECT COUNT(*) FROM friendship_req WHERE target = ? AND destination = ?)
    FROM DUAL;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $_SESSION["id"], $friend_id, $friend_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $count_result);
    mysqli_stmt_fetch($stmt);
    if($count_result > 0){
        echo "An error occurred.";
        var_dump($count_result);
        return;
    }
    $sql = "
    DELETE FROM friendship_req WHERE target = ? AND destination = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("remove_request_id")){
    $friend_id = GetPostField("remove_request_id");
    $sql = "
    SELECT (SELECT COUNT(*) FROM friendship WHERE firstUser = ? AND friendedUser = ?)
    + (SELECT COUNT(*) FROM friendship_req WHERE target = ? AND destination = ?)
    FROM DUAL;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $_SESSION["id"], $friend_id, $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $count_result);
    mysqli_stmt_fetch($stmt);
    if($count_result > 0){
        echo "An error occurred.";
        return;
    }
    $sql = "
    DELETE FROM friendship_req WHERE destination = ? AND target = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("remove_friend_id")){
    $friend_id = GetPostField("remove_friend_id");
    $sql = "
    SELECT COUNT(*) FROM friendship WHERE firstUser = ? AND friendedUser = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $count_result);
    mysqli_stmt_fetch($stmt);
    if($count_result == 0){
        echo "An error occurred.";
        return;
    }
    $sql = "
    DELETE FROM friendship WHERE firstUser = ? AND friendedUser = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    $sql = "
    DELETE FROM friendship WHERE firstUser = ? AND friendedUser = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $friend_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("accept_request_id")){
    $friend_id = GetPostField("accept_request_id");
    $sql = "
    SELECT (SELECT COUNT(*) FROM friendship WHERE firstUser = ? AND friendedUser = ?)
    + (SELECT COUNT(*) FROM friendship_req WHERE target = ? AND destination = ?)
    FROM DUAL;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $_SESSION["id"], $friend_id, $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $count_result);
    mysqli_stmt_fetch($stmt);
    if($count_result > 0){
        echo "An error occurred.";
        return;
    }
    $sql = "
    DELETE FROM friendship_req WHERE destination = ? AND target = ?;
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    $sql = "
    INSERT INTO friendship(firstUser, friendedUser) VALUES(?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $friend_id);
    mysqli_stmt_execute($stmt);
    $sql = "
    INSERT INTO friendship(firstUser, friendedUser) VALUES(?, ?);
    ";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $friend_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
}
if(CheckPostSet("search_keyword")){
    $keyword = GetPostField("search_keyword");
    PostUsers(SearchUser($keyword));
}
?>