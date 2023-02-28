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
            <a class="button" role="button" href="./instancerecovery.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Settings</h2>
        <p>This tool allows you to edit the instance configuration file directly. Unless you know exactly what you are doing, you should use the normal configuration interface to make changes. Changes made here are much more likely to cause errors.</p>
        <main>
            <?php
             // Load the instance configuration file from the disk.
            verify_permissions($config);

            $instance_configuration_file = $config["instance_directory"] . "/config.json";
            $raw_instance_configuration = file_get_contents($instance_configuration_file);


            if ($_POST["updatedconfig"] !== null) {
                if (json_decode($_POST["updatedconfig"])) {
                    file_put_contents($instance_configuration_file, $_POST["updatedconfig"]);
                } else {
                    echo "<p class='error'>The configuration submitted is not valid JSON.</p>";
                }
            }

            ?>
            <form method="post">
                <textarea id="updatedconfig" name="updatedconfig" style="width:100%;height:500px;"><?php
                    if ($_POST["updatedconfig"] !== null) {
                        echo $_POST["updatedconfig"];
                    } else {
                        echo $raw_instance_configuration;
                    }
                    ?></textarea>
                <br><br><input type="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
