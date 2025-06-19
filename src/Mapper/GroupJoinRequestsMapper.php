<?php
namespace App\Mapper;

use DateTime;
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

    private function mapRowToGroupJoinRequest(array $data): GroupJoinRequests
    {
        return new GroupJoinRequests(
            $data['requestId'],
            $data['groupId'],
            $data['requesterId'],
            $data['joinStatus'],
            new \DateTime($data['requestedAt']),
            isset($data['reviewedAt']) ? new \DateTime($data['reviewedAt']) : null,
            $data['reviewedBy'] !== null ? (int) $data['reviewedBy'] : null
        );
    }

    public function getRequestsByStudent(int $studentId): array
    {
        // $stmt = $this->dbConnection->prepare("SELECT requestId, groupId, requesterId, joinStatus, requestedAt FROM groupJoinRequests WHERE requesterId = :studentId");
        $stmt = $this->dbConnection->prepare("SELECT * FROM groupJoinRequests WHERE requesterId = :studentId");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $requestsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requests = [];
        foreach ($requestsData as $data) {
            // $requests[] = new GroupJoinRequests(
            //     $data['requestId'],
            //     $data['groupId'],
            //     $data['requesterId'],
            //     $data['joinStatus'],
            //     new \DateTime($data['requestedAt']),
            //     isset($data['reviewedAt']) ? new \DateTime($data['reviewedAt']) : null,
            //     $data['reviewedBy'] !== null ? (int) $data['reviewedBy'] : null
            //     // new \DateTime($data['reviewedAt'])
            // );
            $requests[] = $this->mapRowToGroupJoinRequest($data);
        }

        return $requests;
    }

    public function getRequestsByGroup(int $groupId): array
    {
        // $stmt = $this->dbConnection->prepare("SELECT requestId, groupId, requesterId, joinStatus, requestedAt FROM groupJoinRequests WHERE joinStatus = 'pending' AND groupId = :groupId");
        $stmt = $this->dbConnection->prepare("SELECT * FROM groupJoinRequests WHERE joinStatus = 'pending' AND groupId = :groupId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        $requestsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requests = [];
        foreach ($requestsData as $data) {
            // $requests[] = new GroupJoinRequests(
            //     $data['requestId'],
            //     $data['groupId'],
            //     $data['requesterId'],
            //     $data['joinStatus'],
            //     new \DateTime($data['requestedAt']),
            //     isset($data['reviewedAt']) ? new \DateTime($data['reviewedAt']) : null,
            //     $data['reviewedBy'] !== null ? (int) $data['reviewedBy'] : null
            //     // new \DateTime($data['reviewedAt'])
            // );
            $requests[] = $this->mapRowToGroupJoinRequest($data);
        }

        return $requests;
    }

    public function getGroupIdByRequestId(int $requestId): ?int
    {
        $stmt = $this->dbConnection->prepare("SELECT groupId FROM groupJoinRequests WHERE requestId = :requestId");
        $stmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['groupId'] : null;
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

    public function updateRequestStatus(int $requestId, int $reviewerId, string $status): void
    {
        $reviewedAt = new DateTime(); // current time

        $stmt = $this->dbConnection->prepare("
        UPDATE groupJoinRequests
        SET joinStatus = :status,
            reviewedAt = :reviewedAt,
            reviewedBy = :reviewedBy
        WHERE requestId = :requestId
    ");

        $stmt->execute([
            ':status' => $status,
            ':reviewedAt' => $reviewedAt->format('Y-m-d H:i:s'),
            ':reviewedBy' => $reviewerId,
            ':requestId' => $requestId
        ]);
    }

}
?>