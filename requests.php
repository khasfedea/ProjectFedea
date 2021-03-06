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
    <?php GetFriendRequests(); ?>
    <?php include "templates/footer.html"?>
</body>
</html>