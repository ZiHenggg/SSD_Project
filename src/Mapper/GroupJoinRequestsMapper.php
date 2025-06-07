<?php
namespace Ngmin\Ict2216G5\Mapper;

use Ngmin\Ict2216G5\Entity\GroupJoinRequests;
use Ngmin\Ict2216G5\Repository\GroupJoinRequestsRepository;
use PDO;

class groupJoinRequestsMapper implements GroupJoinRequestsRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getRequestsByStudent(int $requesterId): array {
        $stmt = $this->dbConnection->prepare("SELECT requestId, groupId, requesterId, joinStatus, requestedAt FROM groupjoinrequests WHERE requesterId = :requesterId");
        $stmt->bindParam(':requesterId', $requesterId, PDO::PARAM_INT);
        $stmt->execute();

        $requestsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requests = [];
        foreach ($requestsData as $data) {
            $requests[] = new GroupJoinRequests(
                $data['requestId'],
                $data['groupId'],
                $data['requesterId'],
                $data['joinStatus'],
                new \DateTime($data['requestedAt']),
                // new \DateTime($data['reviewedAt'])
            );
        }

        return $requests;
    }
    
} 
?>
