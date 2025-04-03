<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

$instance_config = load_instance_config($config);




$error_file_path = $instance_config["general"]["interface_directory"] . "/errors.json";

if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.

    // Errors:

    if (file_exists($error_file_path) == true) { // Check to see if the error file exists.
        $error_log = json_decode(file_get_contents($error_file_path), true); // Load the error file from JSON data.
    } else { // If the error file doesn't exist, then load a blank placeholder instead.
        $error_log = array(); // Set the error log to an empty array.
    }

    $error_log = array_reverse($error_log, true); // Reverse the error log, so that more recent errors are at the top.
    $error_log = array_slice($error_log, 0, 3, true); // Throw out everything but the first 3 errors.

    $messages_to_display = array(); // Set this list of messages to display to a blank placeholder array.

    foreach ($error_log as $key => $error) {

        $error_age = (microtime(true) - floatval($key)); // Get the age of the error, in seconds.
        if ($error_age < 0) { $error_age = 0.0; } // If the error's age is negative, the default to 0 to compensate for minor clock differences.

        $message = date("Y-m-d H:i:s", $key) . " (" . number_format((round($error_age*100)/100), 2) . ") - " . $error["msg"]; // Generate the message line for this error.


        if ($error_age < 30) { // Check to see if this error is less than a certain number of seconds old before adding it to the list of messages to display.
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

    if (sizeof($messages_to_display) > 0) {
        foreach ($messages_to_display as $entry) {
            echo $entry;
        }
    }




    // License plates:

    $plates_file_path = $instance_config["general"]["interface_directory"] . "/plates.json";
    $alerts_file_path = $instance_config["general"]["interface_directory"] . "/alerts.json";

    if (file_exists($plates_file_path) == true) { // Check to see if the plates log file exists.
        $plates_log = json_decode(file_get_contents($plates_file_path), true); // Load the plates log file from JSON data.
        $alerts_log = json_decode(file_get_contents($alerts_file_path), true); // Load the alerts log file from JSON data.
    } else { // If the plate log file doesn't exist, then load a blank placeholder instead.
        $plates_log = array(); // Set the plates log to an empty array.
        $alerts_log = array();
    }


    // Find the most recent entry in the plate history.
    $all_plates = array();
    foreach ($plates_log as $timestamp => $plates) { // Iterate through all entries in the plate history.
        $all_plates[$timestamp] = $plates;
    }


    // Find the most recent entry in the alert history.
    $all_alerts = array();
    foreach ($alerts_log as $timestamp => $plates) { // Iterate through all entries in the plate history.
        foreach ($plates as $plate => $info) {
            $all_alerts[$plate] = $info;
        }
    }

    echo "<pre>";

    $displayed_plates = array(); // This will hold a list of all plates currently displayed so we avoid showing the same plate repeatedly if it is detected multiple times.
    foreach (array_reverse($all_plates) as $timestamp => $plates) {
        if (sizeof($plates) > 0) {
            foreach ($plates as $plate => $guesses) {
                if (in_array($plate, $displayed_plates)) {
                    continue;
                }
                array_push($displayed_plates, $plate);
                $associated_alert = null;
                foreach ($guesses as $guess => $confidence) {
                    if (in_array($guess, array_keys($all_alerts))) { // Check to see if this plate is an active alert.
                        $associated_alert = $all_alerts[$guess];
                    }
                }
                echo "<div style=\"margin-top:10px;padding:10px;border-radius:15px;color:black;";
                if ($associated_alert !== null) {
                    echo "background-color:#ff8888;";
                } else {
                    echo "background-color:#bbbbbb;";
                }
                echo "\">";

                $confidence = reset($guesses);
                echo "<h4>$plate<sup style=\"font-size:14px;\">" . number_format($confidence, 2, ".", "") . "%</sup></h4>";
                echo "<p><i>" . date("Y-m-d H:i:s", $timestamp) . "</i></p>";
                if ($associated_alert != null) {
                    if (in_array("name", array_keys($associated_alert)) and strlen($associated_alert["name"]) > 0) {
                        echo "<p><b>" . $associated_alert["name"] . "</b></p>";
                    }
                    if (in_array("description", array_keys($associated_alert)) and strlen($associated_alert["description"]) > 0) {
                        echo "<p>" . $associated_alert["description"] . "</p>";
                    }
                    if (in_array("vehicle", array_keys($associated_alert))) {
                        $vehicle_info = "";
                        if (in_array("year", array_keys($associated_alert["vehicle"])) and strlen($associated_alert["vehicle"]["year"]) > 0) {
                            $vehicle_info .= " " . $associated_alert["vehicle"]["year"];
                        }
                        if (in_array("make", array_keys($associated_alert["vehicle"])) and strlen($associated_alert["vehicle"]["make"]) > 0) {
                            $vehicle_info .= " " . $associated_alert["vehicle"]["make"];
                        }
                        if (in_array("model", array_keys($associated_alert["vehicle"])) and strlen($associated_alert["vehicle"]["model"]) > 0) {
                            $vehicle_info .= " " . $associated_alert["vehicle"]["model"];
                        }
                        $vehicle_info = trim($vehicle_info);
                        if (strlen($vehicle_info) > 0) {
                            echo "<p>" . $vehicle_info . "</p>";
                        }
                    }
                }
                if ($config["show_guesses"] == true) { // Check to see if Cortex is configured to show guesses.
                    echo "<ul>";
                    foreach ($guesses as $guess => $confidence) {
                        echo "<li>" . $guess . " - " . round($confidence*10)/10 . "%</li>";
                    }
                    echo "</ul>";
                }
                echo "</div>";
            }
        }
    }
} else {
    echo "<p>The interface directory does yet exist.</p>";
}


?>
