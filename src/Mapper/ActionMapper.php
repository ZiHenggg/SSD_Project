<?php
namespace App\Mapper;

use App\Entity\Action;
use App\Repository\ActionRepository;
use PDO;

class ActionMapper implements ActionRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getActionById(int $actionId): ?Action {
        $stmt = $this->dbConnection->prepare("SELECT * FROM actions WHERE actionId = :actionId");
        $stmt->bindParam(':actionId', $actionId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Action(
                (int)$result['actionId'],
                $result['actionType'],
                (int)$result['maxActionCount'],
                (int)$result['windowSeconds']
            );
        }
        return null;
    }

    public function getActionIdByType(string $actionType): ?int {
        $stmt = $this->dbConnection->prepare("SELECT actionId FROM actions WHERE actionType = :actionType");
        $stmt->bindParam(':actionType', $actionType);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['actionId'];
        }
        return null;
    }
} 
?>