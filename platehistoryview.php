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
        <title><?php echo $config["product_name"]; ?> - Files</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Plate History</h2>
        <p>This utility allows you to view the license plate history in various formats.</p>
        <br>
        <main>
            <a class="button" role="button" href="?format=json">JSON</a>
            <a class="button" role="button" href="?format=csv">CSV</a>
            <hr class="separator">
            <div>
                <?php
                $instance_config = load_instance_config($config);
                $plate_history_file = $instance_config["general"]["working_directory"] . "/" . $instance_config["realtime"]["saving"]["license_plates"]["file"];

                $selected_format = $_GET["format"];

                if (isset($selected_format) == true) { // Check to see if the user has selected a directory.

                    if ($selected_format == "json") {
                        $file_contents = file_get_contents($plate_history_file);
                        $file_contents = json_encode(json_decode($file_contents), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        $output = $file_contents;
                    } else if ($selected_format == "csv") {
                        $output = "";
                        $file_contents = file_get_contents($plate_history_file);
                        $plate_history = json_decode($file_contents, true);
                        foreach (array_keys($plate_history) as $timestamp) {
                            foreach (array_keys($plate_history[$timestamp]["plates"]) as $plate) {
                                $output = $output . date("Y-m-d H:i:s", $timestamp) . "," . $plate;
                                if (sizeof($plate_history[$timestamp]["plates"][$plate]["alerts"]) > 0) { $output = $output . ",true";
                                } else { $output = $output . ",false"; }
                                $output = $output . "," . $plate_history[$timestamp]["location"]["lat"] . "," . $plate_history[$timestamp]["location"]["lon"];
                                $output = $output . "\n";
                            }
                        }
                    } else {
                        echo "<p class='warning'>Unknown format selected.</p>";
                        exit();
                    }

                    echo '<a class="button" role="button" href="./platehistorydownload.php?format=' . $selected_format . '">Download</a>';

                    echo "<pre style='text-align:left;white-space: pre-wrap;'>";
                    if ($selected_format == "csv") {
                        echo "time,plate,alert,latitude,longitude<br><br>";
                    }
                    echo $output;
                    echo "</pre>";
                } else {
                    echo "<p><i>Select a format to view.</i></p>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
