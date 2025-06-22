<?php
namespace App\Entity;
use DateTime;

class GroupJoinRequests
{
    private int $requestId;
    private int $groupId;
    private int $requesterId;
    private string $joinStatus; // e.g., 'pending', 'accepted', 'rejected'
    private DateTime $requestedAt;
    private ?DateTime $reviewedAt;
    private ?int $reviewedBy;

    // Getters
    public function getRequestId(): int
    {
        return $this->requestId;
    }
    public function getGroupId(): int
    {
        return $this->groupId;
    }
    public function getRequesterId(): int
    {
        return $this->requesterId;
    }
    public function getJoinStatus(): string
    {
        return $this->joinStatus;
    }
    public function getRequestedAt(): DateTime
    {
        return $this->requestedAt;
    }
    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewedAt;
    }
    public function getReviewedBy(): ?int
    {
        return $this->reviewedBy;
    }

    // Setters
    private function setRequestId(int $requestId): void
    {
        $this->requestId = $requestId;
    }
    private function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }
    private function setRequesterId(int $requesterId): void
    {
        $this->requesterId = $requesterId;
    }
    private function setJoinStatus(string $joinStatus): void
    {
        $this->joinStatus = $joinStatus;
    }
    private function setRequestedAt(DateTime $requestedAt): void
    {
        $this->requestedAt = $requestedAt;
    }
    private function setReviewedAt(?DateTime $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }
    private function setReviewedBy(?int $reviewedBy): void
    {
        $this->reviewedBy = $reviewedBy;
    }

    // Constructor
    public function __construct(
        int $requestId,
        int $groupId,
        int $requesterId,
        string $joinStatus,
        DateTime $requestedAt,
        ?DateTime $reviewedAt = null,
        ?int $reviewedBy = null
    ) {
        $this->setRequestId($requestId);
        $this->setGroupId($groupId);
        $this->setRequesterId($requesterId);
        $this->setJoinStatus($joinStatus);
        $this->setRequestedAt($requestedAt);
        $this->reviewedAt = $reviewedAt;
        $this->reviewedBy = $reviewedBy;
    }
}
?>