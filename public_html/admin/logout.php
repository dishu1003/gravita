<?php
require __DIR__ . '/../includes/config.php';
session_destroy();
header('Location: /admin/login.php');