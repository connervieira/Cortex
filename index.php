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
        <title><?php echo $config["product_name"]; ?> - Dashboard</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./logout.php">Logout</a>
            <a class="button" role="button" href="./settings.php">Settings</a><br>
        </div>
        <div class="navbar">
            <img class="logo" src="./img/logodark.svg" alt="Logo">
        </div>
        <div class="logocontainer">
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Dashboard</h2>
        <?php
        verify_permissions($config); // Verify that PHP has all of the appropriate permissions.

        $action = $_GET["action"];
        if ($action == "start") {
            if (!file_exists("./start.sh")) {
                file_put_contents("./start.sh", ""); // Create the start script.
            }
            if (is_writable("./start.sh")) {
                file_put_contents("./start.sh", "python3 " . $config["instance_directory"] . "/main.py &"); // Update the start script.
            } else {
                echo "<p class=\"error\">The start.sh script is not writable.</p>";
                exit();
            }
            if (file_exists("./start.sh")) { // Verify that the start script exists.
                if (is_alive($config) == false) {
                    $start_command = "sudo -u " . $config["exec_user"] . " sh ./start.sh"; // Prepare the command to start an instance.
                    shell_exec($start_command . ' > /dev/null 2>&1 &'); // Start an instance.
                    header("Location: ."); // Reload the page to remove any arguments from the URL.
                } else {
                    echo "<p class=\"error\">There seems to already be an instance active.</p>";
                    echo "<p class=\"error\">Please stop any existing instances before launching another.</p>";
                }
            } else {
                echo "<p class=\"error\">The start script doesn't appear to exist.</p>";
                echo "<p class=\"error\">The program could not be started.</p>";
            }
        } else if ($action == "stop") {
            shell_exec("sudo killall python3"); // Kill all Python executables.
            header("Location: ."); // Reload the page to remove any arguments from the URL.
        }
        ?>
        <main>
            <div class="display">
                <h3>Control</h3>
                <?php
                if (is_alive($config) == true) {
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#aaaaaa" role="button" href="#">Start</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#ffffff" role="button" href="?action=stop">Stop</a>';
                } else {
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#ffffff" role="button" href="?action=start">Start</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#aaaaaa" role="button" href="#">Stop</a>';
                }

                echo $start_button;
                echo $stop_button;
                ?>
                <br><br>
                <iframe id="statusframe" title="Status Frame" src="./status.php"></iframe>
            </div>
            <div class="display">
                <h3>Plates</h3>
                <iframe id="platesframe" title="Plates Frame" src="./plates.php" height="200px"></iframe>
            </div>
            <div class="display">
                <h3>Errors</h3>
                <iframe id="errorsframe" title="Errors Frame" src="./errors.php"></iframe>
            </div>
            <?php
            if ($config["preview_display"] == true) { // Check to see if the preview display is enabled.
                echo '
                <div class="display">
                    <h3>Preview</h3>
                    <iframe id="previewframe" title="Preview Frame" src="./preview.php" height="700px"></iframe>
                </div>
                ';
            }
            ?>
        </main>
    </body>
    <?php
    if ($config["auto_refresh"] == "client") {
        echo "
        <script>
            setInterval(() => {
                document.getElementById('statusframe').contentWindow.location.reload(true);
                document.getElementById('platesframe').contentWindow.location.reload(true);
                document.getElementById('errorsframe').contentWindow.location.reload(true);";
                if ($config["preview_display"] == true) { // Check to see if the preview display is enabled.
                    echo "document.getElementById('previewframe').contentWindow.location.reload(true);";
                }
            echo "
            }, 1000);
        </script>
        ";
    }
    ?>
    <script>
        const fetch_info = async () => {
            console.log("Fetching instance status");
            const response = await fetch('./jsrelay.php'); // Fetch the status information using the JavaScript relay page.
            const result = await response.json(); // Parse the JSON data from the response.

            // Update the control buttons based on the instance status.
            if (result.is_alive) {
                document.getElementById("startbutton").style.color = "#aaaaaa";
                document.getElementById("startbutton").href = "#";
                document.getElementById("stopbutton").style.color = "#ffffff";
                document.getElementById("stopbutton").href = "?action=stop";
            } else {
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=start";
                document.getElementById("stopbutton").style.color = "#aaaaaa";
                document.getElementById("stopbutton").href = "#";
            }
        }

        setInterval(() => { fetch_info(); }, 500); // Execute the instance fetch script every 500 milliseconds.
    </script>
</html>
