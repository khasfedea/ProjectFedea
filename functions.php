<?php
/** @file
 * This file is a part of Fedea Project (https://github.com/khasfedea/FedeaProject).
 * \copyright
 * Copyright (C) 2021 Furkan Mudanyali, Team FEDEA.
 * 
 * \license{
 * This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU General Public License as published by  
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but \n
 * WITHOUT ANY WARRANTY; without even the implied warranty of \n
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU \n
 * General Public License for more details. \n
 *
 * You should have received a copy of the GNU General Public License \n
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * }
 */
require_once "config.php";
/**
 * Make the sql link global across functions
 * 
 */
$link = $GLOBALS['link'];
/**
 * Just a fancy alias to fit in the naming convention
 * @param string POST field to check
 * @return bool The POST is set
 */
function CheckPostSet($name){
    return isset($_POST[$name]);
}
/**
 * Return POST value if POST field is set
 * @param string POST field to check
 * @return string POST value
 */
function GetIfSet($name){
    if(CheckPostSet($name)){
        return $_POST['firstName_reg'];
    }
    return "";
}
/**
 *  Get the value of the specified POST field.
 * @param string POST field
 * @param bool Is the field required to be filled
 * @param string custom row name for sql
 * @return string POST value
 */
function GetPostField($name, $isRequired=false, $rowName=""){
    global $link;
    $realName = translateRegisterVariable($name);
    $name = htmlspecialchars(trim($_POST[$name]));
    // If not specified, use name as rowname in sql query.
    if(empty($rowName)){
        $rowName = $name;
    }
    // Check $name if isRequired is set to true.
    if($isRequired && empty($name)){
        throw new Exception($realName." cannot be left blank.");
    }
    // We should have a proper $name if it survived all these checks.
    return $name;
}
/**
 * To ease error handling in GetPostField
 * @param string Error string to translate
 * @return string Correct error string
 */
function translateRegisterVariable($name){
    switch($name){
        case "email":
        case "email_reg":
            $realName = "E-Mail";
            break;
        case "firstName_reg":
            $realName = "First Name";
            break;
        case "lastName_reg":
            $realName = "Last Name";
            break;
        case "student_id":
        case "id_reg":
            $realName = "Student ID";
            break;
        default:
            $realName = $name;
            break;
    }
    return $realName;
}
/**
 * Made primarily for duplicate rows, may be modified for general use.
 * @param link SQL link
 * @return string Error message
 */
function handleQueryError($link){
    if (preg_match_all('/".*?"|\'.*?\'/', mysqli_error($link), $match)) {
        $error_msg = translateRegisterVariable(str_replace("'","",$match[0][1]))." is already in use.";
    } else {
        $error_msg = mysqli_error($link);
    }
    return $error_msg;
}
class User {
    public $student_id;
    public $firstName;
    public $lastName;
    public $dateOfBirth;
    public $email;
    public $avatarSrc;
    public $friends;
    public $privacy;
    public $authorized;
    public $branch;
    public $bio;

