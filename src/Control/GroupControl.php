<?php
namespace Ngmin\Ict2216G5\Control;

use Ngmin\Ict2216G5\Entity\Group;
use Ngmin\Ict2216G5\Repository\GroupRepository;

class GroupControl {
    private Group $group;
    private GroupRepository $groupRepo;
    public function createGroup(string $acadYear, string $trimester, string $moduleCode, int $labGroup, int $maxMembers, string $adminId): Group {
        // Get the last group number for that config
        $lastGroup = $this->groupRepo->getLastGroupByParams($acadYear, $trimester, $moduleCode, $labGroup);
    
        $nextGroupNumber = $lastGroup ? $lastGroup->getGroupNumber() + 1 : 1;
    
        // Create group
        $group = new Group($acadYear, $trimester, $moduleCode, $labGroup, $nextGroupNumber, 'active', 0, $maxMembers);
        $group->generateGroupName();
    
        return $group;
    }
}
?>