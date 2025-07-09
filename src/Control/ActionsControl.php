<?php
namespace App\Control;

use App\Entity\UserActions;
use App\Repository\UserActionsRepository;

class ActionsControl
{
    private UserActionsRepository $userActionsRepo;

    public function __construct(
        UserActionsRepository $userActionsRepo
    ) {
        $this->userActionsRepo = $userActionsRepo;
    }

    public function recordUserAction(int $studentId, string $actionType): void
    {
        $userAction = new UserActions(
            $studentId,
            $actionType
        );

        $this->userActionsRepo->addAction($userAction);
    }

    public function getActionCountByStudentId(int $studentId, string $actionType): int
    {
        return $this->userActionsRepo->getActionCountByStudentId($studentId, $actionType);
    }

}
?>