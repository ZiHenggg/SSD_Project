<?php
namespace App\Boundary;

use App\Entity\GroupMembership;
use App\Control\GroupMembershipControl;
use PDO;
use Exception;

class GroupMembershipController
{
    private GroupMembershipControl $groupMembershipControl;
    private PDO $pdo;

    public function __construct(GroupMembershipControl $groupMembershipControl, PDO $pdo)
    {
        $this->groupMembershipControl = $groupMembershipControl;
        $this->pdo = $pdo;
    }

    public function displayGroupId(int $groupMembersId): string
    {
        try {
            return $this->groupMembershipControl->getGroupId($groupMembersId);
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching the group name: ' . $e->getMessage());
        }
    }

    public function showJoinRequest(int $groupId): array
    {
        try {
            $joinRequests = $this->groupMembershipControl->getRequestsByGroup($groupId);
            if (empty($joinRequests)) {
                return ['message' => 'No join requests found for this group.'];
            }
            return ['joinRequest' => $joinRequests];
        } catch (Exception $e) {
            return ['error' => 'An error occurred while fetching join requests: ' . $e->getMessage()];
        }
    }
    
    public function onJoinGroupRequest(int $groupId, int $studentId): void
    {
        if ($this->groupMembershipControl->requestExists($groupId, $studentId)) {
            throw new Exception("You have already requested to join this group.");
        }

        $this->groupMembershipControl->submitJoinRequest($groupId, $studentId);
    }

    public function onCheckIfRequested(int $groupId, int $studentId): bool
    {
        return $this->groupMembershipControl->requestExists($groupId, $studentId);
    }

}
?>