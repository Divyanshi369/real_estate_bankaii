<?php
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: /public/index.php');
exit;


