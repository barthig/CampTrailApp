<?php
require __DIR__ . '/../../src/config.php';
session_unset();
session_destroy();
header('Location: login.php');
exit;
