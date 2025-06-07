<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Control\GroupControl;
use Ngmin\Ict2216G5\Mapper\GroupMapper;
use Ngmin\Ict2216G5\Mapper\GroupMembershipMapper;

// Initialize control class
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo);

// Fetch lab groups from the database
$labGroupStmt = $pdo->query("SELECT labGroupCode, moduleCode FROM labGroups");
$labGroupRows = $labGroupStmt->fetchAll(PDO::FETCH_ASSOC);

// Group lab groups by moduleCode
$labGroups = [];
foreach ($labGroupRows as $row) {
    $labGroups[$row['moduleCode']][] = $row['labGroupCode'];
}

// Get form data
$acadYear = $_POST['acadYear'] ?? null;
$trimester = $_POST['trimester'] ?? null;
$moduleCode = $_POST['moduleCode'] ?? null;
$labGroup = $_POST['labGroup'] ?? '';
$maxMembers = (int) ($_POST['maxGroupSize'] ?? 0);

// Get admin ID from session
$adminId = $_SESSION['user']['id'] ?? null; // from login

// TODO: Do we need to store previous inputs? in case of error?
try {
    if (!$adminId) {
        throw new Exception("User not logged in.");
    }

    // Validate required fields
    if (!$acadYear || !$trimester || !$moduleCode || $maxMembers <= 0) {
        throw new Exception("Missing or invalid form fields.");
    }

    if (array_key_exists($moduleCode, $labGroups)) {
        if (count($labGroups[$moduleCode]) > 0 && !$labGroup) {
            throw new Exception("Lab group is required for this module.");
        }

        if (!empty($labGroup) && !in_array($labGroup, $labGroups[$moduleCode])) {
            throw new Exception("Invalid lab group for the selected module.");
        }
    }

    // Create group
    $group = $groupControl->createGroup($acadYear, $trimester, $moduleCode, $maxMembers, $adminId, $labGroup);

    // Redirect or show success
    header("Location: groups.php");
    exit;

} catch (Exception $e) {
    // Handle error: log it or show message
    $_SESSION['error'] = $e->getMessage();
    header("Location: group_create.php");
    exit;
}
?>