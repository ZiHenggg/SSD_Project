<?php
namespace Ngmin\Ict2216G5\Entity;

class Group {
    private int $groupId;
    private string $groupName;
    private string $acadYear;
    private string $trimester;
    private string $moduleCode;
    private string $labGroup;
    private int $groupNumber;
    private int $noOfMembers;
    private int $maxMembers;
    private string $groupStatus;

    // Getters
    public function getGroupId(): int {
        return $this->groupId;
    }

    public function getGroupName(): string {
        return $this->groupName;
    }

    public function getAcadYear(): string {
        return $this->acadYear;
    }

    public function getModuleCode(): string {
        return $this->moduleCode;
    }

    public function getLabGroup(): string {
        return $this->labGroup;
    }

    public function getGroupNumber(): int {
        return $this->groupNumber;
    }

    public function getNoOfMembers(): int {
        return $this->noOfMembers;
    }

    public function getMaxMembers(): int {
        return $this->maxMembers;
    }

    public function getGroupStatus(): string {
        return $this->groupStatus;
    }

    // Setters
    private function setGroupId(int $groupId): void {
        $this->groupId = $groupId;
    }

    private function setGroupName(string $groupName): void {
        $this->groupName = $groupName;
    }

    private function setAcadYear(string $acadYear): void {
        $this->acadYear = $acadYear;
    }

    private function setModuleCode(string $moduleCode): void {
        $this->moduleCode = $moduleCode;
    }

    private function setLabGroup(string $labGroup): void {
        $this->labGroup = $labGroup;
    }

    private function setGroupNumber(int $groupNumber): void {
        $this->groupNumber = $groupNumber;
    }

    private function setNoOfMembers(int $noOfMembers): void {
        $this->noOfMembers = $noOfMembers;
    }

    private function setMaxMembers(int $maxMembers): void {
        $this->maxMembers = $maxMembers;
    }

    private function setGroupStatus(string $groupStatus): void {
        $this->groupStatus = $groupStatus;
    }

    // Constructor
    public function __construct(
        string $acadYear,
        string $moduleCode,
        string $labGroup,
        int $groupNumber,
        int $maxMembers,
        string $groupStatus = 'active',
        int $noOfMembers = 0,
    ) {
        $this->setAcadYear($acadYear);
        $this->setModuleCode($moduleCode);
        $this->setLabGroup($labGroup);
        $this->setGroupNumber($groupNumber);
        $this->setMaxMembers($maxMembers);
        $this->setGroupStatus($groupStatus);
        $this->setNoOfMembers($noOfMembers);
    }

    public function isFull(): bool {
        return $this->noOfMembers >= $this->maxMembers;
    }

    public function addMember(): void {
        if (!$this->isFull()) {
            $this->noOfMembers++;
        } else {
            throw new \Exception("Cannot add member: group is full.");
        }
    }

    public function removeMember(): void {
        if ($this->noOfMembers > 0) {
            $this->noOfMembers--;
        } else {
            throw new \Exception("Cannot remove member: no members in the group.");
        }
    }

    public function changeGroupStatus(string $newStatus): void {
        $validStatuses = ['active', 'inactive', 'archived'];
        if (in_array($newStatus, $validStatuses)) {
            $this->setGroupStatus($newStatus);
        } else {
            throw new \Exception("Invalid group status: $newStatus");
        }
    }

    public function generateGroupName(): string {
        $acad = trim($this->acadYear);
        $modCode = trim($this->moduleCode);
        $labGrp = trim($this->labGroup);
        $grpNum  = $this->groupNumber;

        $groupName = "[$acad]-$modCode-$labGrp-G$grpNum";

        $this->setGroupName($groupName);

        return $groupName;
    }
}
?>
