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

        $image_file = $config["image_stream"];

        if (file_exists($image_file) == true) { // Check to make sure the image file exists before displaying it.
            $image_data = fread(fopen($image_file, "r"), filesize($image_file));

            echo "<div style=\"width:100%;text-align:center;\">";
            echo "    <img style=\"max-width:100%;max-height:680px;\" src='" . 'data:image;base64,' . base64_encode($image_data) . "' />";
            echo "</div>";
        } else {
            echo "<p><i>No image available.</i></p>";
        }
        ?>
    </body>
</html>
