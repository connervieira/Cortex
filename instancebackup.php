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
        <title><?php echo $config["product_name"]; ?> - Instance Back-up</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./instancerecovery.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Back-up</h2>
        <main>
            <?php
            verify_permissions($config); // Verify that PHP has all of the appropriate permissions, and that all files are where they're expected to be.


             // Load the instance configuration file from the disk.

            $instance_configuration_file = $config["instance_directory"] . "/config.json";
            $backup_location = "./instancebackups";


            // Assume everything is set-up properly until a problem is found.
            $status["config_exists"] = true;
            $status["writable"] = true;



            if (is_dir($config["instance_directory"]) == false) { // Check to see if the Predator Fabric instance  directory exists.
                echo "<p class='error'>The instance directory doesn't appear to exist. Please adjust the interface configuration.</p>";
                echo "<a class=\"button\" href=\"./settingsinterface.php\">Interface Settings</a>";
                exit();
            }
            if (is_writable($config["instance_directory"]) == false) { // Check to make sure the instance directory is writable.
                echo "<p class='error'>The instance directory doesn't appear to be writable.</p>";
            }

            if (file_exists($instance_configuration_file) == false) { // Check to see if the instance configuration file exists.
                // Don't display an error yet, since it's possible the user has selected to restore a backup. This same check will be run again after the user inputs are processed.
                $status["config_exists"] = false;
            }


            if (is_writable("./") == false) {
                echo "<p class='error'>The controller interface's directory is not writable. Please verify the permissions of the " . getcwd() . " directory.</p>";
                $status["writable"] = false;
            }


            if (is_dir($backup_location) == false) { // Check to see if the backup location doesn't exist.
                if (is_writable("./")) {
                    mkdir($backup_location); // Create the backup location directory.
                } else {
                    echo "<p class='error'>The backup directory could not be created at " . $backup_location . " due to a permissions issue.</p>";
                    exit();
                }
            }
            if (is_writable($backup_location) == false) { // Check to make sure the backup directory is writable.
                echo "<p class='error'>The backup directory doesn't appear to be writable. Please verify the permissions of the " . $backup_location . " directory.</p>";
                $status["writable"] = false;
            }

            if (is_writable($backup_location) == false) { // Check to make sure the backup directory is writable.
                echo "<p class='error'>The backup directory doesn't appear to be writable. Please verify the permissions of the " . $backup_location . " directory.</p>";
                $status["writable"] = false;
            }



            if ($_POST["backup"] !== null) { // Check to see if the backup form was submitted.
                $current_config_contents = file_get_contents($instance_configuration_file); // Load the raw contents of the current instance configuration file.
                file_put_contents($backup_location . "/" . time() . ".json", $current_config_contents); // save the raw contents of the current instance configuration file to a back-up file, named after the current Unix time.
            } else if ($_POST["view"] !== null) { // Check to see if the view form was submitted.
                if (file_exists($backup_location . "/" . $_POST["id"]) == true) { // Check to see if the specified file exists.
                    echo "<div style=\"text-align:left\">";
                    echo "    <pre>" . file_get_contents($backup_location . "/" . $_POST["id"]) . "</pre>";
                    if (json_decode(file_get_contents($backup_location . "/" . $_POST["id"]))) {
                        echo "<p style=\"color:#00ff00;\">Valid JSON</p>";
                    } else {
                        echo "<p style=\"color:#ff0000;\">Invalid JSON</p>";
                    }
                    echo "    <a class=\"button\" role=\"button\" href=\"?\">Clear</a>";
                    echo "</div>";
                } else {
                    echo "<p>The specified backup file doesn't appear to exist.</p>";
                }
            } else if ($_POST["delete"] !== null) { // Check to see if the delete form was submitted.
                if (file_exists($backup_location . "/" . $_POST["id"]) == true) { // Check to see if the specified file exists.
                    if (unlink($backup_location . "/" . $_POST["id"]) == true) { // Attempt to delete the specified backup file.
                        echo "<p>The specified backup file was deleted.</p>";
                    } else {
                        echo "<p class='error'>The specified backup file could not be deleted for an unknown reason.</p>";
                    }
                } else {
                    echo "<p class='error'>The specified backup file doesn't appear to exist.</p>";
                }
            } else if ($_POST["restore"] !== null) { // Check to see if the restore form was submitted.
                if (file_exists($backup_location . "/" . $_POST["id"]) == true) { // Check to see if the specified file exists.
                    $backup_config_contents = file_get_contents($backup_location . "/" . $_POST["id"]); // Load the raw contents of the specified backup configuration file.
                    if (file_put_contents($instance_configuration_file, $backup_config_contents)) { // Save the raw contents of the specified backup configuration file to the active instance configuration file.
                    } else {
                        echo "<p class='error'>The specified configuration backup could not be restored for an unknown reason.</p>";
                    }
                } else {
                    echo "<p class='error'>The specified backup file doesn't appear to exist.</p>";
                }
            }



            if (file_exists($instance_configuration_file) == false) { // Check to see if the instance configuration file exists.
                echo "<p>The instance configuration couldn't be located. Please verify that the interface configuration points to the correct instance directory.</p>";
                $status["config_exists"] = false;
            } else {
                $status["config_exists"] = true;
            }


            $backups = scandir($backup_location); // Load the list of files from the backup directory.
            $backups = array_diff($backups, array('..', '.')); // Remove the directory pointers ('.' and '..') from the list of backups.

            ?>
            <h3>List</h3>
            <?php
            if (sizeof($backups) > 0) {
                foreach ($backups as $backup) {
                    echo "<p><a href=\"?id=" . $backup . "\">" . $backup . "</a></p>";
                }
            } else {
                echo "<p><i>There are no backup files.</i></p>";
            }

            ?>

            <br><br>
            <h3>Manage</h3>
            <form method="POST">
                <input class="button" role="button" type="submit" value="Create Backup" name="backup" <?php if ($status["writable"] == false or $status["config_exists"] == false) { echo "style=\"color:#777777;\" disabled"; } ?>>
            </form>
            <br><br><form method="POST">
                <label for="id">File:</label> <input type="text" value="<?php echo $_GET["id"]; ?>" name="id"><br>
                <input class="button" role="button" type="submit" value="View Backup" name="view">
            </form>
            <br><br><form method="POST">
                <label for="id">File:</label> <input type="text" value="<?php echo $_GET["id"]; ?>" name="id" <?php if ($status["writable"] == false) { echo "style=\"color:#777777;\" disabled"; } ?>><br>
                <input class="button" role="button" type="submit" value="Delete Backup" name="delete">
            </form>
            <br><br><form method="POST">
                <label for="id">File:</label> <input type="text" value="<?php echo $_GET["id"]; ?>" name="id" <?php if ($status["writable"] == false) { echo "style=\"color:#777777;\" disabled"; } ?>><br>
                <input class="button" role="button" type="submit" value="Restore Backup" name="restore">
            </form>
        </main>
    </body>
</html>
