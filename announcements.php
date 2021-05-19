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
    <title>Announcements</title>
    <?php include "templates/headtags.html"?>
</head>
<body>
    <?php include "templates/header.html"?>
    <script>makeButtonActive("anno");</script>
    <div class="search-field">
    </div>
    <?php
    $admins = array();
    $sql = "SELECT admin_id FROM admin;";
    $stmt = mysqli_prepare($link, $sql);
    echo mysqli_error($link);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row);
    while(mysqli_stmt_fetch($stmt)) {
        $admins[] = $row;
    }
    if(in_array($currentUser->student_id, $admins)) {
        echo '<div class="post-window">'.PHP_EOL;
        echo '<div class="identification">'.PHP_EOL;
        echo '<img class="avatar" src="'.$currentUser->avatarSrc.'"/>'.PHP_EOL;
        echo '<span class="name">'.$currentUser->firstName.' '.$currentUser->lastName.'</span>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<textarea id="announcementText" placeholder="What are you going to announce, '.$currentUser->firstName.'?"></textarea>'.PHP_EOL;
        echo '<div class="post-buttons">'.PHP_EOL;
        echo '<input type="file" name="announcementPicture" id="announcementPicture">'.PHP_EOL;
        echo '<button onclick="postAnnouncement()">Submit</button>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
    PrintAnnouncements();
    ?>
    <?php include "templates/footer.html"?>
</body>
</html>