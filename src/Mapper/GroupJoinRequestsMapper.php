<?php
namespace App\Mapper;

use App\Entity\GroupJoinRequests;
use App\Repository\GroupJoinRequestsRepository;
use PDO;

class GroupJoinRequestsMapper implements GroupJoinRequestsRepository
{

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getRequestsByStudent(int $studentId): array
    {
        $stmt = $this->dbConnection->prepare("SELECT requestId, groupId, requesterId, joinStatus, requestedAt FROM groupjoinrequests WHERE requesterId = :studentId");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
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

    public function getRequestsByGroup(int $groupId): array
    {
        $stmt = $this->dbConnection->prepare("SELECT requestId, groupId, requesterId, joinStatus, requestedAt FROM groupJoinRequests WHERE joinStatus = 'pending' AND groupId = :groupId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
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

    public function addRequest(GroupJoinRequests $request): void
    {
        $stmt = $this->dbConnection->prepare("
            INSERT INTO groupJoinRequests (groupId, requesterId, joinStatus, requestedAt)
            VALUES (:groupId, :requesterId, :joinStatus, :requestedAt)
        ");

        $stmt->execute([
            ':groupId' => $request->getGroupId(),
            ':requesterId' => $request->getRequesterId(),
            ':joinStatus' => $request->getJoinStatus(),
            ':requestedAt' => $request->getRequestAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function requestExists(int $groupId, int $studentId): bool
    {
        $stmt = $this->dbConnection->prepare("
            SELECT COUNT(*) FROM groupJoinRequests
            WHERE groupId = :groupId AND requesterId = :studentId
        ");
        $stmt->execute([
            ':groupId' => $groupId,
            ':studentId' => $studentId
        ]);

        return $stmt->fetchColumn() > 0;
    }


}
?>