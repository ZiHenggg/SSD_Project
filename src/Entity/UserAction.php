<?php

namespace App\Entity;

class UserAction { 
    private int $userActionId;
    private int $studentId;
    private int $actionId;
    private \DateTime $actionTimestamp;

    public function __construct(int $studentId, int $actionId) {
        $this->studentId = $studentId;
        $this->actionId = $actionId;
        $this->actionTimestamp = new \DateTime();
    }

    public function getUserActionId(): int {
        return $this->userActionId;
    }

    public function getStudentId(): int {
        return $this->studentId;
    }

    public function getActionId(): int {
        return $this->actionId;
    }

    public function getActionTimestamp(): \DateTime {
        return $this->actionTimestamp;
    }
}
?>