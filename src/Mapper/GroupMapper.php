<?php
namespace App\Mapper;

use App\Entity\Group;
use App\Repository\GroupRepository;
use PDO;

class GroupMapper implements GroupRepository
{

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    private function mapRowToGroup(array $row): Group
    {
        $group = new Group(
            $row['acadYear'],
            $row['trimester'],
            $row['moduleCode'],
            $row['labGroupCode'],
            (int) $row['groupNumber'],
            (int) $row['maxGroupSize'],
            $row['groupStatus'],
            (int) $row['noOfMembers']
        );
        $group->assignGroupId((int) $row['groupId']);
        $group->assignGroupName($row['groupName']);
        return $group;
    }
    public function getLastGroupNumberByParams(string $acadYear, string $trimester, string $moduleCode, string $labGroup = ''): int
    {
        $query = "
            SELECT groupNumber 
            FROM `groups`
            WHERE acadYear = :acadYear
                AND trimester = :trimester
                AND moduleCode = :moduleCode";

        // Optional lab group clause
        if ($labGroup !== '') {
            $query .= " AND labGroupCode = :labGroup";
        } else {
            $query .= " AND (labGroupCode IS NULL OR labGroupCode = '')";
        }

        $query .= " ORDER BY groupNumber DESC LIMIT 1";

        $stmt = $this->dbConnection->prepare($query);

        $params = [
            ':acadYear' => $acadYear,
            ':trimester' => $trimester,
            ':moduleCode' => $moduleCode
        ];
        if ($labGroup !== '') {
            $params[':labGroup'] = $labGroup;
        }

        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['groupNumber'] : 0; // Return 0 if no group found
    }

    public function addGroup(Group $group): void
    {
        $stmt = $this->dbConnection->prepare("
            INSERT INTO `groups` (groupName, acadYear, trimester, moduleCode, labGroupCode, groupNumber, noOfMembers, maxGroupSize, groupStatus)
            VALUES (:groupName, :acadYear, :trimester, :moduleCode, :labGroupCode, :groupNumber, :noOfMembers, :maxGroupSize, :groupStatus)
        ");

        $stmt->execute([
            ':groupName' => $group->getGroupName(),
            ':acadYear' => $group->getAcadYear(),
            ':trimester' => $group->getTrimester(),
            ':moduleCode' => $group->getModuleCode(),
            ':labGroupCode' => $group->getLabGroup() !== '' ? $group->getLabGroup() : null,
            ':groupNumber' => $group->getGroupNumber(),
            ':maxGroupSize' => $group->getMaxMembers(),
            ':groupStatus' => $group->getGroupStatus(),
            ':noOfMembers' => $group->getNoOfMembers()
        ]);

        // Assign the last inserted ID to the groupId property
        $group->assignGroupId((int) $this->dbConnection->lastInsertId());
    }

    public function updateGroup(Group $group): void
    {
        $stmt = $this->dbConnection->prepare("
            UPDATE `groups`
            SET noOfMembers = :noOfMembers
            WHERE groupId = :groupId
        ");

        $stmt->execute([
            ':groupId' => $group->getGroupId(),
            ':noOfMembers' => $group->getNoOfMembers()
        ]);
    }

    public function getAllActiveGroups(): array
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM `groups` WHERE groupStatus = 'active'");
        $stmt->execute();

        $groups = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $group = $this->mapRowToGroup($row);

            // Add the group to the array
            $groups[] = $group;
        }

        return $groups;
    }

    public function getGroup(int $groupId): ?Group
    {
        $stmt = $this->dbConnection->prepare("
            SELECT * 
            FROM `groups` 
            WHERE groupId = :groupId
        ");
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $group = $this->mapRowToGroup($result);
            return $group;
        }
        return null; // Return null if no group found
    }
    
    public function getActiveGroupsByUser(int $studentId): array
    {
        $stmt = $this->dbConnection->prepare("
            SELECT g.*
            FROM `groups` g
            INNER JOIN groupMembers gm ON g.groupId = gm.groupId
            WHERE gm.studentId = :studentId
            AND g.groupStatus = 'active'
        ");
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        $groups = [];
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $group = $this->mapRowToGroup($row);
            $groups[] = $group;
        }
        return $groups; // Return an array of Group objects
    }

    public function getGroupsByModuleName(string $moduleName): array 
    {
        $stmt = $this->dbConnection->prepare("
            SELECT g.* 
            FROM `groups` g
            JOIN modules m ON g.moduleCode = m.moduleCode
            WHERE m.moduleName LIKE :moduleName
            AND g.groupStatus = 'active'
        ");
        $stmt->execute([':moduleName' => '%' . $moduleName . '%']);

        $groups = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $groups[] = $this->mapRowToGroup($row);
        }
        return $groups;
    }


    // update group status
    public function updateGroupStatus(int $groupId, string $status): void
    {
        $stmt = $this->dbConnection->prepare("
            UPDATE `groups`
            SET groupStatus = :status
            WHERE groupId = :groupId
        ");

        $stmt->execute([
            ':groupId' => $groupId,
            ':status' => $status
        ]);
    }
}
?>