<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";



// Verify the theme from the form input, and apply it now so that the newly selected theme is reflected by the theme that loads when the page is displayed. This process is repeated during the actual configuration validation process later.
if ($_POST["theme"] == "dark"  or $_POST["theme"] == "light") { // Make sure the theme input matches one of the expected options.
    $config["theme"] = $_POST["theme"]; // Update the configuration array.
}

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
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Controller Settings</h2>
        <main>
            <?php
            $valid = true;
            if (isset($_POST["interface_password"])) { // Check to see if the form has been submitted.
                if (preg_match("/^[A-Za-z0-9]*$/", $_POST["interface_password"])) { // Check to see if all of the characters in the submitted password are alphanumeric.
                    if (strlen($_POST["interface_password"]) <= 100) { // Check to make sure the submitted password is not an excessive length.
                        $config["interface_password"] = $_POST["interface_password"]; // Save the submitted interface password to the configuration array.
                    } else {
                        echo "<p class='error'>The interface password can only be 100 characters or less.</p>";
                        $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                    }
                } else {
                    echo "<p class='error'>The interface password can only contain alpha-numeric characters.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (floatval($_POST["refresh_delay"]) >= 10 and floatval($_POST["refresh_delay"]) <= 5000) { // Make sure the refresh delay is within the expected range.
                    $config["auto_refresh"] = floatval($_POST["auto_refresh"]); // Save the submitted refresh delay to the configuration.
                } else {
                    echo "<p class='error'>The refresh delay is outside of the expected range.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if ($_POST["heartbeat_threshold"] >= 1 and $_POST["heartbeat_threshold"] <= 60) { // Make sure the heartbeat threshold input is within reasonably expected bounds.
                    $config["heartbeat_threshold"] = intval($_POST["heartbeat_threshold"]); // Save the submitted heartbeat threshold option to the configuration array.
                } else {
                    echo "<p class='error'>The heartbeat threshold option is not within expected bounds.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if ($_POST["theme"] == "dark"  or $_POST["theme"] == "light") { // Make sure the theme input matches one of the expected options.
                    $config["theme"] = $_POST["theme"]; // Save the submitted theme option to the configuration array.
                } else {
                    echo "<p class='error'>The theme option is not an expected option.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if ($_POST["preview_display"] == "on") {
                    $config["preview_display"] = true;
                } else {
                    $config["preview_display"] = false;
                }
                if ($_POST["show_guesses"] == "on") {
                    $config["show_guesses"] = true;
                } else {
                    $config["show_guesses"] = false;
                }




                if (preg_match("/^[A-Za-z0-9]*$/", $_POST["exec_user"])) { // Check to see if all of the characters in the submitted execution user are alphanumeric.
                    if (strlen($_POST["exec_user"]) <= 100) { // Check to make sure the submitted execution user is not an excessive length.
                        $config["exec_user"] = $_POST["exec_user"]; // Save the submitted execution user to the configuration array.
                    } else {
                        echo "<p class='error'>The execution user can only be 100 characters or less.</p>";
                        $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                    }
                } else {
                    echo "<p class='error'>The execution user can only contain alpha-numeric characters.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (is_dir($_POST["instance_directory"])) { // Make sure the root directory input is actually a directory.
                    $config["instance_directory"] = $_POST["instance_directory"]; // Save the submitted root directory option to the configuration array.
                } else {
                    echo "<p class='error'>The specified root directory does not exist.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (file_exists(dirname($_POST["image_stream"]))) { // Make sure the image stream file specified points to a directory that exists.
                    $config["image_stream"] = $_POST["image_stream"]; // Save the submitted image stream location to the configuration array.
                } else {
                    echo "<p class='error'>The specified image stream directory does not exist.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }




                if ($valid == true) { // Check to see if the entered configuration is completely valid.
                    if (is_writable($config_database_name)) { // Check to make sure the configuration file is writable.
                        file_put_contents($config_database_name, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); // Save the modified configuration to disk.
                        echo "<p>Successfully updated configuration.</p>";
                    } else {
                        echo "<p class='error'>The configuration file is not writable.</p>";
                    }
                } else {
                    echo "<p class='error'>The configuration was not updated.</p>";
                }
            }
            ?>
            <form method="post">
                <h3>Interface Settings</h3>
                <label for="interface_password">Password:</label> <input type="text" id="interface_password" name="interface_password" placeholder="password" pattern="[a-zA-Z0-9]{0,100}" value="<?php echo $config["interface_password"]; ?>"><br><br>
                <label for="refresh_delay" title="Determines how long the web interface waits between automatically refreshing the data">Refresh Delay:</label> <input type="number" id="refresh_delay" name="refresh_delay" placeholder="500" min="75" max="5000" value="<?php echo $config["refresh_delay"]; ?>"> <span>milliseconds</span><br><br>
                <label for="heartbeat_threshold" title="Determines how long Predator must not respond before being considered dead">Heartbeat Threshold:</label> <input type="number" id="heartbeat_threshold" name="heartbeat_threshold" placeholder="5" min="1" max="20" value="<?php echo $config["heartbeat_threshold"]; ?>"> <span>seconds</span><br><br>
                <label for="theme">Theme:</label>
                <select id="theme" name="theme">
                    <option value="dark" <?php if ($config["theme"] == "dark") { echo "selected"; } ?>>Dark</option>
                    <option value="light" <?php if ($config["theme"] == "light") { echo "selected"; } ?>>Light</option>
                </select><br><br>
                <label for="preview_display" title="Determines whether an image previewing what Predator sees will be shown">Preview Display:</label> <input type="checkbox" id="preview_display" name="preview_display" <?php if ($config["preview_display"] == true) { echo "checked"; } ?>><br><br>
                <label for="show_guesses" title="Determines whether or not all guess for each plate will be shown">Show Guesses:</label> <input type="checkbox" id="show_guesses" name="show_guesses" <?php if ($config["show_guesses"] == true) { echo "checked"; } ?>>

                <br><br><h3>Connection Settings</h3>
                <label for="exec_user">Execution User:</label> <input type="text" id="exec_user" name="exec_user" placeholder="Username" pattern="[a-zA-Z0-9]{1,100}" value="<?php echo $config["exec_user"]; ?>"><br><br>
                <label for="instance_directory">Instance Directory:</label> <input type="text" id="instance_directory" name="instance_directory" placeholder="/home/predator/PredatorFabric/" value="<?php echo $config["instance_directory"]; ?>"><br><br>
                <label for="image_stream">Image Stream:</label> <input type="text" id="image_stream" name="image_stream" placeholder="/dev/shm/phantom-webcam.jpg" value="<?php echo $config["image_stream"]; ?>"><br><br>

                <br><br><input type="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
