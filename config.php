<?php
// Define database credentials and stuff
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'fedea');
define('DB_PASSWORD', 'test');
define('DB_NAME', 'khas');
// Attempt connecting
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Catch fire if you cant
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
// Create tables if they dont exist
$queryArray = [
"
CREATE TABLE IF NOT EXISTS departments(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);",
"
CREATE TABLE IF NOT EXISTS users(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    location VARCHAR(20) NULL,
    dateOfBirth DATETIME NULL,
    password VARCHAR(255) NOT NULL,
    department_id int REFERENCES departments(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS pages(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    pageName VARCHAR(50) NOT NULL,
    pageContent VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS page_likes(
    page_id int REFERENCES pages(id),
    user_id int REFERENCES users(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS posts(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    content VARCHAR(50) NOT NULL,
    page_id int REFERENCES pages(id),
    user_id int REFERENCES users(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS likes(
    post_id int REFERENCES posts(id),
    user_id int REFERENCES users(id),
    page_id int REFERENCES pages(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS photos(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    content VARCHAR(50) NOT NULL,
    post_id int REFERENCES posts(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS comments(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    content VARCHAR(50) NOT NULL,
    post_id int REFERENCES posts(id),
    user_id int REFERENCES users(id),
    page_id int REFERENCES pages(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS news(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    title VARCHAR(50) NOT NULL,
    content VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS roles(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS user_has_role(
    user_id int REFERENCES users(id),
    role_id int REFERENCES roles(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS user_groups(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL,
    role_id int REFERENCES roles(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS groups_has_user(
    user_id int REFERENCES users(id),
    group_id int REFERENCES user_groups(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS permission(
    id INT PRIMARY KEY NOT NULL,
    name VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);",
"
CREATE TABLE IF NOT EXISTS role_has_permission(
    role_id int REFERENCES roles(id),
    permission_id int REFERENCES permission(id)
);"
];
// Iterating over all the queries, preparing and executing them one by one.
/*
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