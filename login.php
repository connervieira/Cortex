<?php
include "./config.php";

$force_login_redirect = false;
include "./authentication.php";

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Login</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body class="truebody">
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Login</h2>
        <?php
        $password = strval($_POST["password"]); // Get the entered password from the POST data.

        if ($password != "" and $password != null) { // Check to see if a password was entered.
            if ($config["interface_password"] == $password) { // Check to see if the password entered matches the password set in the configuration.
                $_SESSION['authid'] = "cortex";
                $_SESSION['username'] = "admin";

                echo "<p>Successfully logged in</p>";
                echo "<a href='./index.php' class=\"button\">Continue</a>";
                exit();
            } else {
                echo "<p class\"error\">Incorrect password</p>";
            }
        }
        ?>
        <main>
            <form method="post">
                <label for="password">Password: </label> <input type="password" placeholder="Password" name="password" id="password">
                <br><br>
                <input type="submit">
            </form>
        </main>
    </body>
</html>
