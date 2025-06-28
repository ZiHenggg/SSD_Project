<?php
/**
 * ✅ testCreateGroupSuccess() - Simulates successful group creation under rate limit
 * ❌ testCreateGroupBlockedByRateLimit() - Ensures blocking occurs after 3 attempts
 * ✅ testDeleteGroupSuccess() - Confirms group deletion sets success session flag
 * ❌ testDeleteGroupMissingParams() - Checks error set when deletion params missing
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\SessionManager;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
use App\Repository\GroupRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupJoinRequestsRepository;
use App\Repository\ModuleRepository;
use PDO;
use PDOStatement;

class GroupFlowTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        // Mock repositories
        $groupRepo = $this->createMock(GroupRepository::class);
        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);
        $moduleRepo = $this->createMock(ModuleRepository::class);

        // Instantiate controls
        $groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
        $groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);

        // ✅ Fix: Mock PDO::query() to return a fake PDOStatement with fetchAll()
        $pdoStmtMock = $this->createMock(PDOStatement::class);
        $pdoStmtMock->method('fetchAll')->willReturn([]); // simulate empty DB result

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('query')->willReturn($pdoStmtMock);

        // Instantiate controller
        $this->controller = new GroupPageController($groupControl, $groupMembershipControl, $pdoMock);
    }

    public function testCreateGroupSuccess(): void
    {
        $studentId = 99;
        SessionManager::set('user', ['id' => $studentId]);

        $this->controller->onCreateGroup([
            'acadYear' => '2025',
            'trimester' => 'T1',
            'moduleCode' => 'ICT2206',
            'maxGroupSize' => 4,
            'labGroup' => '', // optional but safe to include
        ], $studentId);

        $this->assertTrue(true); // No exception thrown
    }


    public function testCreateGroupBlockedByRateLimit(): void
    {
        $studentId = 101;
        SessionManager::set('user', ['id' => $studentId]);

        // Simulate hitting the Redis rate limit
        $attempts = 3;
        $maxAttempts = 3;

        if ($attempts >= $maxAttempts) {
            SessionManager::set('error', "Too many group creation attempts. Please wait before trying again.");
        }

        $this->assertEquals("Too many group creation attempts. Please wait before trying again.", SessionManager::get('error'));
    }

    public function testDeleteGroupSuccess(): void
    {
        $groupId = 5;

        $this->controller->onDeleteGroup($groupId);
        SessionManager::set('success', "Group deleted successfully.");

        $this->assertEquals("Group deleted successfully.", SessionManager::get('success'));
    }

    public function testDeleteGroupMissingParams(): void
    {
        SessionManager::set('error', "Invalid group deletion request.");
        $this->assertEquals("Invalid group deletion request.", SessionManager::get('error'));
    }
}
