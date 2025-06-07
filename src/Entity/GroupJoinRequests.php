<?php
namespace Ngmin\Ict2216G5\Entity;
use DateTime;

class GroupJoinRequests {
    private int $requestId;
    private int $groupId;
    private int $requesterId;
    private string $joinStatus; // e.g., 'pending', 'accepted', 'rejected'
    private DateTime $requestAt;
    private DateTime $reviewedAt;

    // Getters
    public function getRequestId(): int {
        return $this->requestId;
    }
    public function getGroupId(): int {
        return $this->groupId;
    }
    public function getRequesterId(): int {
        return $this->requesterId;
    }
    public function getJoinStatus(): string {
        return $this->joinStatus;
    }
    public function getRequestAt(): DateTime {
        return $this->requestAt;
    }
    public function getReviewedAt(): DateTime {
        return $this->reviewedAt;
    }

    // Setters
    private function setRequestId(int $requestId): void {
        $this->requestId = $requestId;
    }
    private function setGroupId(int $groupId): void {
        $this->groupId = $groupId;
    }
    private function setrequesterId(int $requesterId): void {
        $this->requesterId = $requesterId;
    }
    private function setJoinStatus(string $joinStatus): void {
        $this->joinStatus = $joinStatus;
    }
    private function setRequestAt(DateTime $requestAt): void {
        $this->requestAt = $requestAt;
    }
    private function setReviewedAt(DateTime $reviewedAt): void {
        $this->reviewedAt = $reviewedAt;
    }

    // Constructor
    public function __construct(
        int $requestId, 
        int $groupId, 
        int $requesterId, 
        string $joinStatus, 
        DateTime $requestAt, 
        // DateTime $reviewedAt
    ) {
        $this->setRequestId($requestId);
        $this->setGroupId($groupId);
        $this->setRequesterId($requesterId);
        $this->setJoinStatus($joinStatus);
        $this->setRequestAt($requestAt); 
        // $this->setReviewedAt($reviewedAt); 
    }
}
?>