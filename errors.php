<?php
include "./config.php";
include "./utils.php";

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
        $instance_config = load_instance_config($config);
        $error_file_path = $instance_config["general"]["interface_directory"] . "/errors.json";

        if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
            if (file_exists($error_file_path) == true) { // Check to see if the error file exists.
                $error_log = json_decode(file_get_contents($error_file_path), true); // Load the error file from JSON data.
            } else { // If the error file doesn't exist, then load a blank placeholder instead.
                $error_log = array(); // Set the error log to an empty array.
            }
        } else {
            echo "<p>The interface directory does yet exist.</p>";
            exit();
        }


        $error_log = array_reverse($error_log, true); // Reverse the error log, so that more recent errors are at the top.
        $error_log = array_slice($error_log, 0, 3, true); // Throw out everything but the first 3 errors.

        $messages_to_display = array(); // Set this list of messages to display to a blank placeholder array.

        foreach ($error_log as $key => $error) {

            $error_age = (time() - floatval($key)); // Get the age of the error, in seconds.
            if ($error_age < 0) { $error_age = 0.0; } // If the error's age is negative, the default to 0 to compensate for minor clock differences.

            $message = date("Y-m-d H:i:s", $key) . " (" . number_format((round($error_age*100)/100), 2) . ") - " . $error["msg"]; // Generate the message line for this error.


            if ($error_age < 60 * 1) { // Check to see if this error is less than one minute old before adding it to the list of messages to display.
                if ($error_age < 10) { // If the error is recent enough, display it in a prominent style.
                    if ($error["type"] == "error") { // Check to see if this message is an error.
                        array_push($messages_to_display, "<p style='color:red;'>" . $message . "</p>"); // Display the message in red colored font.
                    } else if ($error["type"] == "warn") { // Check to see if this message is a warning.
                        array_push($messages_to_display, "<p style='color:orange;'>" . $message . "</p>"); // Display the message in orange colored font.
                    }
                } else { // If the error isn't recent, display it in a subtle style.
                    array_push($messages_to_display, "<p style='color:#888888;'>" . $message . "</p>");
                }
            }
        }

        if (sizeof($messages_to_display) <= 0) {
            echo "<p><i>No errors reported recently</i></p>";
        } else {
            foreach ($messages_to_display as $entry) {
                echo $entry;
            }
        }

        ?>
    </body>
</html>
