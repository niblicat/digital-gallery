<?php

error_reporting(E_ALL);

// redirects to a new url
function Redirect($url, $permanent = false) {
    if (headers_sent() === false)
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    exit();
}

// executes an sql statement
function ExecuteStatement($statement, $inner = null) {
    try {
        return ($statement->execute($inner));
    } catch (PDOException $e) {
        die('Exception ' . $e->getMessage());
    }
}

// establishes a connection with the database
function CreatePDO($host = "localhost", $database = "my_db", $username = "root", $password = "root") {
    $dsn = "mysql:host=" . $host . ";dbname=" . $database . ";";
    try {
        $pdo = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        global $status;
        $status = $e->getMessage();
        return false;
    }
    return $pdo;
}

// returns the permission level of a user given a user id
function GetUserPermissions($id) {
    global $pdo;
    global $status;

    $sql = "SELECT operator from registration WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$id])) {
        $status = "could not determine user status";
        return false;
    }
    $permissionLevel = $stmt->fetchColumn();

    if ($permissionLevel === false) {
        $status = "permission level not set";
        return false;
    }
    return $permissionLevel;
}

// verifies the validity of a user given a username and password
function UserValid($username, $password) {
    global $pdo;
    global $status;

    $sql = "SELECT password from registration WHERE username=?";
    $stmt = $pdo->prepare($sql);
    ExecuteStatement($stmt, [$username]);

    $hashed = $stmt->fetchColumn();
    if ($hashed === false) {
        $status = "invalid credentials";
        return false;
    }

    if (password_verify($password, $hashed))
        return true;
    else {
        $status = "Invalid credentials";
        return false;
    }
}

// creates a user with a username, password, and email
function CreateUser($username, $password, $email = null) {
    global $pdo;
    global $status;

    $sql = "SELECT username from registration WHERE username=?";
    $stmt = $pdo->prepare($sql);
    ExecuteStatement($stmt, [$username]);
    
    if ($stmt->fetchColumn()) {
        $status = "username already exists";
        return false;
    }

    $sql = "INSERT INTO registration (username, password, email) VALUES (:username, :password, :email)";
    $stmt = $pdo->prepare($sql);

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt->bindParam("username", $username);
    $stmt->bindParam("email", $email);
    $stmt->bindParam("password", $hashed);

    if (!ExecuteStatement($stmt)) {
        $status = "error creating user";
        return false;
    }

    // potentially validate password
    return true;
}

// removes a user given the user id
function RemoveUser($id) {
    global $pdo;
    global $status;

    $sql = "DELETE from media WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$id])) {
        $status = "could not user's record";
        return false;
    }

    $sql = "DELETE from door WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$id])) {
        $status = "could not user's logs";
        return false;
    }

    $sql = "DELETE from registration WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$id])) {
        $status = "could not delete user";
        return false;
    }

    return true;
}

// initialises the user session with username, id, and permission level
function StartUserSession($username, $id, $operator = false) {
    $_SESSION["username"] = $username;
    $_SESSION["id"] = $id;
    $_SESSION["operator"] = $operator;
}

// ends the user session
function EndUserSession() {
    session_unset();
    session_destroy();
}

// logs the user login time in the door table
function LogUser($username) {
    global $pdo;
    global $status;

    $id = RetrieveID($username);

    date_default_timezone_set("America/Chicago");
    $date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO door (id, time) VALUES (:id, :time)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam("id", $id);
    $stmt->bindParam("time", $date);

    if (!ExecuteStatement($stmt)) {
        $status = "error logging user";
        return false;
    }

    return true;
}

// returns the user id given the username
function RetrieveID($username) {
    global $pdo;
    global $status;

    $sql = "SELECT id from registration WHERE username=?";
    $stmt = $pdo->prepare($sql);
    ExecuteStatement($stmt, [$username]);
    
    $id = $stmt->fetchColumn();
    if ($id === false) {
        $status = "could not retrieve id";
        return false;
    }
    return $id;
}

