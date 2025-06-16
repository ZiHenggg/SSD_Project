<?php
namespace App\Mapper;

use App\Entity\GroupMembership;
use App\Repository\GroupMembershipRepository;
use PDO;

class GroupMembershipMapper implements GroupMembershipRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function isMember(int $groupId, int $studentId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM groupMembers 
            WHERE groupId = :groupId AND studentId = :studentId
        ");
        $stmt->execute([
            ':groupId' => $groupId,
            ':studentId' => $studentId
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function getRoleForUser(int $groupId, int $studentId): ?string
    {
        $stmt = $this->db->prepare("
        SELECT role 
        FROM groupMembers 
        WHERE groupId = :groupId AND studentId = :studentId
    ");
        $stmt->execute([
            ':groupId' => $groupId,
            ':studentId' => $studentId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['role'] : null;
    }
    public function getMembers(int $groupId): array
    {
        $stmt = $this->db->prepare("
            SELECT gm.*, s.name
            FROM groupMembers gm
            JOIN students s ON gm.studentId = s.studentId
            WHERE gm.groupId = :groupId
            ORDER BY gm.role ASC, gm.studentId ASC
        ");
        $stmt->execute([':groupId' => $groupId]);

        $groupMembers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $membership = new GroupMembership(
                (int) $row['groupId'],
                (int) $row['studentId'],
                $row['role']
            );
            if (isset($row['groupMembershipId'])) {
                $membership->assignGroupMembershipId((int) $row['groupMembershipId']);
            }
            if (isset($row['name'])) {
                $membership->assignStudentName($row['name']);
            }
            $groupMembers[] = $membership;
        }
        return $groupMembers;
    }

    public function addMember(int $groupId, int $studentId, string $role = 'member'): void
    {
        $stmt = $this->db->prepare("
        INSERT INTO groupMembers (groupId, studentId, role)
        VALUES (:groupId, :studentId, :role)
    ");
        $stmt->execute([
            ':groupId' => $groupId,
            ':studentId' => $studentId,
            ':role' => $role
        ]);
    }

    public function getGroupId(int $groupMembersId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT groupId 
            FROM groupMembers 
            WHERE groupMembersId = :groupMembersId
        ");
        $stmt->execute([':groupMembersId' => $groupMembersId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int) $result['groupId'] : null;
    }
}

?>