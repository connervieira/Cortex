<?php
// This page serves as a relay to allow JavaScript scripts to read server-side information.

include "./config.php";
include "./utils.php";

$info["is_alive"] = is_alive($config);

echo json_encode($info);

?>
