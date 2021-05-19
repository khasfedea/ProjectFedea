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
require_once "config.php";
$link = $GLOBALS['link'];
// Just a fancy alias to fit in the naming convention
function CheckPostSet($name){
    return isset($_POST[$name]);
}
function GetIfSet($name){
    if(CheckPostSet($name)){
        return $_POST['firstName_reg'];
    }
    return "";
}
// Get defined POST value.
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
// To ease error handling in GetPostField
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
// Made primarily for duplicate rows, may be modified for general use.
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
    public function PrintPortfolio(){
        echo '<div class="portfolio-field">';
        if($this->authorized){
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
            echo '<div class="friends">';
            echo '<h2>Friends</h2>';
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
class LikedComment {
    public $liker;
    public $liked;

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
    }
    public function getLikeCount(){
        global $link;
        $sql = "SELECT liker FROM liked_comments WHERE liked = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);
    }
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
        if($this->commenter->student_id === $_SESSION["id"]){
            echo '<a class="remove-button" onclick="deleteComment('.$this->id.','.$this->post_id.')">Delete</a>'.PHP_EOL;
        }
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL;
    }
}
class Post {
    public $id;
    public $poster;
    public $post;
    public $image;
    public $timestamp;
    public $comments;

    public function __construct($id, $issuer){
        global $link;
        $this->id = $id;
        $sql = "SELECT poster_id, post, image, DATE_FORMAT(timestamp, '%e %b, %H:%i') FROM posts WHERE id = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        echo mysqli_error($link);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->poster, $this->post, $this->image, $this->timestamp);
        mysqli_stmt_fetch($stmt);
        $this->poster = new User($this->poster, $issuer);

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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
    }
    public function getLikeCount(){
        global $link;
        $sql = "SELECT liker FROM liked_posts WHERE liked = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);
    }
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
    public function getCommentCount(){
        return count($this->comments);
    }
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
        if($this->poster->student_id === $_SESSION["id"]){
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
function PostUsers($user_array){
    foreach($user_array as $user){
        echo '<div class="result-element">'.PHP_EOL;
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$user->avatarSrc.'"/>'.PHP_EOL;
        echo '<span class="name">'.$user->firstName.' '.$user->lastName.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo $user->GetFriendshipStatus();
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