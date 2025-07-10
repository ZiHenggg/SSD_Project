<?php
namespace App\Mapper;

use App\Entity\IpAction;
use App\Repository\IpActionRepository;
use PDO;

class IpActionMapper implements IpActionRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function addAction(IpAction $ipAction): void {
        $stmt = $this->dbConnection->prepare("INSERT INTO ipActions (ipAddress, actionId) VALUES (:ipAddress, :actionId)");

        $ipAddress = $ipAction->getIpAddress();
        $actionId = $ipAction->getActionId();

        // Bind the variables
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->bindParam(':actionId', $actionId);
        $stmt->execute();
    }

    public function getActionCountByIpAddress(string $ipAddress, int $actionId, int $windowSeconds): int {
        $stmt = $this->dbConnection->prepare("
        SELECT COUNT(*) AS requestCount
            FROM ipActions
            WHERE ipAddress = :ipAddress
            AND actionId = :actionId
            AND actionTimestamp > NOW() - INTERVAL :windowSeconds SECOND;
        ");

        $stmt->bindParam(':windowSeconds', $windowSeconds, PDO::PARAM_INT);
        $stmt->bindParam(':ipAddress', $ipAddress, PDO::PARAM_STR);
        $stmt->bindParam(':actionId', $actionId, PDO::PARAM_INT); 

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['requestCount'] : 0;
    }
} 
?>