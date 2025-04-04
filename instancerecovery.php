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
        <title><?php echo $config["product_name"]; ?> - Recovery</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./settingsinstancevanilla.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Recovery</h2>
        <main>
            <p>This page provides tools to recover a broken instance without manually interacting with it. The tools on this page should only be used if you know exactly what you're doing. Incorrect usage can cause more harm than good, and make further recovery efforts more difficult.</p>

            <?php
             // Load the instance configuration file from the disk.

            $instance_configuration_file = $config["instance_directory"] . "/config.json";


            verify_permissions($config); // Verify that PHP has all of the appropriate permissions.


            if ($_POST["flashsource"] !== null) { // Check to see if the remote source flash form was submitted.
                $flash_source = $_POST["flashsource"];

                if (filter_var($flash_source, FILTER_VALIDATE_URL) == true) { // Check to make sure the remote flash source is a URL.
                    $remote_config = file_get_contents($flash_source); // Fetch the raw contents from the remote source.
                    if (json_decode($remote_config) == true) { // Check to see if the response from the remote source is valid JSON.
                        $remote_config = json_decode($remote_config, true); // Convert the response from the remote source into an array.
                        if (array_key_exists("general", $remote_config) and array_key_exists("image", $remote_config) and array_key_exists("alpr", $remote_config) and array_key_exists("network", $remote_config) and array_key_exists("developer", $remote_config)) { // Check to make sure all of the main configuration sections exists in the response from the remote source.
                            file_put_contents($instance_configuration_file, json_encode($remote_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // Save the downloaded configuration file to disk.
                            echo "<p>The configuration file from the remote source was successfully applied.</p>";
                        } else if (array_key_exists("general", $remote_config) and array_key_exists("management", $remote_config) and array_key_exists("prerecorded", $remote_config) and array_key_exists("realtime", $remote_config) and array_key_exists("dashcam", $remote_config)) { // Check to make sure all of the main configuration sections exists in the response from the remote source.
                        } else {
                            echo "<p class='error'>The response provided by the remote flash source doesn't appear to have all of the basic configuration sections for Predator or Predator Fabric.</p>";
                        }
                    } else {
                        echo "<p class='error'>The response provided by the remote flash source doesn't appear to be valid JSON.</p>";
                    }
                } else {
                    echo "<p class='error'>The flash source provided was not a valid URL.</p>";
                }


            } else {
                if ($_GET["action"] == "dump") {
                    echo "<a class=\"button\" href=\"instancerecovery.php\">Back</a>";
                    echo "<div style=\"text-align:left\">";
                    echo "    <pre>" . file_get_contents($instance_configuration_file) . "</pre>";
                    if (json_decode(file_get_contents($instance_configuration_file))) {
                        echo "<p style=\"color:#00ff00;\">Valid JSON</p>";
                    } else {
                        echo "<p style=\"color:#ff0000;\">Invalid JSON</p>";
                    }
                    echo "    <a class=\"button\" role=\"button\" href=\"?\">Clear</a>";
                    echo "</div>";
                } else if ($_GET["action"] == "lsinstance") {
                    echo "<a class=\"button\" href=\"instancerecovery.php\">Back</a>";
                    $ls_output = shell_exec("ls -l -A " . $config["instance_directory"]); // Execute the 'ls' command and record its output.
                    $ls_output_array = explode("\n", $ls_output); // Convert the command output into an array, separated by line.
                    echo "<div style=\"text-align:left\">";
                    foreach ($ls_output_array as $line) { // Iterate through each line in the 'ls' command output.
                    echo "    <p>" . $line . "</p>"; // Print each line of the command output.
                    }
                    echo "    <a class=\"button\" role=\"button\" href=\"?\">Clear</a>";
                    echo "</div>";
                } else if ($_GET["action"] == "lsinterface") {
                    echo "<a class=\"button\" href=\"instancerecovery.php\">Back</a>";
                    $ls_output = shell_exec("ls -l -A " . $config["interface_directory"]); // Execute the 'ls' command and record its output.
                    $ls_output_array = explode("\n", $ls_output); // Convert the command output into an array, separated by line.
                    echo "<div style=\"text-align:left\">";
                    foreach ($ls_output_array as $line) { // Iterate through each line in the 'ls' command output.
                    echo "    <p>" . $line . "</p>"; // Print each line of the command output.
                    }
                    echo "    <a class=\"button\" role=\"button\" href=\"?\">Clear</a>";
                    echo "</div>";
                }
            }

            ?>

            <br><br><h3>Configuration</h3>
            <p>This tool allow in-depth configuration changes to be made.</p>
            <a class="button" role="button" href="./settingsinstanceadvanced.php">Advanced&nbsp;Configuration</a><br>

            <br><br><h3>Diagnostics</h3>
            <p>These tools provide information about the state of the instance.</p>
            <a class="button" role="button" href="?action=dump">Print&nbsp;Instance&nbsp;Configuration</a><br>
            <a class="button" role="button" href="?action=lsinstance">Print&nbsp;Instance&nbsp;Directory</a><br>
            <a class="button" role="button" href="?action=lsinterface">Print&nbsp;Interface&nbsp;Directory</a><br>

            <br><br><h3>Back-up</h3>
            <p>These tools create, delete, view, and restore back-ups of the instance configuration.</p>
            <a class="button" role="button" href="./instancebackup.php">Manage&nbsp;Back-ups</a><br>

            <br><br><h3>Rescue</h3>
            <p>This tool rescues a severely corrupted instances by flashing a configuration file from a remote source. This will overwrite your existing configuration file.</p>
            <form method="POST">
                <label for="flashsource">Source:</label> <input type="url" id="flashsource" name="flashsource" value=""><br>
                <input type="submit" class="button" value="Flash">
            </form>
        </main>
    </body>
</html>
