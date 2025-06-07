<?php
namespace Ngmin\Ict2216G5\Repository;

interface GroupMembershipRepository
{
    public function isMember(int $groupId, string $studentId): bool;
    public function addMember(int $groupId, string $studentId, string $role = 'member'): void;
}
?>