<?php
namespace App\Control;

use App\Entity\Action;
use App\Entity\UserAction;
use App\Entity\IpAction;
use App\Repository\UserActionRepository;
use App\Repository\IpActionRepository;
use App\Repository\ActionRepository;

class ActionControl
{
    private ActionRepository $actionRepo;
    private UserActionRepository $userActionRepo;
    private IpActionRepository $ipActionRepo;

    public function __construct(
        ActionRepository $actionRepo,
        UserActionRepository $userActionRepo,
        IpActionRepository $ipActionRepo
    ) {
        $this->actionRepo = $actionRepo;
        $this->userActionRepo = $userActionRepo;
        $this->ipActionRepo = $ipActionRepo;
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

    public function recordIpAction(string $ipAddress, int $actionId): void
    {
        $ipAction = new IpAction(
            $ipAddress,
            $actionId
        );

        $this->ipActionRepo->addAction($ipAction);
    }

    public function getActionCountByIpAddress(string $ipAddress, int $actionId): int
    {
        $windowSeconds = $this->getAction($actionId)->getWindowSeconds();
        return $this->ipActionRepo->getActionCountByIpAddress($ipAddress, $actionId, $windowSeconds);
    }

    public function pruneOldActions(): void
    {
        $this->actionRepo->pruneOldActions();
    }

    public function resetIncorrectOtpCount(mixed $identifier, string $userIdentifierType): void
    {
        $this->actionRepo->resetIncorrectOtpCount($identifier, $userIdentifierType);
    }
}
?>