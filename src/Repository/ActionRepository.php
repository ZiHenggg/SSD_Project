<?php
namespace App\Repository;
use App\Entity\Action; 

interface ActionRepository {
   public function getActionById(int $actionId): ?Action;
   public function getActionIdByType(string $actionType): ?int;
   public function pruneOldActions(): void;
   public function resetIncorrectOtpCount(mixed $identifier, string $userIdentifierType): void;
}
?>