<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\SessionManager;

SessionManager::start();
SessionManager::destroy();

header("Location: login.php");
exit;
