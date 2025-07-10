<?php
namespace App\Mapper;

use App\Entity\UserAction;
use App\Repository\UserActionRepository;
use PDO;

class UserActionMapper implements UserActionRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function addAction(UserAction $userAction): void {
        $stmt = $this->dbConnection->prepare("INSERT INTO userActions (studentId, actionId) VALUES (:studentId, :actionId)");

        $studentId = $userAction->getStudentId();
        $actionId = $userAction->getActionId();

        // Bind the variables
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':actionId', $actionId);
        $stmt->execute();
    }

    public function getActionCountByStudentId(int $studentId, int $actionId, int $windowSeconds): int {
        $stmt = $this->dbConnection->prepare("
        SELECT COUNT(*) AS requestCount
            FROM userActions
            WHERE studentId = :studentId
            AND actionId = :actionId
            AND actionTimestamp > NOW() - INTERVAL :windowSeconds SECOND;
        ");

        $stmt->bindParam(':windowSeconds', $windowSeconds);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':actionId', $actionId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['requestCount'] : 0;
    }
} 
?>