// adds media to the database and server
function AddMedia($id, $name = "", $file = "file") {
    global $pdo;
    global $status;

    $name = htmlspecialchars(trim($name));

    if (isset($_FILES[$file])) {
        $imageDirectory = "images/";
        $fileInfo = pathinfo($_FILES[$file]["name"]);

        // set as uploaded file name if user didn't choose a name
        if ($name === "" || !isset($name))
            $fileName = $fileInfo["filename"];
        else
            $fileName = $name;

        $fileHashedName = hash('sha256', $fileName . strval(time()));
        
        $sql = "SELECT record from media WHERE filename=?";
        $stmt = $pdo->prepare($sql);
        ExecuteStatement($stmt, [$fileHashedName]);

        // check if file already exists in directory
        // add a copy version if it already exists
        while ($stmt->fetchColumn() !== false) {
            $fileHashedName = hash('sha256', $fileName . strval(time()));

            $sql = "SELECT id from media WHERE filename=?";
            $stmt = $pdo->prepare($sql);
            ExecuteStatement($stmt, [$fileHashedName]);
        }

        // check if file is within size limits
        if ($_FILES[$file]["size"] > 500000000) {
            $status = "file is too large";
            return false;
        }

        // check if file type is appropriate
        $imageType = strtolower($fileInfo["extension"]);
        if ($imageType != "jpg" && $imageType != "png" && $imageType != "jpeg" && $imageType != "gif" && $imageType != "webp") {
            $status = "file type is not png, jpg, jpeg, gif, or webp";
            return false;
        }

        $sql = "INSERT INTO media (name, id, filename, extension) VALUES (:name, :id, :filename, :extension)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam("name", $fileName);
        $stmt->bindParam("id", $id);
        $stmt->bindParam("filename", $fileHashedName);
        $stmt->bindParam("extension", $imageType);

        if (!ExecuteStatement($stmt)) {
            $status = "error inserting record into database";
            return false;
        }

        $fileLocation = $imageDirectory . $fileHashedName . "." . $imageType;

        // upload since things went well
        move_uploaded_file($_FILES[$file]["tmp_name"], $fileLocation);

        return true;
    }
}

// removes media from the database and server
function RemoveMedia($userID, $recordID) {
    global $pdo;
    global $status;

    // check if user is operator
    $sql = "SELECT operator from registration WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$userID])) {
        $status = "could not determine user status";
        return false;
    }

    // returns 1 if true
    $isOperator = $stmt->fetchColumn();

    $sql = "SELECT * from media WHERE record=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$recordID])) {
        $status = "could not determine media status in database";
        return false;
    }
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record === false) {
        $status = "could not find media in database";
        return false;
    }

    if (($userID !== $record["id"]) && ($isOperator == false)) {
        $status = "you do not have permission to delete this file" . $isOperator;
        return false;
    }

    $sql = "DELETE from media WHERE record=?";
    $stmt = $pdo->prepare($sql);
    if (!ExecuteStatement($stmt, [$recordID])) {
        $status = "could not delete record";
        return false;
    }

    $fileLocation = "images/" . $record["filename"] . "." . $record["extension"];
    if (!unlink($fileLocation)) {
        $status = "could not remove file from server";
        // return false;
    }

    return true;
}

// returns a defined number of media
function GetMedia($start = 0, $end = 9) {
    global $pdo;
    global $status;

    $amount = $end - $start + 1;

    // only select few records, not all
    // should return array of image names
    $sql = "SELECT * FROM media ORDER BY record DESC LIMIT " . $amount . " OFFSET " . $start;
    $stmt = $pdo->prepare($sql);

    if (!ExecuteStatement($stmt)) {
        $status = "could not retrieve images";
        return false;
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results === false) {
        $status = "could not retrieve media names";
        return false;
    }
    return $results;
}

