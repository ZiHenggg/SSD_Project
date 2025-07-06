<?php
namespace App\Boundary;

use App\Entity\Module;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use Exception;

class GroupPageController
{
    private GroupControl $groupControl;
    private GroupMembershipControl $groupMembershipControl;

    public function __construct(GroupControl $groupControl, GroupMembershipControl $groupMembershipControl)
    {
        $this->groupControl = $groupControl;
        $this->groupMembershipControl = $groupMembershipControl;
    }

    public function onCreateGroup(array $formData, string $studentId): void
    {
        $labGroupsRows = $this->groupControl->getAllLabGroups();

        // Group lab groups by moduleCode
        $labGroups = [];
        foreach ($labGroupsRows as $row) {
            $labGroups[$row->getModuleCode()][] = $row->getLabGroupCode();
        }

        // Get form data
        $acadYear = $formData['acadYear'] ?? null;
        $trimester = $formData['trimester'] ?? null;
        $moduleCode = $formData['moduleCode'] ?? null;
        $labGroup = $formData['labGroup'] ?? '';
        $maxGroupSize = (int) ($formData['maxGroupSize'] ?? 0);

        // Validate required fields
        if (!$studentId) {
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
            $studentId,
            $labGroup
        );
    }

    public function onSearchGroupsByModuleName(string $moduleName): array
    {
        return $this->groupControl->searchGroup($moduleName);
    }


    public function listAllActiveGroups(): array
    {
        return $this->groupControl->getAllActiveGroups();
    }

    public function listActiveUserGroups(int $studentId): array
    {
        return $this->groupControl->getActiveGroupsByUser($studentId);
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

        $module = $this->groupControl->getModuleByGroupId($groupId);

        $members = $this->groupMembershipControl->getGroupMembers($groupId);
        return [
            'group' => $group,
            'members' => $members,
            'module' => $module,
        ];
    }

    public function onDeleteGroup(int $groupId): void
    {
        try {
            $this->groupControl->softDeleteGroup($groupId);

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: dashboard.php");
            exit;
        }
    }

    public function onGetModuleByGroupId(int $groupId): ?Module
    {
        return $this->groupControl->getModuleByGroupId($groupId);
    }

    public function getAllModules(): array
    {
        return $this->groupControl->getAllModules();
    }

    public function getLabGroupsByModuleCode(string $moduleCode): array
    {
        return $this->groupControl->getLabGroupsByModuleCode($moduleCode);
    }
}
?>