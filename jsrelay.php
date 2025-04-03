<?php
// This page serves as a relay to allow JavaScript scripts to read server-side information.

include "./config.php";
include "./utils.php";

$info["is_alive"] = is_alive($config);
$info["last_heartbeat"] = last_heartbeat_age($config);

echo json_encode($info);

?>
