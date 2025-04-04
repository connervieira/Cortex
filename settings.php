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
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./index.php">Back</a>
            <a class="button" role="button" href="./management.php">Management</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Settings</h2>
        <main>
            <a class="button" role="button" href="settingscontroller.php">Controller Settings</a>
            <?php
            $predator_variant_connected = determine_predator_variant($instance_config);
            if ($predator_variant_connected == "fabric") {
                echo '<a class="button" role="button" href="settingsinstancefabric.php">Instance&nbsp;Settings</a>';
            } else if ($predator_variant_connected == "vanilla") {
                echo '<a class="button" role="button" href="settingsinstancevanilla.php">Instance&nbsp;Settings</a>';
            }
            ?>
        </main>
    </body>
</html>