// creates necessary tables if they do not exist
function CreateTables() {
    global $pdo;

    $sql = "CREATE TABLE IF NOT EXISTS registration (
        id INT(6) NOT NULL AUTO_INCREMENT,
        username VARCHAR(127) NOT NULL,
        email VARCHAR(127),
        operator BOOLEAN DEFAULT 0,
        password VARCHAR(127) NOT NULL,
        PRIMARY KEY(id)
    )";

    $statement = $pdo->prepare($sql);
    ExecuteStatement($statement);

    $sql = "CREATE TABLE IF NOT EXISTS door (
            record INT(15) NOT NULL AUTO_INCREMENT,
            id INT(6) NOT NULL,
            time DATETIME NOT NULL,
            PRIMARY KEY(record),
            FOREIGN KEY(id) REFERENCES registration(id)
        )";

    $statement = $pdo->prepare($sql);
    ExecuteStatement($statement);

    $sql = "CREATE TABLE IF NOT EXISTS media (
        record INT(6) NOT NULL AUTO_INCREMENT,
        id INT(6) NOT NULL,
        name VARCHAR(255) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        extension VARCHAR(6) NOT NULL,
        PRIMARY KEY(record),
        FOREIGN KEY(id) REFERENCES registration(id)
    )";

    $statement = $pdo->prepare($sql);
    ExecuteStatement($statement);
}

// creates an image form given the record id, filename, file extension, actual name, and ability to delete
function CreateImageForm($record, $fileName, $extension, $imageName, $deletable = false) {
    $fileLocation = 'images/' . $fileName . "." . $extension;
    echo "<div class='mediawrapper'>";
    echo "<form method='POST' action='index.php'>";
    echo "<a href=$fileLocation>";
    echo "<img title=$imageName alt=$imageName src=$fileLocation height='100'>";
    echo "</a>";
    if ($deletable) {
        echo "<div class='imagebuttonholder'>";
        echo "<br>";
        echo "<input type='hidden' name='todelete' value=" . $record . ">";
        echo "<input class='x' title='delete' type='submit' value='x'>";
        echo "</div>";
    }
    echo "</form>";
    echo "</div>";
}

// echos the status if invalid
function InvalidSubmission($status) {
    if (isset($_GET['invalid']))
        echo '<p class="error">' . $status . '</p>';
}

// echos the user list with options to delete any user
function DisplayUserList() {
    global $pdo;
    global $status;

    $sql = "SELECT id, username, email, operator FROM registration";
    $stmt = $pdo->prepare($sql);

    if (!ExecuteStatement($stmt)) {
        $status = "could not access database";
        return false;
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results === false) {
        $status = "could not retrieve user list";
        return false;
    }

    echo "<table border='1'>";
    echo "<tr><th colspan='5'>users</th></tr>";
    echo "<tr>";
    echo "<th>id</th>";
    echo "<th>username</th>";
    echo "<th>email</th>";
    echo "<th>operator</th>";
    echo "<th>delete</th>";
    echo "</tr>";
    foreach ($results as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "<td><form method='POST' action='admin.php'>";
        echo "<input type='hidden' name='todelete' value=" . $row["id"] . ">";
        echo "<input class='x' type='submit' value='x'>";
        echo "</form></td>";
        echo "</tr>";
    }
    echo "</table>";
    return true;
}

// displays the logs from the door table
function DisplayLogs() {
    global $pdo;
    global $status;

    $sql = "SELECT record, id, time FROM door";
    $stmt = $pdo->prepare($sql);

    if (!ExecuteStatement($stmt)) {
        $status = "could not access database";
        return false;
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results === false) {
        $status = "could not retrieve logs";
        return false;
    }

    echo "<table border='1'>";
    echo "<tr><th colspan='3'>logs</th></tr>";
    echo "<tr>";
    echo "<th>record</th>";
    echo "<th>id</th>";
    echo "<th>time</th>";
    echo "</tr>";
    foreach ($results as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    return true;
}