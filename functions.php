<?php
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
    $name = trim($_POST[$name]);
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

    public function __construct($student_id, $issuer_id){
        global $link;
        $authorized = true;
        $this->student_id = $student_id;
        $sql = "
            SELECT firstName, lastName, avatarSrc, privacy FROM users WHERE student_id = ?;
        ";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $this->firstName, $this->lastName, $this->avatarSrc, $this->privacy);
        mysqli_stmt_fetch($stmt);
        if($this->privacy){
            $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ? AND friendedUser = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $student_id, $issuer_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            // Friendship status
            $authorized = mysqli_stmt_num_rows($stmt) == 1;
        }
        if($authorized){
            $this->friends = array();
            $sql = "SELECT friendedUser FROM friendship WHERE firstUser = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $this->friends);
            mysqli_stmt_fetch($stmt);

            $sql = "SELECT dateOfBirth, email FROM users WHERE student_id = ?;";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $this->dateOfBirth, $this->email);
            mysqli_stmt_fetch($stmt);
        }
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
        $sql = "SELECT post_id, commenter_id, comment, timestamp FROM comments WHERE id = ?;";
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
        $sql = "SELECT poster_id, post, image, timestamp FROM posts WHERE id = ?;";
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
        $sql = "SELECT liker FROM liked_posts WHERE liked = ?;";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->id);
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
    public function getCommentCount(){
        return count($this->comments);
    }
    public function printPost(){
        global $link;
        echo '<div class="post" id="'.$this->id.'">'.PHP_EOL;
        echo '<div class="poster">'.PHP_EOL.'<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$this->poster->avatarSrc.'"/>'.PHP_EOL;
        echo '<span class="name">'.$this->poster->firstName.' '.$this->poster->lastName.'</span>'.PHP_EOL;
        echo '<span class="SID">'.$this->poster->student_id.'</span>'.PHP_EOL.'</div>'.PHP_EOL;
        echo '<span class="timestamp">'.$this->timestamp.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL.'<div class="content">'.PHP_EOL.'<p>'.PHP_EOL;
        echo $this->post.PHP_EOL.'</p>'.PHP_EOL;
        echo '<img class="content-image" src="'.$this->image.'"/>'.PHP_EOL;
        echo '<div class="likes">'.PHP_EOL;
        echo '<a class="like-button" onclick="likePost('.$this->id.')">Like</a>'.PHP_EOL;
        echo '<span id="like-count-'.$this->id.'">'.$this->getLikeCount().'</span>'.PHP_EOL;
        echo '<a class="comment-button" onclick="showComments('.$this->id.');">Comments</a>'.PHP_EOL;
        echo '<span id="comment-count-'.$this->id.'">'.$this->getCommentCount().'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'<div class="messages" id="comment-section-'.$this->id.'">'.PHP_EOL;
        foreach($this->comments as $comment){
            echo '<div class="message" id="'.$comment->id.'">'.PHP_EOL;
            echo '<div class="poster">'.PHP_EOL.'<div class="identification">'.PHP_EOL;
            echo '<img class="avatar" src="'.$comment->commenter->avatarSrc.'"/>'.PHP_EOL;
            echo '<span class="name">'.$comment->commenter->firstName.' '.$comment->commenter->lastName.'</span>'.PHP_EOL;
            echo '<span class="SID">'.$comment->commenter->student_id.'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '<span class="timestamp">'.$comment->timestamp.'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '<div class="content">'.PHP_EOL.'<p>'.PHP_EOL;
            echo $comment->comment.PHP_EOL.'</p>'.PHP_EOL;
            echo '<div class="likes">'.PHP_EOL;
            echo '<a class="like-button" href="likeComment('.$comment->id.');">Like</a>'.PHP_EOL;
            echo '<span class="like-count">'.$comment->getLikeCount().'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL;
        }
        echo '<div class="post-comment">'.PHP_EOL;
        echo '<form name="post-comment" method="post">'.PHP_EOL;
        $currentUser = new User($_SESSION['id'], $_SESSION['id']);
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$currentUser->avatarSrc.'"/>'.PHP_EOL;
        echo '<span class="name">'.$currentUser->firstName.' '.$currentUser->lastName.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="area">'.PHP_EOL;
        echo '<form type="submit" method="post">'.PHP_EOL;
        echo '<input type="hidden" name="post-id" value="'.$this->id.'"/>'.PHP_EOL;
        echo '<textarea name="commentText" placeholder="What is your opinion about this?"></textarea>'.PHP_EOL;
        echo '<button name="comment-button">Submit</button>'.PHP_EOL;
        echo '</form>'.PHP_EOL;
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
?>