<?php
namespace App\Repository;
use App\Entity\GroupJoinRequests; 

interface GroupJoinRequestsRepository
{
    // public function addRequest(GroupJoinRequests $request): void;
    // public function removeRequest(int $groupId, int $requesterId): void;
    public function getRequestsByGroup(int $groupId): array;
    public function getRequestsByStudent(int $requesterId): array;
    // public function getRequestById(int $requestId): ?GroupJoinRequests;
    // public function updateRequestStatus(int $requestId, string $status, DateTime $reviewedAt): void;
    // public function requestExists(int $requesterId, int $groupId): bool;
}
?>