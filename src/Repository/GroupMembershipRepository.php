<?php
namespace App\Repository;

interface GroupMembershipRepository
{
    public function isMember(int $groupId, string $studentId): bool;
    public function addMember(int $groupId, string $studentId, string $role = 'member'): void;
    public function getRoleForUser(int $groupId, int $studentId): ?string;

    public function getGroupId(int $groupMembersId): ?int;
}
?>
