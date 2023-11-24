<?php
    include 'essentials.php';
    session_start();
    $portal = "login";
    $status = "";

    if (isset($_SESSION["username"]))
        Redirect("index.php?logout");
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (isset($_GET['invalid']))
            $status = $_GET['invalid'];
        else if ($_SERVER['QUERY_STRING'] !== "") {
            Redirect("login.php");
        }
    }
    
    // handle login stuff
    include 'credentials.php';
?>

<!DOCTYPE html>
<html>  
    <head>
        <meta charset="UTF-8">
        <title>login</title>
        <link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/exo-2-new" type="text/css"> 
        <link rel="stylesheet" href="default.css" type="text/css">
    </head>
    <body>
        <div class="contentcontainer">
            <div class="contentitems">
                <h1>login</h1>
                <form action="login.php" method="POST">
                    <label for="username">username: 
                        <input type="text" id="username" name="username" required>
                    </label>
                    <label for="password">password: 
                        <input type="password" id="password" name="password" required>
                    </label>
                    <input type="submit" value="submit">
                    <p>
                        <a href="register.php">register</a>
                    </p>
                    <?php
                        InvalidSubmission($status);
                    ?>
                </form>
            </div>
        </div>
    </body>
</html>