<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupJoinRequestsMapper;
use App\Entity\GroupJoinRequests;

$groupId = $_POST['groupId'] ?? null;
$studentId = $_SESSION['user']['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

$mapper = new GroupJoinRequestsMapper($pdo);

if (!$mapper->requestExists($studentId, $groupId)) {
    $request = new GroupJoinRequests(
        0,
        (int)$groupId,
        (int)$studentId,
        'pending',
        new DateTime()
    );

    $mapper->addRequest($request);
}

header("Location: group_info.php?groupId=$groupId");
exit;
?>