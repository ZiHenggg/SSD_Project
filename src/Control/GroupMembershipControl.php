<?php
namespace App\Control;

use App\Entity\GroupJoinRequests;
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

    public function addMember(int $groupId, int $studentId, string $role = 'member'): void
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
    public function getRequestsByStudent(int $studentId): array
    {
        return $this->groupJoinRequestsRepo->getRequestsByStudent($studentId);
    }

    public function requestExists(int $groupId, int $studentId): bool
    {
        return $this->groupJoinRequestsRepo->requestExists($groupId, $studentId);
    }

    public function submitJoinRequest(int $groupId, int $studentId): void
    {
        if ($this->groupJoinRequestsRepo->requestExists($groupId, $studentId)) {
            throw new \Exception("A join request already exists for this group.");
        }

        $request = new GroupJoinRequests(
            0, // Auto-generated ID
            $groupId,
            $studentId,
            'pending',
            new \DateTime()
        );

        $this->groupJoinRequestsRepo->addRequest($request);
    }

    public function memberExists(int $groupId, int $studentId): bool
    {
        return $this->groupMembershipRepo->isMember($groupId, $studentId);
    }

}
?>