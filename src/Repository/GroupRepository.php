<?php
namespace App\Repository;
use App\Entity\Group;

interface GroupRepository
{
    public function getLastGroupNumberByParams(string $acadYear, string $trimester, string $moduleCode, string $labGroup): int;
    public function addGroup(Group $group): void;
    public function getGroup(int $groupId): ?Group;
    public function getGroupsByUser(int $studentId): array;
    public function getActiveGroupsByUser(int $studentId): array;
    public function getGroupsByModuleName(string $moduleName): array;
    public function updateGroup(Group $group): void;
    public function getAllActiveGroups(): array;
    public function updateGroupStatus(int $groupId, string $status): void;
}
?>