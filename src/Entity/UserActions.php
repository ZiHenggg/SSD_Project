<?php

namespace App\Entity;

class UserActions { 
    private int $actionId;
    private int $studentId;
    private string $actionType; // 'join_request', 'create_group'
    private \DateTime $actionTimestamp;

    public function __construct(int $studentId, string $actionType) {
        $this->studentId = $studentId;
        $this->actionType = $actionType;
        $this->actionTimestamp = new \DateTime();
    }

    public function getActionId(): int {
        return $this->actionId;
    }

    public function getStudentId(): int {
        return $this->studentId;
    }

    public function getActionType(): string {
        return $this->actionType;
    }

    public function getActionTimestamp(): \DateTime {
        return $this->actionTimestamp;
    }

    public function setActionId(int $actionId): void {
        $this->actionId = $actionId;
    }

    public function setStudentId(int $studentId): void {
        $this->studentId = $studentId;
    }

    public function setActionType(string $actionType): void {
        $this->actionType = $actionType;
    }

    public function setActionTimestamp(\DateTime $actionTimestamp): void {
        $this->actionTimestamp = $actionTimestamp;
    }
}
?>
