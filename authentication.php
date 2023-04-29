<?php
session_start();
if ($_SESSION['authid'] == "cortex") {
	$username = $_SESSION['username'];
} else {
    if ($force_login_redirect == true) {
        header("Location: ./login.php");
        exit();
    }
}

?>

