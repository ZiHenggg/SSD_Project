<?php
namespace App\Repository;

interface GroupMembershipRepository
{
    public function isMember(int $groupId, int $studentId): bool;
    public function addMember(int $groupId, int $studentId, string $role = 'member'): void;
    public function getRoleForUser(int $groupId, int $studentId): ?string;

    public function getMembers(int $groupId): array;
    public function getGroupId(int $groupMembersId): ?int;
}
?>
