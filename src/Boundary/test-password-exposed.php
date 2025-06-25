<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use DivineOmega\PasswordExposed\Enums\PasswordStatus;

$password = 'password123';
$status = password_exposed($password);

echo "Status: ";
switch ($status) {
    case PasswordStatus::EXPOSED:
        echo "EXPOSED\n";
        break;
    case PasswordStatus::NOT_EXPOSED:
        echo "NOT EXPOSED\n";
        break;
    case PasswordStatus::UNKNOWN:
        echo "UNKNOWN\n";
        break;
    default:
        echo "Unexpected status: $status\n";
}
?>