    /**
     * User constructor
     * 
     * @param string The student_id of the user to be created
     * @param string The student_id of who issues the user, for privacy
     * @return void
     */
    public function __construct($student_id, $issuer_id){
        global $link;
        $this->authorized = true;
        $this->student_id = $student_id;
        $sql = "
            SELECT firstName, lastName, avatarSrc, privacy, branch, bio FROM users WHERE student_id = ?;
        ";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->firstName, $this->lastName, $this->avatarSrc, $this->privacy, $this->branch, $this->bio);
        mysqli_stmt_fetch($stmt);
        if($this->privacy){
            $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ? AND friendedUser = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $student_id, $issuer_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            // Friendship status
            $this->authorized = mysqli_stmt_num_rows($stmt) == 1;
        }
        if($this->authorized){
            $this->friends = array();
            $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $row);
            while(mysqli_stmt_fetch($stmt)) {
                $this->friends[] = $row;
            }

            $sql = "SELECT dateOfBirth, email FROM users WHERE student_id = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $this->dateOfBirth, $this->email);
            mysqli_stmt_fetch($stmt);
        }
    }
    /**
     * Echo the friendship buttons according to the issuer_id
     * 
     * @param void
     * @return void
     */
    public function GetFriendshipStatus(){
        global $link;
        if($_SESSION["id"] == $this->student_id){
            return;
        }
        $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ? AND friendedUser = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            return '<a class="remove-friend" id="'.$this->student_id.'" onclick="removeFriend('.$this->student_id.')">Remove Friend</a>';
        }
        $sql = "SELECT target FROM friendship_req WHERE destination = ? AND target = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            return
            '<div class="friendship-row" id="'.$this->student_id.'">'.PHP_EOL.
            '<a class="accept-friend" id="'.$this->student_id.'" onclick="acceptFriendRequest('.$this->student_id.')">Accept</a>'.
            '<a class="remove-friend" id="'.$this->student_id.'" onclick="removeFriendRequest('.$this->student_id.')">Deny</a>'.
            '</div>'.PHP_EOL;
        }
        $sql = "SELECT target FROM friendship_req WHERE target = ? AND destination = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            return '<a class="remove-friend" id="'.$this->student_id.'" onclick="cancelFriendRequest('.$this->student_id.')">Cancel Friend Request</a>';
        }
        return '<a class="add-friend" id="'.$this->student_id.'" onclick="sendFriendRequest('.$this->student_id.')">Add Friend</a>';
    }
    /**
     * Echo user specific posts.
     * 
     * @param void
     * @return void
     */
    public function PrintUserPosts(){
        global $link;
        $posts = array();
        $sql = "SELECT id FROM posts WHERE poster_id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)){
            $post_ids[] = $row;
        }
        if(!empty($post_ids)){
            foreach($post_ids as $post_id){
                $posts[] = new Post($post_id, $_SESSION["id"]);
            }
            usort($posts, function($a, $b) {
                return $b->id <=> $a->id;
            });
            foreach($posts as $post){
                $post->printPost();
            }
        }
    }
    /**
     * Echo user portfolio according to the issuer_id
     * 
     * @param void
     * @return void
     */
    public function PrintPortfolio(){
        echo '<div class="portfolio-field">';
        if($this->authorized){
            echo '<div class="user-portfolio">';
            echo '<div class="portfolio-identification">'.PHP_EOL;
            echo '<img class="big-avatar" src="'.$this->avatarSrc.'"/>'.PHP_EOL;
            echo '<div class="name-field">';
            echo '<h1>'.$this->firstName.' '.$this->lastName.'</h1>';
            echo '<h2>'.$this->email.'</h2>';
            echo '</div>';
            echo '</div>';
            echo '<div class="additional-info">';
            echo '<h3>'.$this->branch.'</h3>';
            echo '<h3>'.$this->student_id.'</h3>';
            echo '</div>';
            echo '<div class="bio">';
            echo '<p>'.$this->bio.'</p>';
            echo '<div class="friend-button">';
            echo $this->GetFriendshipStatus();
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div class="friends">';
            echo '<div class="friend-count">';
            echo '<h2>Friends</h2><h2>'.(count($this->friends)-1).'</h2>';
            echo '</div>';
            foreach($this->friends as $friend_id){
                if ($friend_id == $this->student_id){
                    continue;
                }
                $friend = new User($friend_id, $_SESSION["id"]);
                echo '<div class="identification">'.PHP_EOL;
                echo '<img class="avatar" src="'.$friend->avatarSrc.'"/>'.PHP_EOL;
                echo '<a class="name" onclick="GoToUser(\''.$friend->student_id.'\')">'.$friend->firstName.' '.$friend->lastName.'</a>'.PHP_EOL;
                echo '</div>'.PHP_EOL;
            }
            echo '</div>';
            echo '</div>';
            $this->PrintUserPosts();
        }else{
            echo '<div class="user-portfolio">';
            echo '<div class="portfolio-identification">'.PHP_EOL;
            echo '<img class="big-avatar" src="'.$this->avatarSrc.'"/>'.PHP_EOL;
            echo '<h1>'.$this->firstName.' '.$this->lastName.'</h1>';
            echo '</div>';
            echo '<div class="additional-info">';
            echo '<h3>'.$this->branch.'</h3>';
            echo '<h3>'.$this->student_id.'</h3>';
            echo '</div>';
            echo '<div class="bio">';
            echo '<p>'.$this->bio.'</p>';
            echo '<div class="friend-button">';
            echo $this->GetFriendshipStatus();
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
}
class Message {
    public $id;
    public $sender;
    public $recipient;
    public $message;
    public $timestamp;
    /**
     * Message class constructor
     * 
     * @param int Message id
     * @return void
     */
    public function __construct($id){
        global $link;
        $this->id = $id;
        $sql = "SELECT id, message, DATE_FORMAT(timestamp, '%e %b, %H:%i'), sender_id, receiver_id FROM messages WHERE id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->id, $this->message, $this->timestamp, $this->sender, $this->receiver);
        mysqli_stmt_fetch($stmt);
    }
}
class LikedComment {
    public $liker;
    public $liked;
    /**
     * Liked comment constructor
     * 
     * @param int Message id
     * @return void
     */
    public function __construct($id){
        global $link;
        $sql = "SELECT liked FROM liked_comments WHERE liker = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->liked);
    }
}
class Comment {
    public $id;
    public $post_id;
    public $commenter;
    public $comment;
    public $timestamp;
    public $admins;
    /**
     * Comment class constructor
     * 
     * @param int Message id
     * @return void
     */
    public function __construct($id){
        global $link;
        $this->id = $id;
        $sql = "SELECT post_id, commenter_id, comment, DATE_FORMAT(timestamp, '%e %b, %H:%i') FROM comments WHERE id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->post_id, $this->commenter, $this->comment, $this->timestamp);
        mysqli_stmt_fetch($stmt);
        $this->commenter = new User($this->commenter, $_SESSION["id"]);
        $this->admins = array();
        $sql = "SELECT admin_id FROM admin;";
        $stmt = mysqli_prepare($link, $sql);
        echo mysqli_error($link);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)) {
            $this->admins[] = $row;
        }
    }
    /**
     * Get the like count of the message.
     * @param void
     * @return int The number of likes
     */
    public function getLikeCount(){
        global $link;
        $sql = "SELECT liker FROM liked_comments WHERE liked = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);
    }
    /**
     * Like/Unlike the comment according to the session id.
     * @param void
     * @return void 
     */
    public function likeComment(){
        global $link;
        $sql = "SELECT liker FROM liked_comments WHERE liked = ? AND liker = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $this->id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            $sql = "DELETE FROM liked_comments WHERE liker = ? AND liked = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->id);
            mysqli_stmt_execute($stmt);
        } else {
            $sql = "INSERT INTO liked_comments(liker, liked) VALUES(?, ?);";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->id);
            mysqli_stmt_execute($stmt);
        }
    }
    /**
     * Check if the session id user liked the comment or not.
     * @param void
     * @return string Like string
     */
    public function GetLikeStatus(){
        global $link;
        $sql = "SELECT liker FROM liked_comments WHERE liked = ? AND liker = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $this->id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            return "Unlike";
        } else {
            return "Like";
        }
    }
    /**
     * Echo the comment in the specific format.
     * @param void
     * @return void
     */
    public function printComment(){
        echo '<div class="message" id="'.$this->id.'">'.PHP_EOL;
        echo '<div class="poster">'.PHP_EOL.'<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$this->commenter->avatarSrc.'"/>'.PHP_EOL;
        echo '<a class="name" onclick="GoToUser(\''.$this->commenter->student_id.'\')">'.$this->commenter->firstName.' '.$this->commenter->lastName.'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<span class="timestamp">'.$this->timestamp.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="content">'.PHP_EOL.'<p>';
        echo $this->comment.'</p>'.PHP_EOL;
        echo '<div class="under-comment">'.PHP_EOL;
        echo '<div class="likes">'.PHP_EOL;
        echo '<span id="like-comment-'.$this->post_id.'-'.$this->id.'">'.$this->getLikeCount().'</span>'.PHP_EOL;
        echo '<a class="like-button" onclick="likeComment('.$this->post_id.','.$this->id.');">'.$this->GetLikeStatus().'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        if($this->commenter->student_id === $_SESSION["id"] || in_array($_SESSION["id"], $this->admins)){
            echo '<a class="remove-button" onclick="deleteComment('.$this->id.','.$this->post_id.')">Delete</a>'.PHP_EOL;
        }
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL;
    }
}
class Announcement {
    public $id;
    public $announcer;
    public $announcement;
    public $image;
    public $timestamp;
    public $admins;
    /**
     * Announcement class constructor
     * @param int Announcement id
     * @return void
     */
    public function __construct($id){
        global $link;
        $this->id = $id;
        $sql = "SELECT announcer_id, announcement, image, DATE_FORMAT(timestamp, '%e %b, %H:%i') FROM announcements WHERE id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->announcer, $this->announcement, $this->image, $this->timestamp);
        mysqli_stmt_fetch($stmt);
        $this->announcer = new User($this->announcer, $_SESSION["id"]);
        $this->admins = array();
        $sql = "SELECT admin_id FROM admin;";
        $stmt = mysqli_prepare($link, $sql);
        echo mysqli_error($link);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)) {
            $this->admins[] = $row;
        }
    }
    /**
     * Echo the announcement in a specific format,
     * put delete button if the session_id is in the admins array.
     * @param void
     * @return void
     */
    public function PrintAnnouncement(){
        global $link;
        echo '<div class="post" id="'.$this->id.'">'.PHP_EOL;
        echo '<div class="poster">'.PHP_EOL;
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$this->announcer->avatarSrc.'"/>'.PHP_EOL;
        echo '<a class="name" onclick="GoToUser(\''.$this->announcer->student_id.'\')">'.$this->announcer->firstName.' '.$this->announcer->lastName.'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<span class="timestamp">'.$this->timestamp.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="content">'.PHP_EOL.'<p>';
        echo $this->announcement.'</p>'.PHP_EOL;
        if(!empty($this->image)){
            echo '<img class="content-image" src="'.$this->image.'"/>'.PHP_EOL;
        }
        if(in_array($_SESSION["id"], $this->admins)){
            echo '<div class="under-post">';
            echo '<a class="remove-button" onclick="deleteAnnouncement('.$this->id.')">Delete</a>'.PHP_EOL;
            echo '</div>';
        }
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
}
class Post {
    public $id;
    public $poster;
    public $post;
    public $image;
    public $timestamp;
    public $comments;
    public $admins;
    /**
     * Post class constructor
     * @param int Post ID
     * @param string Issuer ID
     * @return void
     */
    public function __construct($id, $issuer){
        global $link;
        $this->id = $id;
        $sql = "SELECT poster_id, post, image, DATE_FORMAT(timestamp, '%e %b, %H:%i') FROM posts WHERE id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->poster, $this->post, $this->image, $this->timestamp);
        mysqli_stmt_fetch($stmt);
        $this->poster = new User($this->poster, $issuer);
        $this->comments = array();
        $comment_ids = array();
        $sql = "SELECT id FROM comments WHERE post_id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)) {
            $comment_ids[] = $row;
        }
        foreach($comment_ids as $comment_id){
            $this->comments[] = new Comment($comment_id);
        }
        $this->admins = array();
        $sql = "SELECT admin_id FROM admin;";
        $stmt = mysqli_prepare($link, $sql);
        echo mysqli_error($link);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)) {
            $this->admins[] = $row;
        }
    }
    /**
     * Returns the number of likes of the post
     * @param void
     * @return int Number of likes
     */
    public function getLikeCount(){
        global $link;
        $sql = "SELECT liker FROM liked_posts WHERE liked = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);
    }
    /**
     * Like/Unlike the post according to the issuer ID
     * @param void
     * @return void
     */
    public function likePost(){
        global $link;
        $sql = "SELECT liker FROM liked_posts WHERE liked = ? AND liker = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $this->id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            $sql = "DELETE FROM liked_posts WHERE liker = ? AND liked = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->id);
            mysqli_stmt_execute($stmt);
        } else {
            $sql = "INSERT INTO liked_posts(liker, liked) VALUES(?, ?);";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION["id"], $this->id);
            mysqli_stmt_execute($stmt);
        }
    }
    /**
     * Return the like status of the post.
     * @param void
     * @return string Like status
     */
    public function GetLikeStatus(){
        global $link;
        $sql = "SELECT liker FROM liked_posts WHERE liked = ? AND liker = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $this->id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            return "Unlike";
        } else {
            return "Like";
        }
    }
        /**
     * Returns the number of comments of the post
     * @param void
     * @return int Number of comments
     */
    public function getCommentCount(){
        return count($this->comments);
    }
    /**
     * Echo the post in a specific format.
     * @param void
     * @return void
     */
    public function printPost(){
        global $link;
        echo '<div class="post" id="'.$this->id.'">'.PHP_EOL;
        echo '<div class="poster">'.PHP_EOL.'<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$this->poster->avatarSrc.'"/>'.PHP_EOL;
        echo '<a class="name" onclick="GoToUser(\''.$this->poster->student_id.'\')">'.$this->poster->firstName.' '.$this->poster->lastName.'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<span class="timestamp">'.$this->timestamp.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL.'<div class="content">'.PHP_EOL.'<p>';
        echo $this->post.'</p>'.PHP_EOL;
        if(!empty($this->image)){
            echo '<img class="content-image" src="'.$this->image.'"/>'.PHP_EOL;
        }
        echo '<div class="under-post">';
        echo '<div class="likes">'.PHP_EOL;
        echo '<span id="like-count-'.$this->id.'">'.$this->getLikeCount().'</span>'.PHP_EOL;
        echo '<a class="like-button" onclick="likePost('.$this->id.')">'.$this->GetLikeStatus().'</a>'.PHP_EOL;
        echo '<span id="comment-count-'.$this->id.'">'.$this->getCommentCount().'</span>'.PHP_EOL;
        echo '<a class="comment-button" onclick="showComments('.$this->id.');">Comments</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        if($this->poster->student_id === $_SESSION["id"] || in_array($_SESSION["id"], $this->admins)){
            echo '<a class="remove-button" onclick="deletePost('.$this->id.')">Delete</a>'.PHP_EOL;
        }
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'<div class="messages" id="comment-section-'.$this->id.'">'.PHP_EOL;
        foreach($this->comments as $comment){
            $comment->printComment();
        }
        echo '<div class="post-comment" id="post-comment-'.$this->id.'">'.PHP_EOL;
        $currentUser = new User($_SESSION['id'], $_SESSION['id']);
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$currentUser->avatarSrc.'"/>'.PHP_EOL;
        echo '<span class="name">'.$currentUser->firstName.' '.$currentUser->lastName.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="area">'.PHP_EOL;
        echo '<textarea id="commentText-'.$this->id.'" placeholder="What is your opinion about this?"></textarea>'.PHP_EOL;
        echo '<button onclick="postComment('.$this->id.')">Submit</button>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL;
    }
}
/**
 * Echo every "Post" of the issuers friends.
 * @param string Issuer ID
 * @return void
 */
