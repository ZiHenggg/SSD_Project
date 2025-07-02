<?php
namespace App\Control;

use App\Entity\Group;
use App\Entity\LabGroup;
use App\Entity\Module;
use App\Repository\GroupRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\ModuleRepository;
use App\Repository\LabGroupRepository;

class GroupControl
{
    private GroupRepository $groupRepo;
    private GroupMembershipRepository $groupMembershipRepo;
    private ModuleRepository $moduleRepo;
    private LabGroupRepository $labGroupRepo;

    public function __construct(
        GroupRepository $groupRepo,
        GroupMembershipRepository $groupMembershipRepo,
        ModuleRepository $moduleRepo,
        LabGroupRepository $labGroupRepo

    ) {
        $this->groupRepo = $groupRepo;
        $this->groupMembershipRepo = $groupMembershipRepo;
        $this->moduleRepo = $moduleRepo;
        $this->labGroupRepo = $labGroupRepo;
    }

    public function createGroup(string $acadYear, string $trimester, string $moduleCode, int $maxMembers, string $studentId, string $labGroup = ''): Group
    {
        // Get the last group number for that config
        $lastGroupNumber = $this->groupRepo->getLastGroupNumberByParams($acadYear, $trimester, $moduleCode, $labGroup);

        $nextGroupNumber = $lastGroupNumber + 1; // Increment the last group number to get the next group number

        $group = new Group(
            $acadYear,
            $trimester,
            $moduleCode,
            $labGroup,
            $nextGroupNumber,
            $maxMembers
        );
        $group->generateGroupName();

        $this->groupRepo->addGroup($group);

        $this->groupMembershipRepo->addMember($group->getGroupId(), $studentId, "admin");

        $group->addMember(); // Increment the member count for the group
        $this->groupRepo->updateGroup($group); // Update the group in the repository
        return $group;
    }

    public function searchGroup(string $moduleName): array 
    {
        return $this->groupRepo->getGroupsByModuleName($moduleName);
    }


    public function isMember(int $groupId, int $studentId): bool
    {
        return $this->groupMembershipRepo->isMember($groupId, $studentId);
    }

    public function getGroupsByUser(int $studentId): array
    {
        return $this->groupRepo->getGroupsByUser($studentId);
    }

    public function getActiveGroupsByUser(int $studentId): array
    {
        return $this->groupRepo->getActiveGroupsByUser($studentId);
    }

    public function getAllActiveGroups(): array
    {
        return $this->groupRepo->getAllActiveGroups();
    }

    public function getGroupInfo(int $groupId): ?Group
    {
        return $this->groupRepo->getGroup($groupId);
    }

    public function getModuleByGroupId(int $groupId): ?Module
    {
        $group = $this->groupRepo->getGroup($groupId);
        if (!$group) {
            return null;
        }

        return $this->moduleRepo->getModule($group->getModuleCode());
    }

    public function softDeleteGroup(int $groupId): void
    {
        $group = $this->getGroupInfo($groupId);
        if ($group) {
            // $group->setIsActive(false); // Set the group as inactive
            // $this->groupRepo->updateGroup($group); // Update the group in the repository
            $this->groupRepo->updateGroupStatus($groupId, 'inactive');
        }
    }

    public function getAllModules(): array
    {
        return $this->moduleRepo->getAllModules();
    }

    public function getLabGroupsByModuleCode(string $moduleCode): array
    {
        return $this->labGroupRepo->getLabGroupsByModuleCode($moduleCode);
    }
}
?>