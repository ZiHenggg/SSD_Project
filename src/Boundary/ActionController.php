<?php
namespace App\Boundary;

use App\Entity\Action;
use App\Entity\UserAction;
use App\Control\ActionControl;
use App\Control\StudentControl;
use Exception;

class ActionController
{
    private ActionControl $actionControl;
    private StudentControl $studentControl;

    public function __construct(ActionControl $actionControl, StudentControl $studentControl)
    {
        $this->actionControl = $actionControl;
        $this->studentControl = $studentControl;
    }

    public function onUserAction(int $studentId, string $actionType): void
    {
        try {
            $actionId = $this->actionControl->getActionIdByType($actionType);
            
            if ($actionId === null) {
                throw new Exception("Invalid action type.");
            }

            if ($this->studentControl->getStudentById($studentId) === null) {
                throw new Exception("Invalid student ID.");
            }

            // Check rate limiting
            $maxActionCount = $this->actionControl->getAction($actionId)->getMaxActionCount();

            if ($this->actionControl->getActionCountByStudentId($studentId, $actionId) >= $maxActionCount) {
                throw new Exception("Rate limit exceeded. Please try again later.");
            }

            $this->actionControl->recordUserAction($studentId, $actionId);
        } catch (Exception $e) {
            // Handle exception
            throw new Exception($e->getMessage());
        }
    }
}
?>