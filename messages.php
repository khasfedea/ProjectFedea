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
    header("location: index.php");
    exit;
}
$currentUser = new User($_SESSION["id"], $_SESSION["id"]);
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <title>Messages</title>
    <?php include "templates/headtags.html"?>
    <script src="src/messages.js" type="text/javascript"></script>
</head>
<body>
    <?php include "templates/header.html"?>
    <script>makeButtonActive("mesg");</script>
    <div class="search-field">
    </div>
    <div class="message-window">
    <?php
    echo '<div class="message-pane">';
    echo '<div class="identification">';
    echo '<img class="avatar"/>';
    echo '<span class="name"></span>';
    echo '</div>';
    echo '<div class="message-field">';
    echo '</div>';
    echo '<input type="text" class="message-enter"/>';
    echo '</div>';
    echo '<div class="friends" id="message-friends">';
    foreach($currentUser->friends as $friend_id){
        if ($friend_id == $currentUser->student_id){
            continue;
        }
        $friend = new User($friend_id, $_SESSION["id"]);
        echo '<div class="identification" id="'.$friend_id.'">'.PHP_EOL;
        echo '<img class="avatar" src="'.$friend->avatarSrc.'"/>'.PHP_EOL;
        echo '<a class="name" onclick="fetchMessages(\''.$friend->student_id.'\')">'.$friend->firstName.' '.$friend->lastName.'</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
    echo '</div>';
    ?>
    </div>
    <?php include "templates/footer.html"?>
</body>
</html>