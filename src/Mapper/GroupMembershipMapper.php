<?php
namespace Ngmin\Ict2216G5\Mapper;

use Ngmin\Ict2216G5\Repository\GroupMembershipRepository;
use PDO;

class GroupMembershipMapper implements GroupMembershipRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function isMember(int $groupId, string $studentId): bool
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

    public function addMember(int $groupId, string $studentId, string $role = 'member'): void
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
}

?>