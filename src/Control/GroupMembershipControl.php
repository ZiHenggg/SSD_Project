<?php
namespace App\Control;

use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\GroupJoinRequestsRepository;

class GroupMembershipControl
{
    private GroupMembershipRepository $groupMembershipRepo;
    private GroupRepository $groupRepo;
    private GroupJoinRequestsRepository $groupJoinRequestsRepo;

    public function __construct(
        GroupMembershipRepository $groupMembershipRepo,
        GroupRepository $groupRepo,
        GroupJoinRequestsRepository $groupJoinRequestsRepo
    ) {
        $this->groupMembershipRepo = $groupMembershipRepo;
        $this->groupRepo = $groupRepo;
        $this->groupJoinRequestsRepo = $groupJoinRequestsRepo;
    }

    public function addMember(int $groupId, string $studentId, string $role = 'member'): void
    {
        // Check if the group exists
        $group = $this->groupRepo->getGroup($groupId);
        if (!$group) {
            throw new \Exception("Group with ID $groupId does not exist.");
        }

        if ($group->getGroupStatus() !== 'active') {
            throw new \Exception("Group with ID $groupId is not active.");
        }

        if ($group->isFull()) {
            throw new \Exception("Group with ID $groupId is already full. Cannot add more members.");
        }

        // Check if the student is already a member of the group
        if ($this->groupMembershipRepo->isMember($groupId, $studentId)) {
            throw new \Exception("Student is already a member of this group.");
        }

        $this->groupMembershipRepo->addMember($groupId, $studentId, $role);

        // Increment the member count for the group
        $group->addMember();

        // Update the group in the repository
        $this->groupRepo->updateGroup($group);
    }

    public function getUserRole(int $groupId, int $studentId): ?string
    {
        return $this->groupMembershipRepo->getRoleForUser($groupId, $studentId);
    }

    public function getGroupId(int $groupMembersId): ?string
    {
        return $this->groupMembershipRepo->getGroupId($groupMembersId);
    }

    public function getGroupMembers(int $groupId): array
    {
        return $this->groupMembershipRepo->getMembers($groupId);
        
    }
    public function getRequestsByGroup(int $groupId): array
    {
        return $this->groupJoinRequestsRepo->getRequestsByGroup($groupId);
    }
}
?>