function PostThePosts($issuer_id){
    global $link;
    $posts = array();
    $friends = array();
    $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ?;";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $issuer_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row);
    while(mysqli_stmt_fetch($stmt)) {
        $friends[] = $row;
    }
    mysqli_stmt_close($stmt);
    foreach($friends as $friend){
        $sql = "SELECT id FROM posts WHERE poster_id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $friend);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $row);
        while(mysqli_stmt_fetch($stmt)){
            $post_ids[] = $row;
        }
    }
    if(!empty($post_ids)){
        foreach($post_ids as $post_id){
            $posts[] = new Post($post_id, $issuer_id);
        }
        usort($posts, function($a, $b) {
            return $b->id <=> $a->id;
        });
        foreach($posts as $post){
            $post->printPost();
        }
    }
}
/**
 * Echo every announcement.
 * @param void
 * @return void
 */
function PrintAnnouncements(){
    global $link;
    $announcement_ids = array();
    $sql = "SELECT id FROM announcements;";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row);
    while(mysqli_stmt_fetch($stmt)) {
        $announcement_ids[] = $row;
    }
    if(!empty($announcement_ids)){
        foreach($announcement_ids as $announcement_id){
            $announcements[] = new Announcement($announcement_id);
        }
        usort($announcements, function($a, $b) {
            return $b->id <=> $a->id;
        });
        foreach($announcements as $announcement){
            $announcement->PrintAnnouncement();
        }
    }
}
/**
 * Compress the given image, return error if input is not an image.
 * @param string Input path
 * @param string Output path
 * @param int Quality of the compression
 */
