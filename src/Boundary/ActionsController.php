<?php
namespace App\Boundary;

use App\Entity\UserActions;
use App\Control\ActionsControl;
use App\Control\StudentControl;
use Exception;

class ActionsController
{
    private ActionsControl $actionsControl;
    private StudentControl $studentControl;

    public function __construct(ActionsControl $actionsControl, StudentControl $studentControl)
    {
        $this->actionsControl = $actionsControl;
        $this->studentControl = $studentControl;
    }

    private const MAX_ACTIONS = 5;
    private const ACTION_TYPES = ['join_request', 'create_group'];

    public function onUserAction(int $studentId, string $actionType): void
    {
        try {
            // Validate inputs
            if (!in_array($actionType, self::ACTION_TYPES)) {
                throw new Exception("Something went wrong.");
            }

            if ($this->studentControl->getStudentById($studentId) === null) {
                throw new Exception("Invalid student ID.");
            }

            // Check rate limiting
            if ($this->actionsControl->getActionCountByStudentId($studentId, $actionType) >= self::MAX_ACTIONS) {
                throw new Exception("Rate limit exceeded. Please try again later.");
            }

            $this->actionsControl->recordUserAction($studentId, $actionType);
        } catch (Exception $e) {
            // Handle exception
            throw new Exception($e->getMessage());
        }
    }
}
?>