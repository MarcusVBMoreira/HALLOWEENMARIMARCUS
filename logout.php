<?php

require_once 'includes/config.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "login.php");
exit;