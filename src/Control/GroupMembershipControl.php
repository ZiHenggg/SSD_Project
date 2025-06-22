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

    public function getGroupId(int $groupMembersId): ?int
    {
        return $this->groupMembershipRepo->getGroupId($groupMembersId);
    }

    public function getGroupIdByRequestId(int $requestId): ?int
    {
        return $this->groupJoinRequestsRepo->getGroupIdByRequestId($requestId);
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

    public function removeJoinRequest(int $groupId, int $studentId): void 
    {
        $this->groupJoinRequestsRepo->removeRequest($groupId, $studentId);
    }

    public function memberExists(int $groupId, int $studentId): bool
    {
        return $this->groupMembershipRepo->isMember($groupId, $studentId);
    }

    public function acceptJoinRequest(int $requestId, int $requesterId, int $approverId): void
    {
        // Get the join request
        $requests = $this->groupJoinRequestsRepo->getRequestsByStudent($requesterId);
        $request = null;
        // TODO: Use a more efficient way to find the request
        foreach ($requests as $req) {
            if ($req->getRequestId() === $requestId) {
                $request = $req;
                break;
            }
        }
        if (!$request) {
            throw new \Exception("Join request with ID $requestId does not exist.");
        }

        if ($request->getRequesterId() !== $requesterId) {
            throw new \Exception("Mismatch: Join request does not belong to the provided student.");
        }

        if ($request->getJoinStatus() !== 'pending') {
            throw new \Exception("Join request with ID $requestId is not pending.");
        }

        // Update the join request status to accepted
        $this->groupJoinRequestsRepo->updateRequestStatus(
            $requestId,
            $approverId,
            'accepted'
        );

        // Add the member to the group
        $groupId = $request->getGroupId();
        $this->addMember($groupId, $requesterId);
    }

    public function rejectJoinRequest(int $requestId, int $requesterId, int $approverId): void
    {
        // Get the join request
        $requests = $this->groupJoinRequestsRepo->getRequestsByStudent($requesterId);
        $request = null;
        // TODO: Use a more efficient way to find the request
        foreach ($requests as $req) {
            if ($req->getRequestId() === $requestId) {
                $request = $req;
                break;
            }
        }
        if (!$request) {
            throw new \Exception("Join request with ID $requestId does not exist.");
        }

        if ($request->getRequesterId() !== $requesterId) {
            throw new \Exception("Mismatch: Join request does not belong to the provided student.");
        }

        if ($request->getJoinStatus() !== 'pending') {
            throw new \Exception("Join request with ID $requestId is not pending.");
        }

        // Update the join request status to accepted
        $this->groupJoinRequestsRepo->updateRequestStatus(
            $requestId,
            $approverId,
            'rejected'
        );
    }
}
?>