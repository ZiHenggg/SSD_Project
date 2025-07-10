<?php

namespace App\Entity;

class IpAction { 
    private int $ipActionId;
    private string $ipAddress;
    private int $actionId;
    private \DateTime $actionTimestamp;

    public function __construct(string $ipAddress, int $actionId) {
        $this->ipAddress = $ipAddress;
        $this->actionId = $actionId;
        $this->actionTimestamp = new \DateTime();
    }

    public function getIpActionId(): int {
        return $this->ipActionId;
    }

    public function getIpAddress(): string {
        return $this->ipAddress;
    }

    public function getActionId(): int {
        return $this->actionId;
    }

    public function getActionTimestamp(): \DateTime {
        return $this->actionTimestamp;
    }
}
?>