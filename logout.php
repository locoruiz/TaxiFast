<?php
    session_start();
    header('Cache-control: private');
     if (isset($_GET["csrf"]) && $_GET["csrf"] == $_SESSION["token"]) {
        $_SESSION = array();
        session_destroy();
	}
	header("Location: login.html");
?>