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
        <title><?php echo $config["product_name"]; ?> - Settings</title>
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
        <main>
            <?php
            verify_permissions($config);

            $instance_config = load_instance_config();

            if (determine_predator_variant() != "fabric") {
                echo "<p class=\"error\">This page is only meant to configure Predator Fabric. If you know what you are doing, you can use the <a href='./settingsinstanceadvanced.php'>advanced configuration interface</a> to directly modify the configuration file.</p>";
                exit();
            }





            // Load the values from the input form.

            $input_values = array();
            $input_values["general"]["interval"] = $_POST["general>interval"];


            $input_values["image"]["camera"]["provider"] = $_POST["image>camera>provider"];
            $input_values["image"]["camera"]["device"] = $_POST["image>camera>device"];
            $input_values["image"]["camera"]["resolution"] = $_POST["image>camera>resolution"];

            $input_values["image"]["processing"]["cropping"]["enabled"] = $_POST["image>processing>cropping>enabled"];
            $input_values["image"]["processing"]["cropping"]["left_margin"] = $_POST["image>processing>cropping>left_margin"];
            $input_values["image"]["processing"]["cropping"]["right_margin"] = $_POST["image>processing>cropping>right_margin"];
            $input_values["image"]["processing"]["cropping"]["top_margin"] = $_POST["image>processing>cropping>top_margin"];
            $input_values["image"]["processing"]["cropping"]["bottom_margin"] = $_POST["image>processing>cropping>bottom_margin"];

            $input_values["image"]["processing"]["rotation"]["enabled"] = $_POST["image>processing>rotation>enabled"];
            $input_values["image"]["processing"]["rotation"]["angle"] = $_POST["image>processing>rotation>angle"];


            $input_values["alpr"]["engine"] = $_POST["alpr>engine"];
            $input_values["alpr"]["guesses"] = $_POST["alpr>guesses"];
            $input_values["alpr"]["confidence"] = $_POST["alpr>confidence"];


            $input_values["network"]["identifier"] = $_POST["network>identifier"];
            $input_values["network"]["results_submission"]["target"] = $_POST["network>results_submission>target"];
            $input_values["network"]["remote_processing"]["target"] = $_POST["network>remote_processing>target"];
            $input_values["network"]["remote_processing"]["mode"] = $_POST["network>remote_processing>mode"];





            // Validate the values from the input form.

            if (isset($input_values["general"]["interval"]) and $input_values["general"]["interval"] != null) { // Check to see if the form has been submitted.
                $valid = true; // By default, assume the configuration is valid until an invalid value is found.


                $input_values["general"]["interval"] = floatval($input_values["general"]["interval"]); // Convert the general>interval value to a decimal number.
                if ($input_values["general"]["interval"] < 0 or $input_values["general"]["interval"] > 60) { echo "<p class='error'>The <b>general>interval</b> value is invalid.</p>"; $valid = false; } // Validate that the general>interval number is within the expected range.



                if ($input_values["image"]["camera"]["provider"] !== "fswebcam" and $input_values["image"]["camera"]["provider"] !== "imagesnap" and $input_values["image"]["camera"]["provider"] !== "off") { echo "<p class='error'>The <b>image>camera>provider</b> value is not a recognized option.</p>"; $valid = false; }
                $input_values["image"]["camera"]["device"] = filter_var($input_values["image"]["camera"]["device"], FILTER_SANITIZE_STRING); // Sanitize the image>camera>device input.
                if (file_exists($input_values["image"]["camera"]["device"]) == false) { echo "<p class='error'>The <b>image>camera>device</b> value doesn't appear to point to a valid file.</p>"; $valid = false; } // Verify that the image>camera>device input points to a valid file.
                if ($input_values["image"]["camera"]["resolution"] !== "960x540" and $input_values["image"]["camera"]["resolution"] !== "1280x720" and $input_values["image"]["camera"]["resolution"] !== "1920x1080" and $input_values["image"]["camera"]["resolution"] !== "2560x1440" and $input_values["image"]["camera"]["resolution"] !== "3840x2160") { echo "<p class='error'>The <b>image>camera>resolution</b> value is not a recognized option.</p>"; $valid = false; } // Validate that the image>camera>resolution is an expected option.
                if (strtolower($input_values["image"]["processing"]["cropping"]["enabled"]) == "on") { $input_values["image"]["processing"]["cropping"]["enabled"] = true; } else { $input_values["image"]["processing"]["cropping"]["enabled"] = false; } // Convert the image>processing>cropping>enabled value to a boolean.
                $input_values["image"]["processing"]["cropping"]["left_margin"] = intval($input_values["image"]["processing"]["cropping"]["left_margin"]); // Convert the image>processing>cropping>left_margin value to a whole number.
                $input_values["image"]["processing"]["cropping"]["right_margin"] = intval($input_values["image"]["processing"]["cropping"]["right_margin"]); // Convert the image>processing>cropping>right_margin value to a whole number.
                $input_values["image"]["processing"]["cropping"]["top_margin"] = intval($input_values["image"]["processing"]["cropping"]["top_margin"]); // Convert the image>processing>cropping>top_margin value to a whole number.
                $input_values["image"]["processing"]["cropping"]["bottom_margin"] = intval($input_values["image"]["processing"]["cropping"]["bottom_margin"]); // Convert the image>processing>cropping>bottom_margin value to a whole number.
                if ($input_values["image"]["processing"]["cropping"]["left_margin"] < 0) { echo "<p class='error'>The <b>image>processing>cropping>left_margin</b> value can not be negative.</p>"; $valid = false; } // Validate that the image>processing>cropping>left_margin value is greater than 0.
                if ($input_values["image"]["processing"]["cropping"]["right_margin"] < 0) { echo "<p class='error'>The <b>image>processing>cropping>right_margin</b> value can not be negative.</p>"; $valid = false; } // Validate that the image>processing>cropping>right_margin value is greater than 0.
                if ($input_values["image"]["processing"]["cropping"]["top_margin"] < 0) { echo "<p class='error'>The <b>image>processing>cropping>top_margin</b> value can not be negative.</p>"; $valid = false; } // Validate that the image>processing>cropping>top_margin value is greater than 0.
                if ($input_values["image"]["processing"]["cropping"]["bottom_margin"] < 0) { echo "<p class='error'>The <b>image>processing>cropping>bottom_margin</b> value can not be negative.</p>"; $valid = false; } // Validate that the image>processing>cropping>bottom_margin value is greater than 0.
                if (explode("x", $input_values["image"]["camera"]["resolution"])[1] - $input_values["image"]["processing"]["cropping"]["left_margin"] - $input_values["image"]["processing"]["cropping"]["right_margin"] <= 0) { echo "<p class='error'>The <b>image>processing>cropping>left_margin</b> and <b>image>processing>cropping>right_margin</b> are too big for the <b>image>camera>resolution</b> value.</p>"; $valid = false; } // Validate that the horizontal cropping margins don't conflict with each other.
                if (explode("x", $input_values["image"]["camera"]["resolution"])[0] - $input_values["image"]["processing"]["cropping"]["top_margin"] - $input_values["image"]["processing"]["cropping"]["bottom_margin"] <= 0) { echo "<p class='error'>The <b>image>processing>cropping>top_margin</b> and <b>image>processing>cropping>bottom_margin</b> are too big for the <b>image>camera>resolution</b> value.</p>"; $valid = false; } // Validate that the vertical cropping margins don't conflict with each other.

                if (strtolower($input_values["image"]["processing"]["rotation"]["enabled"]) == "on") { $input_values["image"]["processing"]["rotation"]["enabled"] = true; } else { $input_values["image"]["processing"]["rotation"]["enabled"] = false; } // Convert the image>processing>rotation>enabled value to a boolean.
                $input_values["image"]["processing"]["rotation"]["angle"] = intval($input_values["image"]["processing"]["rotation"]["angle"]); // Convert the image>processing>rotation>angle value to a whole number.
                if ($input_values["image"]["processing"]["rotation"]["angle"] < -180 or $input_values["image"]["processing"]["rotation"]["angle"] > 180) { echo "<p class='error'>The <b>image>processing>rotation>angle</b> value is outside of the expected range.</p>"; $valid = false; } // Validate that the image>processing>rotation>angle value is within the expected range.



                if ($input_values["alpr"]["engine"] !== "phantom" and $input_values["alpr"]["engine"] !== "openalpr") { echo "<p class='error'>The <b>alpr>engine</b> value is not a recognized option.</p>"; $valid = false; }
                $input_values["alpr"]["guesses"] = intval($input_values["alpr"]["guesses"]); // Convert the alpr>guesses to a whole number.
                if ($input_values["alpr"]["guesses"] < 1 or $input_values["alpr"]["guesses"] > 50) { echo "<p class='error'>The <b>alpr>guesses</b> value is outside of the expected range.</p>"; $valid = false; } // Validate that the alpr>confidence value is within the expected range.
                $input_values["alpr"]["confidence"] = intval($input_values["alpr"]["confidence"]); // Convert the alpr>confidence to a decimal number.
                if ($input_values["alpr"]["confidence"] < 0 or $input_values["alpr"]["confidence"] > 100) { echo "<p class='error'>The <b>alpr>confidence</b> value is outside of the expected range.</p>"; $valid = false; } // Validate that the alpr>confidence value is within the expected range.



                if (strlen($input_values["network"]["identifier"]) > 100) { echo "<p class='error'>The <b>network>identifier</b> value is longer than expected.</p>"; $valid = false; } // Validate that the network>identifier value is a reasonable length.
                if (filter_var($input_values["network"]["results_submission"]["target"], FILTER_VALIDATE_URL) == false) { echo "<p class='error'>The <b>network>results_submission>target</b> value is not a URL.</p>"; $valid = false; } // Validate that the network>results_submission>target value is a URL.
                if (filter_var($input_values["network"]["remote_processing"]["target"], FILTER_VALIDATE_URL) == false) { echo "<p class='error'>The <b>network>remote_processing>target</b> value is not a URL.</p>"; $valid = false; } // Validate that the network>remote_processing>target value is a URL.
                if ($input_values["network"]["remote_processing"]["mode"] !== "on" and $input_values["network"]["remote_processing"]["mode"] !== "auto" and $input_values["network"]["remote_processing"]["mode"] !== "off") { echo "<p class='error'>The <b>network>remote_processing>mode</b> value is not a recognized option.</p>"; $valid = false; }






                // Update the instance configuration file.

                if ($valid == true) { // Check to see if all configuration values were validated.
                    $instance_config["general"]["interval"] = $input_values["general"]["interval"];


                    $instance_config["image"]["camera"]["provider"] = $input_values["image"]["camera"]["provider"];
                    $instance_config["image"]["camera"]["device"] = $input_values["image"]["camera"]["device"];
                    $instance_config["image"]["camera"]["resolution"] = $input_values["image"]["camera"]["resolution"];

                    $instance_config["image"]["processing"]["cropping"]["enabled"] = $input_values["image"]["processing"]["cropping"]["enabled"];
                    $instance_config["image"]["processing"]["cropping"]["left_margin"] = $input_values["image"]["processing"]["cropping"]["left_margin"];
                    $instance_config["image"]["processing"]["cropping"]["right_margin"] = $input_values["image"]["processing"]["cropping"]["right_margin"];
                    $instance_config["image"]["processing"]["cropping"]["top_margin"] = $input_values["image"]["processing"]["cropping"]["top_margin"];
                    $instance_config["image"]["processing"]["cropping"]["bottom_margin"] = $input_values["image"]["processing"]["cropping"]["bottom_margin"];

                    $instance_config["image"]["processing"]["rotation"]["enabled"] = $input_values["image"]["processing"]["rotation"]["enabled"];
                    $instance_config["image"]["processing"]["rotation"]["angle"] = $input_values["image"]["processing"]["rotation"]["angle"];


                    $instance_config["alpr"]["engine"] = $input_values["alpr"]["engine"];
                    $instance_config["alpr"]["guesses"] = $input_values["alpr"]["guesses"];
                    $instance_config["alpr"]["confidence"] = $input_values["alpr"]["confidence"];


                    $instance_config["network"]["identifier"] = $input_values["network"]["identifier"];
                    $instance_config["network"]["results_submission"]["target"] = $input_values["network"]["results_submission"]["target"];
                    $instance_config["network"]["remote_processing"]["target"] = $input_values["network"]["remote_processing"]["target"];
                    $instance_config["network"]["remote_processing"]["mode"] = $input_values["network"]["remote_processing"]["mode"];


                    if (json_encode($instance_config) == true) { // Verify that the data to be saved to the instance configuration file is valid.
                        if (is_writable($instance_configuration_file) == true) { // Verify that the instance configuration file is writable.
                            file_put_contents($instance_configuration_file, json_encode($instance_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // Save the modified instance configuration to disk.
                            echo "<p>The configuration was updated successfully.<p>";
                        } else {
                            echo "<p class='error'>The instance configuration file doesn't appear to be writable. The configuration could not be saved.</p>";
                        }
                    } else {
                        echo "<p class='error'>The modified configuration couldn't be converted into JSON. This should never occur, and is likely a bug. The configuration could not be saved.</p>";
                    }
                } else { // If one or more configuration values is invalid, then don't update the configuration.
                    echo "<p class='error'>The configuration was not updated.<p>";
                }
            }

            ?>
            <form method="post">


                <div class="buffer">
                    <h3>General</h3>
                    <label for="general>interval">Interval:</label> <input type="number" class="compactinput" id="general>interval" name="general>interval" placeholder="1" step="1" min="0" max="60" value="<?php echo $instance_config["general"]["interval"]; ?>"><br><br>
                </div>


                <div class="buffer">
                    <h3>Image</h3>
                    <div class="buffer">
                        <h4>Camera</h4>
                        <label for="image>camera>provider">Provider:</label>
                        <select id="image>camera>provider" name="image>camera>provider">
                            <option value="fswebcam" <?php if ($instance_config["image"]["camera"]["provider"] == "fswebcam") { echo "selected"; } ?>>FSWebcam</option>
                            <option value="imagesnap" <?php if ($instance_config["image"]["camera"]["provider"] == "imagesnap") { echo "selected"; } ?>>ImageSnap</option>
                            <option value="off" <?php if ($instance_config["image"]["camera"]["provider"] == "off") { echo "selected"; } ?>>Off</option>
                        </select><br><br>
                        <label for="image>camera>device">Device:</label> <input type="text" id="image>camera>device" name="image>camera>device" placeholder="/dev/video0" value="<?php echo $instance_config["image"]["camera"]["device"]; ?>"><br><br>
                        <label for="image>camera>resolution">Max Resolution:</label>
                        <select id="image>camera>resolution" name="image>camera>resolution">
                            <option value="960x540" <?php if ($instance_config["image"]["camera"]["resolution"] == "960x540") { echo "selected"; } ?>>540p</option>
                            <option value="1280x720" <?php if ($instance_config["image"]["camera"]["resolution"] == "1280x720") { echo "selected"; } ?>>720p</option>
                            <option value="1920x1080" <?php if ($instance_config["image"]["camera"]["resolution"] == "1920x1080") { echo "selected"; } ?>>1080p</option>
                            <option value="2560x1440" <?php if ($instance_config["image"]["camera"]["resolution"] == "2560x1440") { echo "selected"; } ?>>1440p</option>
                            <option value="3840x2160" <?php if ($instance_config["image"]["camera"]["resolution"] == "3840x2160") { echo "selected"; } ?>>2160p</option>
                        </select><br><br>
                    </div>
                    <div class="buffer">
                        <h4>Processing</h4>
                        <div class="buffer">
                            <h5>Cropping</h5>
                            <label for="image>processing>cropping>enabled">Enabled: </label><input type="checkbox" id="image>processing>cropping>enabled" name="image>processing>cropping>enabled" <?php if ($instance_config["image"]["processing"]["cropping"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="image>processing>cropping>left_margin">Left Margin: </label><input type="number" class="compactinput" id="image>processing>cropping>left_margin" name="image>processing>cropping>left_margin" min="0" step="1" value="<?php echo $instance_config["image"]["processing"]["cropping"]["left_margin"]?>"><br><br>
                            <label for="image>processing>cropping>right_margin">Right Margin: </label><input type="number" class="compactinput" id="image>processing>cropping>right_margin" name="image>processing>cropping>right_margin" step="1" min="0" value="<?php echo $instance_config["image"]["processing"]["cropping"]["right_margin"]?>"><br><br>
                            <label for="image>processing>cropping>top_margin">Top Margin: </label><input type="number" class="compactinput" id="image>processing>cropping>top_margin" name="image>processing>cropping>top_margin" step="1" min="0" value="<?php echo $instance_config["image"]["processing"]["cropping"]["top_margin"]?>"><br><br>
                            <label for="image>processing>cropping>bottom_margin">Bottom Margin: </label><input type="number" class="compactinput" id="image>processing>cropping>bottom_margin" name="image>processing>cropping>bottom_margin" step="1" min="0" value="<?php echo $instance_config["image"]["processing"]["cropping"]["bottom_margin"]?>"><br><br>
                        </div>
                        <div class="buffer">
                            <h5>Rotation</h5>
                            <label for="image>processing>rotation>enabled">Enabled: </label><input type="checkbox" id="image>processing>rotation>enabled" name="image>processing>rotation>enabled" <?php if ($instance_config["image"]["processing"]["rotation"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="image>processing>rotation>angle">Angle: </label><input type="number" class="compactinput" id="image>processing>rotation>angle" name="image>processing>rotation>angle" step="1" min="-180" max="180" value="<?php echo $instance_config["image"]["processing"]["rotation"]["angle"]?>"><br><br>
                        </div>
                    </div>
                </div>

                <div class="buffer">
                    <h3>ALPR</h3>
                    <label for="alpr>engine">Engine:</label>
                    <select id="alpr>engine" name="alpr>engine">
                        <option value="phantom" <?php if ($instance_config["alpr"]["engine"] == "phantom") { echo "selected"; } ?>>Phantom</option>
                        <option value="openalpr" <?php if ($instance_config["alpr"]["engine"] == "openalpr") { echo "selected"; } ?>>OpenALPR</option>
                    </select><br><br>
                    <label for="alpr>guesses">Guesses:</label> <input type="number" class="compactinput" id="alpr>guesses" name="alpr>guesses" step="1" min="1" max="50" placeholder="10" value="<?php echo $instance_config["alpr"]["guesses"]; ?>"><br><br>
                    <label for="alpr>confidence">Confidence:</label> <input type="number" class="compactinput" id="alpr>confidence" name="alpr>confidence" step="1" min="0" max="100" placeholder="80" value="<?php echo $instance_config["alpr"]["confidence"]; ?>">%<br><br>
                </div>

                <div class="buffer">
                    <h3>Network</h3>
                    <label for="network>identifier">Identifier:</label> <input type="text" id="network>identifier" name="network>identifier" placeholder="abcdef123456789" value="<?php echo $instance_config["network"]["identifier"]; ?>"><br><br>
                    <div class="buffer">
                        <h4>Results Submission</h5>
                        <label for="network>results_submission>target">Results Target:</label> <input type="url" id="network>results_submission>target" name="network>results_submission>target" placeholder="https://service.tld/receiver.php" value="<?php echo $instance_config["network"]["results_submission"]["target"]; ?>"><br><br>
                    </div>
                    <div class="buffer">
                        <h4>Remote Processing</h5>
                        <label for="network>remote_processing>target">Target:</label> <input type="url" id="network>remote_processing>target" name="network>remote_processing>target" placeholder="https://service.tld/image_handler.php" value="<?php echo $instance_config["network"]["remote_processing"]["target"]; ?>"><br><br>
                        <label for="network>remote_processing>mode">Auto:</label>
                        <select id="network>remote_processing>mode" name="network>remote_processing>mode">
                            <option value="on" <?php if ($instance_config["network"]["remote_processing"]["mode"] == "on") { echo "selected"; } ?>>On</option>
                            <option value="auto" <?php if ($instance_config["network"]["remote_processing"]["mode"] == "auto") { echo "selected"; } ?>>Auto</option>
                            <option value="off" <?php if ($instance_config["network"]["remote_processing"]["mode"] == "off") { echo "selected"; } ?>>Off</option>
                        </select><br><br>
                    </div>
                </div>


                <br><br><input type="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
