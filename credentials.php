<?php
// should be called from registration or login page
include_once 'essentials.php';

if (isset($_POST["password"]) && isset($_POST["username"])) {
    $password = $_POST['password'];
    $username = htmlspecialchars(trim($_POST['username']));

    // if credentials are empty
    if ($username === "" || $password === "")
        Redirect($portal . ".php?invalid=" . urlencode($status));

    // start database shenanigans
    $dsnHost = "localhost";
    $database = "project";
    $dsnUsername = "root";
    $dsnPassword = "root";
    
    $pdo = CreatePDO($dsnHost, $database, $dsnUsername, $dsnPassword);

    if (!$pdo)
        Redirect($portal . ".php?invalid=" . urlencode($status));
    
    CreateTables();
    
    if ($portal === "login") {
        if (UserValid($username, $password) && LogUser($username)) {
            $id = RetrieveID($username);
            if ($id === false)
                Redirect($portal . ".php?invalid=". urlencode($status));
            $userPermissions = GetUserPermissions($id);

            StartUserSession($username, $id, $userPermissions);
            Redirect("index.php");
        }
        else
            Redirect($portal . ".php?invalid=" . urlencode($status));
    }
    if ($portal === "register") {
        if (CreateUser($username, $password, $email)) {
            // send to login or success page
            Redirect("index.php");
        }
        else
            Redirect($portal . ".php?invalid=" . urlencode($status));
    }
    
    $pdo = null;
}

