<?php
// should be called from upload
include_once 'essentials.php';

// Assume we're already logged in

$dsnHost = "localhost";
$database = "project";
$dsnUsername = "root";
$dsnPassword = "root";

$pdo = CreatePDO($dsnHost, $database, $dsnUsername, $dsnPassword);

if (!$pdo)
    Redirect($portal . ".php?invalid=" . urlencode($status));

$sql = "CREATE TABLE IF NOT EXISTS media (
            record INT(6) NOT NULL AUTO_INCREMENT,
            id INT(6) NOT NULL,
            name VARCHAR(255) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            PRIMARY KEY(record),
            FOREIGN KEY(id) REFERENCES registration(id)
        )";

$statement = $pdo->prepare($sql);
ExecuteStatement($statement);

// try to upload picture
// then redirect to main page
if (!AddMedia($id, $_POST["name"], "file"))
    Redirect($portal . ".php?invalid=" . urlencode($status));

$pdo = null;
