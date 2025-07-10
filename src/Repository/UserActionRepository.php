<?php
namespace App\Repository;
use App\Entity\UserAction; 

interface UserActionRepository {
    public function addAction(UserAction $userAction): void;
    public function getActionCountByStudentId(int $studentId, int $actionId, int $windowSeconds): int;
}
?>