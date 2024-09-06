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
        $plates_file_path = $instance_config["general"]["interface_directory"] . "/plates.json";
        $alerts_file_path = $instance_config["general"]["interface_directory"] . "/alerts.json";

        if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
            if (file_exists($plates_file_path) == true) { // Check to see if the plates log file exists.
                $plates_log = json_decode(file_get_contents($plates_file_path), true); // Load the plates log file from JSON data.
                $alerts_log = json_decode(file_get_contents($alerts_file_path), true); // Load the alerts log file from JSON data.
            } else { // If the plate log file doesn't exist, then load a blank placeholder instead.
                $plates_log = array(); // Set the plates log to an empty array.
                $alerts_log = array();
            }
        } else {
            echo "<p>The interface directory does not yet exist.</p>";
            exit();
        }







        // Find the most recent entry in the plate history.
        $most_recent_entry_plates = array(0, array()); // Set the most recent entry to a blank placeholder.
        foreach ($plates_log as $key => $entry) { // Iterate through all entries in the plate history.
            if (time() - $key < time() - $most_recent_entry_plates[0]) { // Check to see if this entry is more recent than the current best entry.
                $most_recent_entry_plates = array($key, $entry); // Set this entry to the current best entry.
            }
        }

        $entry_age = (time() - floatval($most_recent_entry_plates[0])); // Get the age of the entry, in seconds.
        if ($entry_age < 0) { $entry_age = 0.0; } // If the entry's age is negative, the default to 0 to compensate for minor clock differences.

        if (sizeof($most_recent_entry_plates[1]) <= 0 or $entry_age > 60) { // Check to see if the plate history is empty.
            echo "<p><i>No plates detected recently</i></p>";
            exit();
        }


        // Find the most recent entry in the alert history.
        $most_recent_entry_alerts = array(0, array()); // Set the most recent entry to a blank placeholder.
        foreach ($alerts_log as $key => $entry) { // Iterate through all entries in the plate history.
            if (time() - $key < time() - $most_recent_entry_alerts[0]) { // Check to see if this entry is more recent than the current best entry.
                $most_recent_entry_alerts = array($key, $entry); // Set this entry to the current best entry.
            }
        }

        $entry_age = (time() - floatval($most_recent_entry_alerts[0])); // Get the age of the entry, in seconds.
        if ($entry_age < 0) { $entry_age = 0.0; } // If the entry's age is negative, the default to 0 to compensate for minor clock differences.


        foreach ($most_recent_entry_plates[1] as $plate => $guesses) {
            $is_alert = false; // This will be switched to true if one of this plate's guesses is an active alert.
            foreach ($guesses as $guess => $confidence) {
                if (in_array($guess, array_keys($most_recent_entry_alerts[1]))) { // Check to see if this plate is an active alert.
                    $is_alert = true;
                }
            }
            if ($is_alert == true) {
                $message = $message . "<div style=\"border-radius:15px;background-color:#ff8888;\">";
            } else {
                $message = $message . "<div style=\"border-radius:15px;\">";
            }
            $message = $message . "<li>$plate</li>";
            if ($config["show_guesses"] == true) { // Check to see if Cortex is configured to show guesses.
                $message = $message . "<ul>";
                foreach ($guesses as $guess => $confidence) {
                    $message = $message . "<li>" . $guess . " - " . round($confidence*10)/10 . "%</li>";
                }
                $message = $message . "</ul>";
            }
            $message = $message . "</div>";
        }



        echo "<ul>";
        if ($entry_age < 10) { // If the entry is recent enough, display it in a prominent style.
            echo $message;
        } else { // If the error isn't recent, display it in a subtle style.
            echo "<span style='color:#888888;'>" . $message . "</span>";
        }
        echo "</ul>";

        ?>
    </body>
</html>
