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

        verify_permissions($config);

        $instance_configuration_file = $config["instance_directory"] . "/config.json";

        $raw_instance_configuration = file_get_contents($instance_configuration_file);
        $instance_config = json_decode($raw_instance_configuration, true);


        if (isset($instance_config["developer"]["working_directory"])) { // Check to see if the configuration layout reflects that the instance Predator Fabric.
            $image_file = $instance_config["developer"]["working_directory"] . "/" . $instance_config["image"]["camera"]["file_name"];
        } else if (isset($instance_config["general"]["working_directory"])) { // Check to see if the configuration layout reflects that the instance is vanilla Predator.
            $image_file = $instance_config["general"]["working_directory"] . "/" . $instance_config["realtime"]["image"]["camera"]["file_name"] . ".jpg";
        } else {
            echo "<p class=\"error\">The image file could not be located given the instance configuration file.</p>";
        }

        $image_data = fread(fopen($image_file, "r"), filesize($image_file));

        echo "<div style=\"width:100%;text-align:center;\">";
        echo "    <img style=\"max-width:100%;max-height:680px;\" src='" . 'data:image;base64,' . base64_encode($image_data) . "' />";
        echo "</div>";


        ?>
    </body>
</html>
