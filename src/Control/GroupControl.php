<?php
namespace App\Control;

use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\GroupMembershipRepository;

class GroupControl
{
    private GroupRepository $groupRepo;
    private GroupMembershipRepository $groupMembershipRepo;

    public function __construct(
        GroupRepository $groupRepo,
        GroupMembershipRepository $groupMembershipRepo
    ) {
        $this->groupRepo = $groupRepo;
        $this->groupMembershipRepo = $groupMembershipRepo;
    }

    public function createGroup(string $acadYear, string $trimester, string $moduleCode, int $maxMembers, string $adminId, string $labGroup = ''): Group
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

        $this->groupMembershipRepo->addMember($group->getGroupId(), $adminId, "admin");

        $group->addMember(); // Increment the member count for the group
        $this->groupRepo->updateGroup($group); // Update the group in the repository
        return $group;
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

    public function softDeleteGroup(int $groupId): void
    {
        $group = $this->getGroupInfo($groupId);
        if ($group) {
            // $group->setIsActive(false); // Set the group as inactive
            // $this->groupRepo->updateGroup($group); // Update the group in the repository
            $this->groupRepo->updateGroupStatus($groupId, 'inactive');
        }
    }
}
?>