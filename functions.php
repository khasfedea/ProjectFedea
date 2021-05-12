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
class Poster {
    public $SID;
    public $avatar;
    public $name;
}
class Comments {
    public $id;
    public $poster;
    public $timestamp;
    public $content;
    public $likeCount;
    public function __construct(){
        $this->poster = new Poster;
    }
}
class PostTemplate {
    public $id;
    public $poster;
    public $timestamp;
    public $content;
    public $image;
    public $likeCount;
    public $commentCount;
    public $comments;
    public function __construct(){
        $this->poster = new Poster;
        $this->comments = array();
    }
    public function printPost(){
        echo '<div class="post" id="'.$this->id.'">'.PHP_EOL;
        echo '<div class="poster">'.PHP_EOL;
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$this->poster->avatar.'"/>'.PHP_EOL;
        echo '<span class="name">'.$this->poster->name.'</span>'.PHP_EOL;
        echo '<span class="SID">'.$this->poster->SID.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<span class="timestamp">'.$this->timestamp.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="content">'.PHP_EOL;
        echo '<p>'.PHP_EOL;
        echo $this->content.PHP_EOL;
        echo '</p>'.PHP_EOL;
        echo '<img class="content-image" src="'.$this->image.'"/>'.PHP_EOL;
        echo '<div class="likes">'.PHP_EOL;
        echo '<a class="like-button" href="likePost('.$this->id.');">Like</a>'.PHP_EOL;
        echo '<span class="like-count">'.$this->likeCount.'</span>'.PHP_EOL;
        echo '<a class="comment-button" href="showComments('.$this->id.');">Comment</a>'.PHP_EOL;
        echo '<span class="comment-count">'.$this->commentCount.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<div class="messages">'.PHP_EOL;
        foreach($this->comments as $comment){
            echo '<div class="message" id="'.$comment->id.'">'.PHP_EOL;
            echo '<div class="poster">'.PHP_EOL;
            echo '<div class="identification">'.PHP_EOL;
            echo '<img class="avatar" src="'.$comment->poster->avatar.'"/>'.PHP_EOL;
            echo '<span class="name">'.$comment->poster->name.'</span>'.PHP_EOL;
            echo '<span class="SID">'.$comment->poster->SID.'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '<span class="timestamp">'.$comment->timestamp.'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '<div class="content">'.PHP_EOL;
            echo '<p>'.PHP_EOL;
            echo $comment->content.PHP_EOL;
            echo '</p>'.PHP_EOL;
            echo '<div class="likes">'.PHP_EOL;
            echo '<a class="like-button" href="likeComment('.$comment->id.');">Like</a>'.PHP_EOL;
            echo '<span class="like-count">'.$comment->likeCount.'</span>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
        }
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
}
?>