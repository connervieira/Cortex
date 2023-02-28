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
        $plates_file_path = $config["interface_directory"] . "/plates.json";

        if (is_dir($config["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
            if (file_exists($plates_file_path) == true) { // Check to see if the plates log file exists.
                $plates_log = json_decode(file_get_contents($plates_file_path), true); // Load the plates log file from JSON data.
            } else { // If the plate log file doesn't exist, then load a blank placeholder instead.
                $plates_log = array(); // Set the plates log to an empty array.
            }
        } else {
            echo "<p>The specified interface directory does not exist.</p>";
            exit();
        }







        // Find the most recent entry in the plate history.

        $most_recent_entry = array(0, array()); // Set the most recent entry to a blank placeholder.
        foreach ($plates_log as $key => $entry) { // Iterate through all entries in the plate history.
            if (time() - $key < time() - $most_recent_entry[0]) { // Check to see if this entry is more recent than the current best entry.
                $most_recent_entry = array($key, $entry); // Set this entry to the current best entry.
            }
        }




        $entry_age = (time() - floatval($most_recent_entry[0])); // Get the age of the entry, in seconds.
        if ($entry_age < 0) { $entry_age = 0.0; } // If the entry's age is negative, the default to 0 to compensate for minor clock differences.




        if (sizeof($most_recent_entry[1]) <= 0 or $entry_age > 60) { // Check to see if the plate history is empty.
            echo "<p><i>No plates detected recently</i></p>";
            exit();
        }





        foreach ($most_recent_entry[1] as $plate => $guesses) {
            $message = $message . "<li>$plate</li>";
            $message = $message . "<ul>";
            foreach ($guesses as $guess => $confidence) {
                $message = $message . "<li>" . $guess . " - " . round($confidence*10)/10 . "%</li>";
            }
            $message = $message . "</ul>";
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
