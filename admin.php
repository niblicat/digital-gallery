<?php
    include 'essentials.php';
    session_start();
    $portal = "admin";
    $username = "";

    // admin controls to view users and see logs
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (isset($_GET['logout']))
            EndUserSession();
        else if (isset($_GET['invalid']))
            $status = $_GET['invalid'];
        else if ($_SERVER['QUERY_STRING'] !== "")
            Redirect("upload.php");
    }
    
    // should redirect to login if not a valid session
    if (!isset($_SESSION["username"]) || !isset($_SESSION["operator"]))
        Redirect("login.php");
    else {
        $username = $_SESSION["username"];
        if ($_SESSION["operator"] == false) {
            Redirect("index.php");
        } 
    }
   
    $dsnHost = "localhost";
    $database = "project";
    $dsnUsername = "root";
    $dsnPassword = "root";
    
    $pdo = CreatePDO($dsnHost, $database, $dsnUsername, $dsnPassword);
    
    if (!$pdo)
        Redirect($portal . ".php?invalid=" . urlencode($status));

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["todelete"]))
            if (!RemoveUser($_POST["todelete"]))
                Redirect($portal . ".php?invalid=" . urlencode($status));
    }

?>

<!DOCTYPE html>
<html>  
    <head>
        <meta charset="UTF-8">
        <title>admin</title>
        <link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/exo-2-new" type="text/css"> 
        <link rel="stylesheet" href="default.css" type="text/css">
    </head>
    <body>
        <nav>
            <a href="index.php">home</a> | 
            <a href="upload.php">upload</a> | 
            <a href="admin.php?logout">logout</a>
        </nav>
        <div class="contentcontainer">
            <div class="contentitems">
                <h1>admin</h1>
                <?php
                    if (!$pdo)
                        Redirect($portal . ".php?invalid=" . urlencode($status));

                    if (!DisplayUserList())
                        Redirect($portal . ".php?invalid=" . urlencode($status));

                    if (!DisplayLogs())
                        Redirect($portal . ".php?invalid=" . urlencode($status));

                    InvalidSubmission($status);
                ?>
            </div>
        </div>
    </body>
</html>