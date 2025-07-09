<?php
namespace App\Repository;
use App\Entity\UserActions; 

interface UserActionsRepository {
    public function addAction(UserActions $userActions): void;
    public function getActionCountByStudentId(int $studentId, string $actionType): int;
}
?>