<?php
require_once __DIR__ . '/../config/session.php';
session_destroy();
header('Location: /PamudiNew/auth/login.php');
exit;
?>
