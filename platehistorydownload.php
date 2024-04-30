<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";

$instance_config = load_instance_config($config);
$plate_history_file = $instance_config["general"]["working_directory"] . "/" . $instance_config["realtime"]["saving"]["license_plates"]["file"];

$selected_format = $_GET["format"];

if (isset($selected_format) == true) { // Check to see if the user has selected a directory.

    if ($selected_format == "json") {
        $file_contents = file_get_contents($plate_history_file);
        $output = json_encode(json_decode($file_contents), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        header('Content-Disposition: attachment; filename="plate_history_export.json"');
        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($output));
        header('Connection: close');
        echo $output;
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
        header('Content-Disposition: attachment; filename="plate_history_export.csv"');
        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($output));
        header('Connection: close');
        echo $output;
    } else {
        echo "Unknown format selected.";
    }
}
