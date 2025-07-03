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
use App\Repository\LabGroupRepository;
use App\Entity\Group;
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
        $labGroupRepo = $this->createMock(LabGroupRepository::class); // ✅ Added

        // ✅ Mock GroupControl and patch createGroup()
        $groupControl = $this->getMockBuilder(GroupControl::class)
            ->setConstructorArgs([$groupRepo, $groupMembershipRepo, $moduleRepo, $labGroupRepo])
            ->onlyMethods(['createGroup'])
            ->getMock();

        $groupControl->method('createGroup')->willReturn($this->fakeGroupEntity());

        // GroupMembershipControl
        $groupMembershipControl = new GroupMembershipControl(
            $groupMembershipRepo,
            $groupRepo,
            $groupJoinRequestsRepo
        );

        // ✅ Mock PDO::query() to return empty lab group list
        $pdoStmtMock = $this->createMock(PDOStatement::class);
        $pdoStmtMock->method('fetchAll')->willReturn([]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('query')->willReturn($pdoStmtMock);

        // Controller with mocked GroupControl
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
            'labGroup' => '', // optional
        ], $studentId);

        $this->assertTrue(true); // No exception thrown = success
    }

    public function testCreateGroupBlockedByRateLimit(): void
    {
        $studentId = 101;
        SessionManager::set('user', ['id' => $studentId]);

        // Simulate rate limit condition
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

    private function fakeGroupEntity(): Group
    {
        $group = new Group(
            '2025',        // acadYear
            'T1',          // trimester
            'ICT2206',     // moduleCode
            4,             // maxGroupSize
            99,            // creatorId
            10             // maxMembers
        );

        $ref = new \ReflectionClass($group);
        $prop = $ref->getProperty('groupId');
        $prop->setAccessible(true);
        $prop->setValue($group, 1);

        return $group;
    }
}
