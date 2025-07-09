<?php
namespace App\Control;

use App\Entity\Action;
use App\Entity\UserAction;
use App\Repository\UserActionRepository;
use App\Repository\ActionRepository;

class ActionControl
{
    private UserActionRepository $userActionRepo;
    private ActionRepository $actionRepo;

    public function __construct(
        UserActionRepository $userActionRepo,
        ActionRepository $actionRepo
    ) {
        $this->userActionRepo = $userActionRepo;
        $this->actionRepo = $actionRepo;
    }

    public function getAction(int $actionId): ?Action
    {
        return $this->actionRepo->getActionById($actionId);
    }

    public function getActionIdByType(string $actionType): ?int
    {
        return $this->actionRepo->getActionIdByType($actionType);
    }

    public function recordUserAction(int $studentId, int $actionId): void
    {
        $userAction = new UserAction(
            $studentId,
            $actionId
        );

        $this->userActionRepo->addAction($userAction);
    }

    public function getActionCountByStudentId(int $studentId, int $actionId): int
    {
        $windowSeconds = $this->getAction($actionId)->getWindowSeconds();
        return $this->userActionRepo->getActionCountByStudentId($studentId, $actionId, $windowSeconds);
    }

}
?>