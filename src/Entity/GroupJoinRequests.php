<?php
namespace Ngmin\Ict2216G5\Entity;
use DateTime;

class GroupJoinRequests {
    private int $requestId;
    private int $groupId;
    private int $studentId;
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
    public function getStudentId(): int {
        return $this->studentId;
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
    private function setStudentId(int $studentId): void {
        $this->studentId = $studentId;
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
}
?>