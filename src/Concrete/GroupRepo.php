<?php
namespace Ngmin\Ict2216G5\Concrete;

use Ngmin\Ict2216G5\Entity\Group;
use Ngmin\Ict2216G5\Repository\GroupRepository;
use PDO;

class GroupRepo implements GroupRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getLastGroupByParams(string $acadYear, string $trimester, string $moduleCode, int $labGroup): ?Group {
        $stmt = $this->dbConnection->prepare("
            SELECT * FROM `groups`
            WHERE acadYear = :acadYear
              AND moduleCode = :moduleCode
              AND labGroupCode = :labGroup
            ORDER BY groupNumber DESC
            LIMIT 1
        ");
        $stmt->execute([
            ':acadYear' => $acadYear,
            ':moduleCode' => $moduleCode,
            ':labGroup' => $labGroup
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $group = new Group(
                $row['acadYear'],
                $row['moduleCode'],
                $row['labGroupCode'],
                $row['groupNumber'],
                (int)$row['maxGroupSize'],
                (int)$row['groupStatus'],
                $row['noOfMembers']
            );

            $group->generateGroupName();

            return $group;
        }

        return null;
    }

    public function addGroup(Group $group): void {
        $stmt = $this->dbConnection->prepare("
            INSERT INTO `groups` (groupName, acadYear, moduleCode, labGroupCode, groupNumber, noOfMembers, maxGroupSize, groupStatus)
            VALUES (:groupName, :acadYear, :moduleCode, :labGroupCode, :groupNumber, :noOfMembers, :maxGroupSize, :groupStatus)
        ");

        $stmt->execute([
            ':groupName'    => $group->getGroupName(),
            ':acadYear'     => $group->getAcadYear(),
            ':moduleCode'   => $group->getModuleCode(),
            ':labGroupCode' => $group->getLabGroup(),
            ':groupNumber'  => $group->getGroupNumber(),
            ':maxGroupSize' => $group->getMaxMembers(),
            ':groupStatus'  => $group->getGroupStatus(),
            ':noOfMembers'  => $group->getNoOfMembers()
        ]);
    }

    public function getAllActiveGroups(): array {
        $stmt = $this->dbConnection->prepare("SELECT * FROM `groups` WHERE groupStatus = 'active'");
        $stmt->execute();

        $groups = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $group = new Group(
                $row['acadYear'],
                $row['moduleCode'],
                $row['labGroupCode'],
                $row['groupNumber'],
                (int)$row['maxGroupSize'],
                (int)$row['groupStatus'],
                $row['noOfMembers']
            );

            $group->generateGroupName();

            $groups[] = $group;
        }

        return $groups;
    }
}
?>
