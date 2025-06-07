<?php
namespace Ngmin\Ict2216G5\Entity;

class GroupMembership
{
    private int $groupMembershipId;
    private int $studentId;
    private int $groupId;
    private string $role; // e.g., 'member', 'leader'

    // Getters
    public function getGroupMembershipId(): int
    {
        return $this->groupMembershipId;
    }
    public function getStudentId(): int
    {
        return $this->studentId;
    }
    public function getGroupId(): int
    {
        return $this->groupId;
    }
    public function getRole(): string
    {
        return $this->role;
    }

    // Setters
    private function setGroupMembershipId(int $groupMembershipId): void
    {
        $this->groupMembershipId = $groupMembershipId;
    }
    private function setStudentId(int $studentId): void
    {
        $this->studentId = $studentId;
    }
    private function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }
    private function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin'; // return true if the role is 'admin'
    }

    public function __construct(int $groupId, int $studentId, string $role = 'member')
    {
        $this->setGroupId($groupId);
        $this->setStudentId($studentId);
        $this->setRole($role);
    }

    public function assignGroupMembershipId(int $id): void
    {
        if (isset($this->groupMembershipId)) {
            throw new \LogicException("ID already set");
        }
        $this->setGroupMembershipId($id);
    }
}
?>