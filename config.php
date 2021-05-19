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
// Define database credentials and stuff
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'fedea');
define('DB_PASSWORD', 'test');
define('DB_NAME', 'khas1');
// Attempt connecting
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Catch fire if you cant
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
/*
// Create tables if they dont exist
$queryArray = [
"
CREATE TABLE IF NOT EXISTS users(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    avatarSrc VARCHAR(255) DEFAULT 'img/avatars/default.jpg',
    privacy BOOL DEFAULT 1,
    email VARCHAR(75) NOT NULL UNIQUE,
    dateOfBirth DATETIME NULL,
    password VARCHAR(255) NOT NULL,
    branch VARCHAR(128) NULL,
    bio VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
",
"
CREATE TABLE IF NOT EXISTS friendship(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    firstUser VARCHAR(50) NOT NULL,
    friendedUser VARCHAR(50) NOT NULL
);
",
"
CREATE TABLE IF NOT EXISTS friendship_req(
    id int not null primary key auto_increment,
    target VARCHAR(50) not null,
    destination VARCHAR(50) not null
);
",
"
CREATE TRIGGER befriend_yourself
AFTER INSERT ON users
FOR EACH ROW
INSERT INTO friendship(firstUser, friendedUser) VALUES(NEW.student_id, NEW.student_id);
",
"
CREATE TABLE IF NOT EXISTS admin(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
admin_id VARCHAR(50) NOT NULL UNIQUE);
",
"
CREATE TABLE IF NOT EXISTS posts(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    poster_id VARCHAR(50) NOT NULL,
    post VARCHAR(500) NOT NULL,
    image VARCHAR(255) NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
",
"
 CREATE TABLE IF NOT EXISTS announcements(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    announcer_id VARCHAR(50) NOT NULL,
    announcement VARCHAR(500) NOT NULL,
    image VARCHAR(255) NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
",
"
CREATE TABLE IF NOT EXISTS comments(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    commenter_id VARCHAR(50) NOT NULL,
    comment VARCHAR(500) NOT NULL,
    post_id VARCHAR(500) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
",
"
CREATE TABLE IF NOT EXISTS liked_posts(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    liker VARCHAR(50) NOT NULL,
    liked VARCHAR(50) NOT NULL
);
",
"
CREATE TABLE IF NOT EXISTS liked_comments(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    liker VARCHAR(50) NOT NULL,
    liked VARCHAR(50) NOT NULL
);
"
];
foreach ($queryArray as &$query){
    if($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_execute($stmt);
    } else {
        echo "Something went wrong for some reason.\n\n";
        die("<pre>".mysqli_error($link).PHP_EOL.$query."</pre>");
    }
} unset($query); // not unsetting $query produces undefined behaviour.
*/
?>