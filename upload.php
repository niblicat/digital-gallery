<?php
    include 'essentials.php';
    session_start();
    $status = "";
    $portal = "upload";

    if (!isset($_SESSION["username"]) && !isset($_SESSION["id"]))
        Redirect("login.php");

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (isset($_GET['logout']))
            EndUserSession();
        else if (isset($_GET['invalid']))
            $status = $_GET['invalid'];
        else if ($_SERVER['QUERY_STRING'] !== "")
            Redirect("upload.php");
    }
    else if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = $_SESSION["id"];
        
        include "media.php";
    }
?>

<!DOCTYPE html>
<html>  
    <head>
        <meta charset="UTF-8">
        <title>upload</title>
        <link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/exo-2-new" type="text/css"> 
        <link rel="stylesheet" href="default.css" type="text/css">
    </head>
    <body>
        <nav>
            <?php
                if ($_SESSION["operator"] != false)
                    echo "<a href='admin.php'>admin</a> |"
            ?>
            <a href="index.php">home</a> | 
            <a href="upload.php?logout">logout</a>
        </nav>
        <div class="contentcontainer">
            <div class="contentitems">
                <h1>upload</h1>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <label for="name">name: 
                        <input type="text" id="name" name="name" placeholder="leave blank for default">
                    </label>
                    <input
                    type="file"
                    id="file"
                    name="file"
                    accept=".png,.jpg,.jpeg,.gif,.webp"
                    required>
                    <br>
                    <input type="submit" value="upload">
                    <?php
                        InvalidSubmission($status);
                    ?>
                </form>
            </div>
        </div>
    </body>
</html>