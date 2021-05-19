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
require "functions.php";
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
    <title>Profile</title>
    <?php include "templates/headtags.html"?>
</head>
<body>
    <?php include "templates/header.html"?>
    <script>makeButtonActive("profile");</script>
    <div class="search-field">
    </div>
    <?php
    echo '<div class="settings">';
    echo '<div>';
    echo '<img height="150px" src="'.$currentUser->avatarSrc.'">';
    echo '<h2>'.$currentUser->firstName.' '.$currentUser->lastName.'</h2>';
    echo '</div>';
    echo '<div>';
    echo '<input type="file" class="changeAvatar"/>'.PHP_EOL;
    echo '</div>';
    echo '<div>';
    echo '<span>First Name</span>';
    echo '<input type="text" class="firstName" readonly onfocus="this.removeAttribute(\'readonly\');"/>';
    echo '</div>';
    echo '<div>';
    echo '<span>Last Name</span>';
    echo '<input type="text" class="lastName" readonly onfocus="this.removeAttribute(\'readonly\');"/>';
    echo '</div>';
    echo '<div>';
    echo '<span>E-Mail</span>';
    echo '<input type="text" class="email" readonly onfocus="this.removeAttribute(\'readonly\');"/>';
    echo '</div>';
    echo '<span class="invalid-feedback" id="email"></span>';
    echo '<div>';
    echo '<span>Major</span>';
    echo '<input type="text" class="major" readonly onfocus="this.removeAttribute(\'readonly\');"/>';
    echo '</div>';
    echo '<div>';
    echo '<span>Bio</span>';
    echo '<textarea class="edit-bio" readonly onfocus="this.removeAttribute(\'readonly\');"></textarea>';
    echo '</div>';
    echo '<div>';
    echo '<span>Confirm Password</span>';
    echo '<input type="password" class="confirmPassword"/>';
    echo '</div>';
    echo '<span class="invalid-feedback" id="confirmPassword"></span>';
    echo '<div>';
    echo '<button onclick="modifySettings()">Change Settings</button>';
    echo '</div>';
    echo '</div>';
    ?>
    <?php include "templates/footer.html"?>
</body>
</html>