function CompressImage($input, $output, $quality = 90){
    $file_info = getimagesize($input);
    if($file_info === false){
        echo '<script>alert("Please upload an image.");</script>';
        return;
    }
    switch($file_info['mime']){
        case 'image/jpeg':
            $image = imagecreatefromjpeg($input);
            break;
        case 'image/png':
            $image = imagecreatefrompng($input);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($input);
            break;
        default:
            echo '<script>alert("Please upload an image.");</script>';
            return;
        }
        imagejpeg($image, $output, $quality);
}

/**
 * ImageManipulator Class by philBrown,
 * https://gist.github.com/philBrown/880506
 */
class ImageManipulator
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var resource
     */
    protected $image;

    /**
     * Image manipulator constructor
     * 
     * @param string $file OPTIONAL Path to image file or image data as string
     * @return void
     */
    public function __construct($file = null)
    {
        if (null !== $file) {
            if (is_file($file)) {
                $this->setImageFile($file);
            } else {
                $this->setImageString($file);
            }
        }
    }

    /**
     * Set image resource from file
     * 
     * @param string $file Path to image file
     * @return ImageManipulator for a fluent interface
     * @throws InvalidArgumentException
     */
    public function setImageFile($file)
    {
        if (!(is_readable($file) && is_file($file))) {
            throw new InvalidArgumentException("Image file $file is not readable");
        }

        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        list ($this->width, $this->height, $type) = getimagesize($file);

        switch ($type) {
            case IMAGETYPE_GIF  :
                $this->image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG :
                $this->image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG  :
                $this->image = imagecreatefrompng($file);
                break;
            default             :
                throw new InvalidArgumentException("Image type $type not supported");
        }

        return $this;
    }
    
    /**
     * Set image resource from string data
     * 
     * @param string $data
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function setImageString($data)
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        if (!$this->image = imagecreatefromstring($data)) {
            throw new RuntimeException('Cannot create image from data string');
        }
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }

    /**
     * Resamples the current image
     *
     * @param int  $width                New width
     * @param int  $height               New height
     * @param bool $constrainProportions Constrain current image proportions when resizing
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function resample($width, $height, $constrainProportions = true)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if ($constrainProportions) {
            if ($this->height >= $this->width) {
                $width  = round($height / $this->height * $this->width);
            } else {
                $height = round($width / $this->width * $this->height);
            }
        }
        $temp = imagecreatetruecolor($width, $height);
        imagecopyresampled($temp, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        return $this->_replace($temp);
    }
    
    /**
     * Enlarge canvas
     * 
     * @param int   $width  Canvas width
     * @param int   $height Canvas height
     * @param array $rgb    RGB colour values
     * @param int   $xpos   X-Position of image in new canvas, null for centre
     * @param int   $ypos   Y-Position of image in new canvas, null for centre
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function enlargeCanvas($width, $height, array $rgb = array(), $xpos = null, $ypos = null)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        
        $width = max($width, $this->width);
        $height = max($height, $this->height);
        
        $temp = imagecreatetruecolor($width, $height);
        if (count($rgb) == 3) {
            $bg = imagecolorallocate($temp, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($temp, 0, 0, $bg);
        }
        
        if (null === $xpos) {
            $xpos = round(($width - $this->width) / 2);
        }
        if (null === $ypos) {
            $ypos = round(($height - $this->height) / 2);
        }
        
        imagecopy($temp, $this->image, (int) $xpos, (int) $ypos, 0, 0, $this->width, $this->height);
        return $this->_replace($temp);
    }
    
    /**
     * Crop image
     * 
     * @param int|array $x1 Top left x-coordinate of crop box or array of coordinates
     * @param int       $y1 Top left y-coordinate of crop box
     * @param int       $x2 Bottom right x-coordinate of crop box
     * @param int       $y2 Bottom right y-coordinate of crop box
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function crop($x1, $y1 = 0, $x2 = 0, $y2 = 0)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if (is_array($x1) && 4 == count($x1)) {
            list($x1, $y1, $x2, $y2) = $x1;
        }
        
        $x1 = max($x1, 0);
        $y1 = max($y1, 0);
        
        $x2 = min($x2, $this->width);
        $y2 = min($y2, $this->height);
        
        $width = $x2 - $x1;
        $height = $y2 - $y1;
        
        $temp = imagecreatetruecolor($width, $height);
        imagecopy($temp, $this->image, 0, 0, $x1, $y1, $width, $height);
        
        return $this->_replace($temp);
    }
    
    /**
     * Replace current image resource with a new one
     * 
     * @param resource $res New image resource
     * @return ImageManipulator for a fluent interface
     * @throws UnexpectedValueException
     */
    protected function _replace($res)
    {
        if (!is_resource($res)) {
            throw new UnexpectedValueException('Invalid resource');
        }
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
        $this->image = $res;
        $this->width = imagesx($res);
        $this->height = imagesy($res);
        return $this;
    }
    
    /**
     * Save current image to file
     * 
     * @param string $fileName
     * @return void
     * @throws RuntimeException
     */
    public function save($fileName, $type = IMAGETYPE_JPEG)
    {
        $dir = dirname($fileName);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException('Error creating directory ' . $dir);
            }
        }
        
        try {
            switch ($type) {
                case IMAGETYPE_GIF  :
                    if (!imagegif($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_PNG  :
                    if (!imagepng($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_JPEG :
                default             :
                    if (!imagejpeg($this->image, $fileName, 95)) {
                        throw new RuntimeException;
                    }
            }
        } catch (Exception $ex) {
            throw new RuntimeException('Error saving image file to ' . $fileName);
        }
    }

    /**
     * Returns the GD image resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->image;
    }

    /**
     * Get current image resource width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get current image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
/**
 * Cut the given image to a square.
 * @param string Input path
 * @param string Output path
 */
function CutImage($input,$output){
    $im = new ImageManipulator($input);
    $centreX = round($im->getWidth() / 2);
    $centreY = round($im->getHeight() / 2);

    $minDimension = min($im->getWidth(), $im->getHeight());

    $x1 = $centreX - $minDimension/2;
    $y1 = $centreY - $minDimension/2;

    $x2 = $centreX + $minDimension/2;
    $y2 = $centreY + $minDimension/2;

    $im->crop($x1, $y1, $x2, $y2);
    $im->save($output);
}
/**
 * Search for the given keyword, and echo the result.
 * @param string keyword to search
 * @return users Array of user objects
 */
function SearchUser($keyword){
    global $link;
    $username = '%'.$keyword.'%';
    $user_ids = array();
    $users = array();
    $sql = "SELECT student_id FROM users WHERE CONCAT(firstName,' ',lastName) LIKE ? OR student_id = ?;";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $keyword);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row);
    while(mysqli_stmt_fetch($stmt)) {
        $user_ids[] = $row;
    }
    foreach($user_ids as $user_id){
        $users[] = new User($user_id, $_SESSION["id"]);
    }
    return $users;
}
/**
 * Echo the given user object array
 * @param users Array of user objects
 * @return void
 */
function PostUsers($user_array){
    foreach($user_array as $user){
        echo '<div class="result-element">'.PHP_EOL;
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$user->avatarSrc.'"/>'.PHP_EOL;
        echo '<a class="name" onclick="GoToUser(\''.$user->student_id.'\')">'.$user->firstName.' '.$user->lastName.'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo $user->GetFriendshipStatus();
        echo '</div>'.PHP_EOL;
    }
}
/** 
 * Echo the pending friend requests of the session user.
 * @param void
 * @return void
 */
function GetFriendRequests(){
    global $link;
    $user_ids = array();
    $users = array();
    $sql = "SELECT target FROM friendship_req WHERE destination = ?;";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s",  $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row);
    while(mysqli_stmt_fetch($stmt)) {
        $user_ids[] = $row;
    }
    foreach($user_ids as $user_id){
        if($user_id == $_SESSION["id"]){
            continue;
        }
        $users[] = new User($user_id, $_SESSION["id"]);
    }
    if(!empty($user_ids) > 0){
        PostUsers($users);
    } else {
        echo '<div class="result-element">'.PHP_EOL;
        echo "<p>You're all set!</p>".PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
}
class SearchResult{
    public $result;
    
    public function __construct($result_array){
        $this->result = $result_array;
    }

}
?>