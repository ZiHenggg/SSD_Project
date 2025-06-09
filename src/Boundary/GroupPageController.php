<?php
namespace App\Boundary;

use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use PDO;
use Exception;

class GroupPageController
{
    private GroupControl $groupControl;
    private GroupMembershipControl $groupMembershipControl;
    private PDO $pdo;

    public function __construct(GroupControl $groupControl, GroupMembershipControl $groupMembershipControl, PDO $pdo)
    {
        $this->groupControl = $groupControl;
        $this->groupMembershipControl = $groupMembershipControl;
        $this->pdo = $pdo;
    }

    public function createGroup(array $formData, string $adminId): void
    {
        // Fetch lab groups from the database
        $stmt = $this->pdo->query("SELECT labGroupCode, moduleCode FROM labGroups");
        $labGroupRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group lab groups by moduleCode
        $labGroups = [];
        foreach ($labGroupRows as $row) {
            $labGroups[$row['moduleCode']][] = $row['labGroupCode'];
        }

        // Get form data
        $acadYear = $formData['acadYear'] ?? null;
        $trimester = $formData['trimester'] ?? null;
        $moduleCode = $formData['moduleCode'] ?? null;
        $labGroup = $formData['labGroup'] ?? '';
        $maxGroupSize = (int) ($formData['maxGroupSize'] ?? 0);

        // Validate required fields
        if (!$adminId) {
            throw new Exception("User not logged in.");
        }

        // Validate required fields
        if (!$acadYear || !$trimester || !$moduleCode || $maxGroupSize <= 0) {
            throw new Exception("Missing or invalid form fields.");
        }

        if (array_key_exists($moduleCode, $labGroups)) {
            if (count($labGroups[$moduleCode]) > 0 && !$labGroup) {
                throw new Exception("Lab group is required for this module.");
            }

            if (!empty($labGroup) && !in_array($labGroup, $labGroups[$moduleCode])) {
                throw new Exception("Invalid lab group for the selected module.");
            }
        }

        $group = $this->groupControl->createGroup(
            $acadYear,
            $trimester,
            $moduleCode,
            $maxGroupSize,
            $adminId,
            $labGroup
        );
    }

    public function listAllActiveGroups(): array
    {
        // return $this->groupControl->getGroupsByUser($studentId);
        return $this->groupControl->getAllActiveGroups();
    }

    public function listUserGroups(int $studentId): array
    {
        return $this->groupControl->getGroupsByUser($studentId);
    }

    public function getUserRoleInGroup(int $groupId, int $studentId): ?string
    {
        return $this->groupMembershipControl->getUserRole($groupId, $studentId);
    }

    public function getUserRolesForGroups(int $studentId, array $groups): array
    {
        $roles = [];
        foreach ($groups as $group) {
            $roles[$group->getGroupId()] = $this->groupMembershipControl->getUserRole($group->getGroupId(), $studentId);
        }
        return $roles;
    }

    public function displayGroupDetails(int $groupId): ?array
    {
        $group = $this->groupControl->getGroupInfo($groupId);
        if (!$group) {
            return null; // Group not found
        }

        $members = $this->groupMembershipControl->getGroupMembers($groupId);
        return [
            'group' => $group,
            'members' => $members,
        ];
    }

    // public function onDeleteGroup(int $groupId, string $adminId): void
    // {
    //     try {
    //         $this->groupControl->deleteGroup($groupId, $adminId);
    //         header("Location: groups.php");
    //         exit;
    //     } catch (Exception $e) {
    //         $_SESSION['error'] = $e->getMessage();
    //         header("Location: group_details.php?id=$groupId");
    //         exit;
    //     }
    // }

    // public function onUpdateGroupStatus(int $groupId, string $status): void
    // {
    //     try {
    //         $this->groupControl->updateGroupStatus($groupId, $status);
    //         header("Location: group_details.php?id=$groupId");
    //         exit;
    //     } catch (Exception $e) {
    //         $_SESSION['error'] = $e->getMessage();
    //         header("Location: group_details.php?id=$groupId");
    //         exit;
    //     }
    // }
}
?>