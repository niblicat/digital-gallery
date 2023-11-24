<?php
    include 'essentials.php';
    
    $portal = "success";
    $status = "";

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if ($_SERVER['QUERY_STRING'] !== "") {
            Redirect("success.php");
        }
    }
?>

<!DOCTYPE html>
<html>  
    <head>
        <meta charset="UTF-8">
        <title>success</title>
        <link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/exo-2-new" type="text/css"> 
        <link rel="stylesheet" href="default.css" type="text/css">
    </head>
    <body>
        <div class="contentcontainer">
            <div class="contentitems">
                <h1>success!</h1>
                <p>continue to <a href="login.php">login</a></p>
            </div>
        </div>
    </body>
</html>