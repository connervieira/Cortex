<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Instance Settings</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./settings.php">Back</a>
            <a class="button" role="button" href="./instancerecovery.php">Recovery</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Settings</h2>
        <br>
        <main>
            <?php
            verify_permissions($config);

            $instance_config = load_instance_config();

            if (determine_predator_variant() != "vanilla") {
                echo "<p class=\"error\">This page is only meant to configure vanilla Predator. If you know what you are doing, you can use the <a href='./settingsinstanceadvanced.php'>advanced configuration interface</a> to directly modify the configuration file.</p>";
                exit();
            }






            if ($_POST["submit"] == "Submit") { // Check to see if the form has been submitted.

                // Load the values from the input form:

                $input_values = array();
                $input_values["general"]["working_directory"] = $_POST["general>working_directory"];
                $input_values["general"]["interface_directory"] = $_POST["general>interface_directory"];

                $input_values["general"]["gps"]["enabled"] = $_POST["general>gps>enabled"];
                $input_values["realtime"]["gps"]["alpr_location_tagging"] = $_POST["realtime>gps>alpr_location_tagging"];

                $input_values["general"]["alpr"]["engine"] = $_POST["general>alpr>engine"];
                $input_values["general"]["alpr"]["validation"]["guesses"] = intval($_POST["general>alpr>validation>guesses"]);
                $input_values["general"]["alpr"]["validation"]["confidence"] = floatval($_POST["general>alpr>validation>confidence"]);
                if ($_POST["general>alpr>validation>best_effort"] == "on") {
                    $input_values["general"]["alpr"]["validation"]["best_effort"] = true;
                } else {
                    $input_values["general"]["alpr"]["validation"]["best_effort"] = false;
                }

                $input_values["general"]["alpr"]["validation"]["license_plate_format"] = array();
                $original_validation_template_count = sizeof($instance_config["general"]["alpr"]["validation"]["license_plate_format"]);
                for ($i = 0; $i <= $original_validation_template_count + 1; $i++) { // Run once for each template in the configuration, plus one to account for the new entry.
                    $this_format = $_POST["general>alpr>validation>templates" . $i];
                    $this_format = strtoupper($this_format);
                    $this_format = str_replace(" ", "", $this_format);
                    if (strlen($this_format) > 0) {
                        array_push($input_values["general"]["alpr"]["validation"]["license_plate_format"], $this_format);
                    }
                }

                $input_values["general"]["alerts"]["databases"]= array();
                $original_database_count = sizeof($instance_config["general"]["alerts"]["databases"]);
                for ($i = 0; $i <= $original_database_count + 1; $i++) { // Run once for each database in the configuration, plus one to account for the new entry.
                    $this_database = $_POST["general>alerts>databases>" . $i];
                    $this_database = trim($this_database);
                    if (strlen($this_database) > 0) {
                        array_push($input_values["general"]["alerts"]["databases"], $this_database);
                    }
                }



                // Validate the values from the input form:
                $valid = true; // By default, assume the configuration is valid until an invalid value is found.

                foreach ($input_values["general"]["alerts"]["databases"] as $database) {
                    if (strlen($database) <= 0 or strlen($database) >= 100) {
                        echo "<p class=\"error\">The length of one of the alert databases is outside of the expected range.</p>";
                        $valid = false;
                    }
                    print_r(str_starts_with("http://", $database));
                    if (str_starts_with(strtolower($database), "http://") == false and str_starts_with(strtolower($database), "https://") == false and file_exists($instance_config["general"]["working_directory"] . "/" . $database) == false and file_exists($input_values["general"]["working_directory"] . "/" . $database) == false) {
                        echo "<p class=\"error\">The '" . htmlspecialchars($input_values["general"]["working_directory"] . "/" . $database) . "' alert database does not exist and does not appear to be a URL (starting with 'https://' or 'http://').</p>";
                        $valid = false;
                    }
                }

                foreach ($input_values["general"]["alpr"]["validation"]["license_plate_format"] as $template) {
                    if (strlen($template) <= 0 or strlen($template) >= 10) {
                        echo "<p class=\"error\">The length of one of the validation templates is outside of the expected range.</p>";
                        $valid = false;
                    }
                    if ($template != preg_replace("/[^A-Z0-9]/", '', $template)) {
                        echo "<p class=\"error\">The one of the validation templates contains disallowed characters.</p>";
                        $valid = false;
                    }
                }

                $original_device_count = sizeof($instance_config["realtime"]["image"]["camera"]["devices"]); // Count the number of capture devices already in the instance configuration.
                $instance_config["realtime"]["image"]["camera"]["devices"] = array(); // Reset the list of devices in the loaded instance configuration.
                for ($i = 0; $i <= $original_device_count + 1; $i++) { // Run once for each device in the configuration, plus one to account for the new entry.
                    $device_name = $_POST["realtime>image>camera>devices>" . $i . ">name"]; // This will be the key for the capture device.
                    $device_value = $_POST["realtime>image>camera>devices>" . $i . ">value"]; // This is the value of the capture device.
                    if (strlen($device_name) > 0) { // Check to see if the device name is set.
                        if (file_exists($device_value) == true) {
                            if (!in_array($device_value, $instance_config["realtime"]["image"]["camera"]["devices"])) { // Check to make sure there is no capture device that already uses this device value.
                                $instance_config["realtime"]["image"]["camera"]["devices"][$device_name] = $device_value; // Add this device to the instance configuration.
                            } else {
                                echo "<p class='error'>The value for <b>realtime>image>camera>devices>" . $device_name . "</b> value is already used by another capture device.</p>";
                                $valid = false;
                            }
                        } else {
                            echo "<p class='error'>The value for <b>realtime>image>camera>devices>" . $device_name . "</b> value is invalid.</p>";
                            $valid = false;
                        }
                    }
                }



                if (!is_dir($input_values["general"]["working_directory"])) { echo "<p class='error'>The <b>general>working_directory</b> does not point to a valid directory.</p>"; $valid = false; } // Validate that the general>working_directory points to an existing directory.

                if (strtolower($input_values["general"]["gps"]["enabled"]) == "on") { $input_values["general"]["gps"]["enabled"] = true; } else { $input_values["general"]["gps"]["enabled"] = false; } // Convert the general>gps>enabled value to a boolean.
                if (strtolower($input_values["realtime"]["gps"]["alpr_location_tagging"]) == "on") { $input_values["realtime"]["gps"]["alpr_location_tagging"] = true; } else { $input_values["realtime"]["gps"]["alpr_location_tagging"] = false; } // Convert the realtime>gps>alpr_location_tagging value to a boolean.




                // Update the instance configuration file.
                if ($valid == true) { // Check to see if all configuration values were validated.
                    $instance_config["general"]["working_directory"] = $input_values["general"]["working_directory"];
                    $instance_config["general"]["interface_directory"] = $input_values["general"]["interface_directory"];
                    $instance_config["general"]["gps"]["enabled"] = $input_values["general"]["gps"]["enabled"];
                    $instance_config["realtime"]["gps"]["alpr_location_tagging"] = $input_values["realtime"]["gps"]["alpr_location_tagging"];
                    $instance_config["general"]["alpr"]["engine"] = $input_values["general"]["alpr"]["engine"];
                    $instance_config["general"]["alpr"]["validation"]["guesses"] = $input_values["general"]["alpr"]["validation"]["guesses"];
                    $instance_config["general"]["alpr"]["validation"]["confidence"] = $input_values["general"]["alpr"]["validation"]["confidence"];
                    $instance_config["general"]["alpr"]["validation"]["best_effort"] = $input_values["general"]["alpr"]["validation"]["best_effort"];
                    $instance_config["general"]["alpr"]["validation"]["license_plate_format"] = $input_values["general"]["alpr"]["validation"]["license_plate_format"];
                    $instance_config["general"]["alerts"]["databases"] = $input_values["general"]["alerts"]["databases"];




                    if (json_encode($instance_config) == true) { // Verify that the data to be saved to the instance configuration file is valid.
                        $instance_configuration_file = $config["instance_directory"] . "/config.json";
                        if (is_writable($instance_configuration_file) == true) { // Verify that the instance configuration file is writable.
                            file_put_contents($instance_configuration_file, json_encode($instance_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // Save the modified instance configuration to disk.
                            echo "<p class='success'>The configuration was updated successfully.<p>";
                        } else {
                            echo "<p class='error'>The instance configuration file at '" . $instance_configuration_file . "' doesn't appear to be writable. The configuration could not be saved.</p>";
                        }
                    } else {
                        echo "<p class='error'>The modified configuration couldn't be converted into JSON. This should never occur, and is likely a bug. The configuration could not be saved.</p>";
                    }
                } else { // If one or more configuration values is invalid, then don't update the configuration.
                    echo "<p class='error'>The configuration was not updated.<p>";
                    $instance_config = load_instance_config($config); // Reload the instance configuration so invalid information is not placed into the input fields.
                }
            }

            ?>
            <form method="post">
                <div class="buffer">
                    <h3>System</h3>
                    <label for="general>working_directory" title="The directory where Predator will store all semi-permanent files, including logs and videos.">Working Directory: </label><input type="text" id="general>working_directory" name="general>working_directory" pattern="[a-zA-Z0-9-_ /]{0,300}" value="<?php echo $instance_config["general"]["working_directory"]; ?>"><br><br>
                    <label for="general>interface_directory" title="The directory where Predator places temporary files for communicating with Cortex.">Interface Directory: </label><input type="text" id="general>interface_directory" name="general>interface_directory" pattern="[a-zA-Z0-9-_ /]{0,300}" value="<?php echo $instance_config["general"]["interface_directory"]; ?>"><br><br>
                </div>
                <div class="buffer">
                    <h3>Capture</h3>
                    <div class="buffer">
                        <h4>Cameras</h4>
                        <?php
                        $displayed_cameras = 0;
                        foreach (array_keys($instance_config["realtime"]["image"]["camera"]["devices"]) as $key) {
                            echo '<div class="buffer">';
                            echo '    <h5>Device "' . $key . '"</h5>';
                            echo '    <label for="realtime>image>camera>devices>' . $displayed_cameras . '>name" title="The name that will be used as this capture device\'s ID.">Name: </label><input type="text" id="realtime>image>camera>devices>' . $displayed_cameras . '>name" name="realtime>image>camera>devices>' . $displayed_cameras . '>name" min="0" max="10" value="' . $key . '"><br><br>';
                            echo '    <label for="realtime>image>camera>devices>' . $displayed_cameras . '>value" title="The file of the capture device on the system.">Value: </label><input type="text" id="realtime>image>camera>devices>' . $displayed_cameras . '>value" name="realtime>image>camera>devices>' . $displayed_cameras . '>value" value="' . $instance_config["realtime"]["image"]["camera"]["devices"][$key] . '">';
                            echo '</div>';
                            $displayed_cameras++;
                        }
                        ?>
                        <div class="buffer">
                            <h5>New Device</h5>
                            <label for="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>name" title="The name that will be used as this capture device's ID.">Name: </label><input type="text" id="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>name" name="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>name" max="10"><br><br>
                            <label for="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>value" title="The file of the capture device on the system.">Value: </label><input type="text" id="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>value" name="realtime>image>camera>devices><?php echo $displayed_cameras; ?>>value">
                        </div>
                    </div>
                </div>
                <div class="buffer">
                    <h3>GPS</h3>
                    <label for="general>gps>enabled" title="Globally enables GPS functionality">Enabled: </label><input type="checkbox" id="general>gps>enabled" name="general>gps>enabled" <?php if ($instance_config["general"]["gps"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                    <label for="realtime>gps>alpr_location_tagging" title="Determines whether Predator will GPS tag ALPR results in the plate log file">Tagging: </label><input type="checkbox" id="realtime>gps>alpr_location_tagging" name="realtime>gps>alpr_location_tagging" <?php if ($instance_config["realtime"]["gps"]["alpr_location_tagging"] == true) { echo "checked"; } ?>>
                </div>
                <div class="buffer">
                    <h3>License Plate Recognition</h3>
                    <div class="buffer">
                        <h4>Analysis</h4>
                        <label for="general>alpr>engine" title="Determines the engine Predator is expecting when running ALPR">Engine:</label>
                        <select id="general>alpr>engine" name="general>alpr>engine">
                            <option value="phantom" <?php if ($instance_config["general"]["alpr"]["engine"] == "phantom") { echo "selected"; } ?>>Phantom ALPR</option>
                            <option value="openalpr" <?php if ($instance_config["general"]["alpr"]["engine"] == "openalpr") { echo "selected"; } ?>>OpenALPR</option>
                        </select><br><br>
                        <div class="buffer">
                            <h4>Validation</h4>
                            <label for="general>alpr>validation>guesses" title="Determines the number of guesses the ALPR back-end will make for the contents of each individual plate">Guesses: </label><input class="compactinput" type="number" step="1" min="1" max="50" id="general>alpr>validation>guesses" name="general>alpr>validation>guesses" value="<?php echo $instance_config["general"]["alpr"]["validation"]["guesses"]; ?>"><br><br>
                            <label for="general>alpr>validation>confidence" title="Determines the minimum needed confidence percentage to consider a plate guess valid.">Confidence: </label><input class="compactinput" type="number" step="1" min="5" max="100" id="general>alpr>validation>confidence" name="general>alpr>validation>confidence" value="<?php echo $instance_config["general"]["alpr"]["validation"]["confidence"]; ?>"><br><br>
                            <label for="general>alpr>validation>best_effort" title="Determines if Predator will accept the most likely guess if all guesses are considered invalid by the validation rules">Best Effort: </label><input type="checkbox" id="general>alpr>validation>best_effort" name="general>alpr>validation>best_effort" <?php if ($instance_config["general"]["alpr"]["validation"]["best_effort"] == true) { echo "checked"; } ?>><br><br>
                            <label title="If any formats are defined here, then Predator will only accept plates that match at least one template (Ex: AAA0000 allows any 3 letters followed by any 4 numbers)">Templates: </label><br>
                            <?php
                            $displayed_formats = 0;
                            if (sizeof($instance_config["general"]["alpr"]["validation"]["license_plate_format"]) > 0) {
                                foreach ($instance_config["general"]["alpr"]["validation"]["license_plate_format"] as $template) {
                                    echo "<input type=\"text\" id=\"general>alpr>validation>templates" . $displayed_formats . "\" name=\"general>alpr>validation>templates" . $displayed_formats . "\" placeholder=\"Leave Blank To Remove\" value=\"" . $template . "\"><br>";
                                    $displayed_formats += 1;
                                }
                            } else {
                                echo "<p><i>No templates are currently defined.</i></p>";
                            }
                            echo "<input type=\"text\" id=\"general>alpr>validation>templates" . $displayed_formats . "\" name=\"general>alpr>validation>templates" . $displayed_formats . "\" placeholder=\"New Template\"><br>";
                            ?>
                            <br>
                        </div>
                    </div>
                    <div class="buffer">
                        <h4>Alerts</h4>
                        <label title="This is a list of alert database sources. These can be either local files (relative to the working directory) or HTTP/HTTPS URLs.">Databases: </label><br>
                        <?php
                        $displayed_databases = 0;
                        if (sizeof($instance_config["general"]["alerts"]["databases"]) > 0) {
                            foreach ($instance_config["general"]["alerts"]["databases"] as $database) {
                                echo "<input type=\"text\" id=\"general>alerts>databases>" . $displayed_databases . "\" name=\"general>alerts>databases>" . $displayed_databases . "\" placeholder=\"Leave Blank To Remove\" value=\"" . $database . "\"><br>";
                                $displayed_databases += 1;
                            }
                        } else {
                            echo "<p><i>No databases are currently configured.</i></p>";
                        }
                        echo "<input type=\"text\" id=\"general>alerts>databases>" . $displayed_databases . "\" name=\"general>alerts>databases>" . $displayed_databases . "\" placeholder=\"New Database Path/URL\"><br>";
                        ?>
                    </div>
                </div>

                <br><br><input type="submit" id="submit" name="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
