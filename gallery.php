<?php
// should be called from index
include_once 'essentials.php';

$dsnHost = "localhost";
$database = "project";
$dsnUsername = "root";
$dsnPassword = "root";

$pdo = CreatePDO($dsnHost, $database, $dsnUsername, $dsnPassword);

if (!$pdo)
    Redirect($portal . ".php?invalid=" . urlencode($status));

CreateTables();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["todelete"]))
        if (!RemoveMedia($_SESSION["id"], $_POST["todelete"]))
            Redirect($portal . ".php?invalid=" . urlencode($status));
}

$start = ($page - 1) * 10;
$end = $start + 9;

$mymedia = GetMedia($start, $end);
