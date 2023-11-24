<?php
    include 'essentials.php';
    session_start();
    $portal = "index";
    $username = "";
    $page = 1;

    // have some sort of panel system where images can be viewed
    // and sorted by username, search for images by title
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (isset($_GET['logout']))
            EndUserSession();
        else if (isset($_GET['invalid']))
            $status = $_GET['invalid'];
        else if (isset($_GET['page'])) {
            $page = $_GET['page'];
            if ($page < 1) Redirect('index.php?page=1');
        }
        else if ($_SERVER['QUERY_STRING'] !== "")
            Redirect("upload.php");
    }
    
    // should redirect to login if not a valid session
    if (!isset($_SESSION["username"]))
        Redirect("login.php");
    else
        $username = $_SESSION["username"];

    include 'gallery.php';
?>

<!DOCTYPE html>
<html>  
    <head>
        <meta charset="UTF-8">
        <title>home</title>
        <link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/exo-2-new" type="text/css"> 
        <link rel="stylesheet" href="default.css" type="text/css">
    </head>
    <body>
        <nav>
            <?php
                if ($_SESSION["operator"] == true)
                    echo "<a href='admin.php'>admin</a> |"
            ?>
            <a href="upload.php">upload</a> | 
            <a href="index.php?logout">logout</a>
        </nav>
        <div class="contentcontainer">
            <div class="contentitems">
                <h1>home</h1>
                <?php echo "user: " . $username; ?>

                <div id="gallery">
                    <?php
                        if ($mymedia !== false) {
                            foreach ($mymedia as $media) {
                                $deletable = false;
                                if (($_SESSION["id"] === $media["id"]) || $_SESSION["operator"] == true)
                                    $deletable = true;
                                CreateImageForm($media["record"], $media["filename"], $media["extension"], $media["name"], $deletable);
                            }
                        }
                    ?>
                </div>
                <?php
                    echo "<p>";
                    if ($page > 1) {
                        echo "<a href=index.php?page=" . $page - 1 . ">previous</a>";
                        if (sizeof($results) >= 10) echo " | ";
                    }
                    if (sizeof($mymedia) >= 10) {
                        echo "<a href=index.php?page=" . $page + 1 . ">next</a>";
                    }
                    echo "</p>";


                    InvalidSubmission($status);
                ?>
            </div>
        </div>
    </body>
</html>