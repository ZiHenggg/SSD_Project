<?php

namespace App\Entity;

class Action { 
    private int $actionId;
    private string $actionType;
    private int $maxActionCount;
    private int $windowSeconds;

    public function __construct(int $actionId, string $actionType, int $maxActionCount, int $windowSeconds) {
        $this->actionId = $actionId;
        $this->actionType = $actionType;
        $this->maxActionCount = $maxActionCount;
        $this->windowSeconds = $windowSeconds;
    }

    public function getActionId(): int {
        return $this->actionId;
    }

    public function getActionType(): string {
        return $this->actionType;
    }

    public function getMaxActionCount(): int {
        return $this->maxActionCount;
    }

    public function getWindowSeconds(): int {
        return $this->windowSeconds;
    }
}
?>