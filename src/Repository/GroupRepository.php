<?php
namespace Ngmin\Ict2216G5\Repository;
use Ngmin\Ict2216G5\Entity\Group;

interface GroupRepository
{
    public function getLastGroupNumberByParams(string $acadYear, string $trimester, string $moduleCode, string $labGroup): int;

    public function addGroup(Group $group): void;

    public function getGroup(int $groupId): ?Group;
    public function getGroupsByUser(string $studentId): array;
    public function updateGroup(Group $group): void;
}
?>