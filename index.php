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
                file_put_contents("./start.sh", "python3 " . $config["instance_directory"] . "/main.py 2 --headless &"); // Update the start script.
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
            shell_exec("sudo killall python3; sudo killall alpr"); // Kill all Python and ALPR processes.
            header("Location: ."); // Reload the page to remove any arguments from the URL.
        }
        ?>
        <main>
            <div class="display">
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
                <br>
                <p id="lastheartbeat">Last heartbeat: <span id="lastheartbeatdisplay">X</span> seconds</p>
                <br>
                <div id="platesview"></div>
            </div>
            <?php
            if ($config["preview_display"] == true) { // Check to see if the preview display is enabled.
                echo '
                <div class="display">
                    <h3>Preview</h3>
                    <img id="videopreview">
                </div>
                ';
            }
            ?>
        </main>
    </body>
    <script>
        const fetch_info = async () => {
            console.log("Fetching instance status");
            const status_response = await fetch('./jsrelay.php'); // Fetch the status information using the JavaScript relay page.
            const status_result = await status_response.json(); // Parse the JSON data from the response.

            // Update the control buttons based on the instance status.
            if (status_result.is_alive) {
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=restart";
                document.getElementById("startbutton").innerHTML = "Restart";
                document.getElementById("stopbutton").style.color = "#ffffff";
                document.getElementById("stopbutton").href = "?action=stop";
            } else {
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=start";
                document.getElementById("startbutton").innerHTML = "Start";
                document.getElementById("stopbutton").style.color = "#aaaaaa";
                document.getElementById("stopbutton").href = "?action=stop";
            }
            document.getElementById("lastheartbeatdisplay").innerHTML = await status_result.last_heartbeat.toFixed(2);

            const plates_response = await fetch('./plates.php'); // Fetch the status information using the JavaScript relay page.
            if (status_result.is_alive) {
                document.getElementById("lastheartbeat").style = "opacity:1;";
                document.getElementById("platesview").innerHTML = await plates_response.text();
            } else {
                document.getElementById("lastheartbeat").style = "opacity:0.2;";
            }
        }
        setInterval(() => { fetch_info(); }, <?php echo floatval($config["refresh_delay"]); ?>); // Execute the instance fetch script at a regular timed interval.

        const update_preview = async () => {
            const video_preview = await fetch('./preview.php'); // Fetch the status information using the JavaScript relay page.
            document.getElementById("videopreview").src = await video_preview.text();
        }
        <?php if ($config["preview_display"] == true) { // Check to see if the preview display is enabled.
        echo "setInterval(() => { update_preview(); }, 300);";
        } ?>
    </script>
</html>
