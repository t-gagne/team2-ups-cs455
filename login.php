<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'generalFunctions.php';
require 'PasswordHash.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST["username"]);
    $password = clean_input($_POST["password"]);

    if (inputted_properly($username) and inputted_properly($password)) {

        // Before checking credentials, I need to make sure that the username actually exists.

        if (correct_credentials($username, $password)) {
            $_SESSION["username"] = $username;
            unset($_SESSION["login/username"]);
            unset($_SESSION["login/password"]);
            unset($_SESSION["login/invalidLogin"]);

            // TODO: Make the user go to the last page they were on after a successful login
            header("Location: index.php");
            exit();
        } else {
            $_SESSION["login/invalidLogin"] = "Sorry, your account name or password was wrong.";
            unset($_SESSION["login/username"]);
            unset($_SESSION["login/password"]);

            header("Location: login.php");
            exit();
        }

    } else {
        $_SESSION["login/invalidLogin"] = "Sorry, your username or password cannot be blank.";
    }
}

function correct_credentials($username, $password) {
    $rightPassword = false;

    try {
        // Pull in the password hash from the DB for this user
        $db = new PDO("sqlite:database/noiseFactionDatabase.db");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $db->prepare("select passwordHash from Account where username = ?;");
        $result = $statement->execute(array($username));

        if ($result != 1) {
            throw new pdoDbException("Something's gone wrong with the prepared statement");
        } else {
            $userTuple = $statement->fetch(PDO::FETCH_ASSOC);

            // If $userTuple is null, there is no account under that name
            if($userTuple !== null){
                $passwordHash = $userTuple["passwordHash"];

                $hasher = new PasswordHash(8, FALSE);

                // This comes from PasswordHash.php
                $rightPassword = $hasher->CheckPassword($attempt, $passwordHash);
            } else {
                // What should we do here?
                $rightPassword = false;
            }
        }

        $db = null;

    } catch(PDOException $e) {
        echo 'Exception: '.$e->getMessage();
    }

    return $rightPassword;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Team2</title>
        <link rel="stylesheet" type="text/css" href="css/basic.css">
    </head>
    <body>

        <div class="content">
            <h3>Log in to an existing account:</h3>

            <form method="post" action="login.php">
                Username: <input type="text" name="username">
                <br><br>
                Password: <input type="password" name="password">
                <br>
                <input type="submit" name="submit" value="Log in">
                <br>
                <span class="error"><?php echo $_SESSION["login/invalidLogin"]; ?></span>
                <br>
            </form>

        </div>

    </body>
</html>
