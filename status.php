<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php
        if ($config["auto_refresh"] == "server") {
            echo '<meta http-equiv="refresh" content="1" />';
        }
        ?>
        <link rel="stylesheet" href="./styles/minimal.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body class="inlinebody">
        <?php

        $heartbeat_file_path = $config["interface_directory"] . "/heartbeat.json";
        if (is_dir($config["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
            if (file_exists($heartbeat_file_path)) { // Check to see if the heartbeat file exists.
                $heartbeat_log = json_decode(file_get_contents($heartbeat_file_path), true); // Load the heartbeat file from JSON data.
            } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
                $heartbeat_log = array(); // Set the heartbeat log to an empty array.
            }
        }

        $last_heartbeat = time() - floatval(end($heartbeat_log)); // Calculate how many seconds ago the last heartbeat was.

        if ($last_heartbeat < $config["heartbeat_threshold"]) { // Only consider the system online if it's last heartbeat was within a certain number of seconds ago.
            if ($last_heartbeat < 0) { // If the last heartbeat was negative seconds ago, assume 0 seconds, since slight variations in clocks can cause negative numbers.
                echo "<p>Last heartbeat: 0 seconds ago</p>";
            } else {
                echo "<p>Last heartbeat: " . round(strval($last_heartbeat)*10)/10 . " seconds ago</p>";
            }
        } else { // If the last heartbeat exceeded the time to be considered online, display a message that the system is offline.
            echo "<p><i>Instance offline</i></p>";
        }

        ?>
    </body>
</html>
