<?php
include "./config.php";


// The `is_alive` function checks to see if the linked instance is running, based on its heartbeat.
function is_alive($config) {
    $instance_config = load_instance_config($config);
    $heartbeat_file_path = $instance_config["general"]["interface_directory"] . "/heartbeat.json";
    if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
        if (file_exists($heartbeat_file_path)) { // Check to see if the heartbeat file exists.
            $heartbeat_log = json_decode(file_get_contents($heartbeat_file_path), true); // Load the heartbeat file from JSON data.
        } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
            $heartbeat_log = array(); // Set the heartbeat log to an empty array.
        }
    } else {
        $heartbeat_log = array(); // Set the heartbeat log to an empty array.
    }


    $last_heartbeat = time() - floatval(end($heartbeat_log)); // Calculate how many seconds ago the last heartbeat was.

    if ($last_heartbeat < $config["heartbeat_threshold"]) { // Only consider the system online if it's last heartbeat was within a certain number of seconds ago.
        return true;
    } else { // If the last heartbeat exceeded the time to be considered online, display a message that the system is offline.
        return false;
    }
}



// The `verify_permissions` function checks to see if all permissions are set correctly, and that all files are in their expected locations.
function verify_permissions($config) {
    $verify_command = "sudo -u " . $config["exec_user"] . " echo verify"; // Prepare the command to verify permissions.
    $command_output = shell_exec($verify_command); // Execute the command, and record its output.
    $command_output = trim($command_output); // Remove whitespaces from the end and beginning of the command output.

    $instance_configuration_file = $config["instance_directory"] . "/config.json";

    if ($command_output !== "verify") { // Check to see if the command output differs from the expected output.
        echo "<p class=\"error\">PHP does not have the necessary permissions to manage this system as '" . $config["exec_user"] . "' using the '" . shell_exec("whoami") . "' user.</p>"; // Display an error briefly explaining the problem.
        exit(); // Terminate the script.
    }


    if (is_writable("./") == false) { // Check to se if the controller interface's root directory is writable.
        echo "<p class=\"error\">The controller interface's root directory is not writable. Please verify the permissions of the " . getcwd() . " directory.</p>";
    } else if (is_writable("./start.sh") == false) { // Check to see if the controller interface's start script is writable.
        echo "<p class=\"error\">The start.sh script in the " . getcwd() . " directory is not writable.</p>";
    }

    if (is_writable($instance_configuration_file) == false) { shell_exec("timeout 2 sudo -u " . $config["exec_user"] . " chmod 777 " . $instance_configuration_file); } // Attempt to correct permissions on the instance configuration file before running the permissions check.

    if (is_dir($config["instance_directory"]) == false) { // Check to see if the root Predator instance directory exists.
        echo "<p class=\"error\">The instance directory doesn't appear to exist. Please adjust the controller configuration.</p>";
        echo "<a class=\"button\" href=\"./settingscontroller.php\">Controller Settings</a>";
    } else if (file_exists($instance_configuration_file) == false) { // Check to see if the instance configuration file exists.
        echo "<p class=\"error\">The instance configuration couldn't be located at " . $instance_configuration_file . ". Please verify that the interface configuration points to the correct instance root directory.</p>";
    } else if (is_writable($instance_configuration_file) == false) { // Check to see if the instance configuration file is writable.
        echo "<p class=\"error\">The instance configuration isn't writable. Please verify that the instance configuration file at " . $instance_configuration_file . " has the correct permissions to be modified by external programs.</p>";
    } else if (!json_decode(file_get_contents($instance_configuration_file))) {
        echo "<p class=\"error\">The instance configuration doesn't appear to be valid JSON. Please verify that the instance configuration file at " . $instance_configuration_file . " is valid.</p>";
    }

}


function load_instance_config() {
    global $config;

    $instance_configuration_file = $config["instance_directory"] . "/config.json";

    if (is_dir($config["instance_directory"]) == false) { // Check to see if the instance directory exists.
        echo "<p class='error'>The instance directory doesn't appear to exist. Please adjust the controller configuration.</p>";
        exit();
    }
    if (file_exists($instance_configuration_file) == false) { // Check to see if the Predator configuration file exists.
        echo "<p class='error'>The instance configuration couldn't be located. Please verify that the interface configuration points to the correct instance directory.</p>";
        exit();
    }
    if (is_writable($instance_configuration_file) == false) {
        echo "<p class='error'>Please make sure the instance configuration file is writable to make configuration modifications.</p>";
        exit();
    }

    $raw_instance_configuration = file_get_contents($instance_configuration_file);
    $instance_config = json_decode($raw_instance_configuration, true);

    return $instance_config;
}


function determine_predator_variant() {
    $instance_config = load_instance_config();

    if (isset($instance_config["general"]) and isset($instance_config["image"]) and isset($instance_config["alpr"]) and isset($instance_config["network"])) { // If this statement is true, then the configuration is likely Predator Fabric.
        // Do nothing, since this is the intended situation.
        return "fabric";
    } else if (isset($instance_config["general"]) and isset($instance_config["management"]) and isset($instance_config["prerecorded"]) and isset($instance_config["realtime"]) and isset($instance_config["dashcam"]) and isset($instance_config["developer"])) { // If this statement is true, then the configuration is likely vanilla Predator.
        return "vanilla";
    } else { // If neither of the statements above are true, then it is likely that the configuration file is corrupt.
        echo "<p class=\"error\">The instance configuration appears to be incomplete. Please ensure that your Predator configuration file is valid.</p>";
    }
}
?>
