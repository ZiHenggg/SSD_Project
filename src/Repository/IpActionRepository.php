<?php
namespace App\Repository;
use App\Entity\IpAction; 

interface IpActionRepository {
    public function addAction(IpAction $ipAction): void;
    public function getActionCountByIpAddress(string $ipAddress, int $actionId, int $windowSeconds): int;
}
?>