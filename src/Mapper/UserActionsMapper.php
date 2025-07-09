<?php
namespace App\Mapper;

use App\Entity\UserActions;
use App\Repository\UserActionsRepository;
use PDO;

class UserActionsMapper implements UserActionsRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function addAction(UserActions $userActions): void {
        $stmt = $this->dbConnection->prepare("INSERT INTO userActions (studentId, actionType) VALUES (:studentId, :actionType)");

        $studentId = $userActions->getStudentId();
        $actionType = $userActions->getActionType();

        // Bind the variables
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':actionType', $actionType);
        $stmt->execute();
    }

    // update interval later
    public function getActionCountByStudentId(int $studentId, string $actionType): int {
        $stmt = $this->dbConnection->prepare("
        SELECT COUNT(*) AS requestCount
            FROM userActions
            WHERE studentId = :studentId
            AND actionType = :actionType
            AND actionTimestamp > NOW() - INTERVAL 30 SECOND;
        ");
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':actionType', $actionType);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['requestCount'] : 0;
    }
} 
?>