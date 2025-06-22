<?php
namespace App\Repository;
use App\Entity\GroupJoinRequests;

interface GroupJoinRequestsRepository
{
    public function addRequest(GroupJoinRequests $request): void;
    public function removeRequest(int $groupId, int $requesterId): void;
    public function getRequestsByGroup(int $groupId): array;
    public function getRequestsByStudent(int $studentId): array;
    // public function getRequestById(int $requestId): ?GroupJoinRequests;
    public function getGroupIdByRequestId(int $requestId): ?int;
    public function requestExists(int $groupId, int $studentId): bool;
    public function updateRequestStatus(int $requestId, int $reviewerId, string $status): void;
}
?>