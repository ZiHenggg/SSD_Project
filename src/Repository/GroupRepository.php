<?php
namespace Ngmin\Ict2216G5\Repository;
use Ngmin\Ict2216G5\Entity\Group;

interface GroupRepository {

    /**
     * Retrieves the last group by parameters.
     *
     * @param string $acadYear The academic year.
     * @param string $trimester The trimester.
     * @param string $moduleCode The module code.
     * @param int $labGroup The lab group number.
     * @return Group|null The last group found, or null if none exists.
     * 
     * SELECT MAX(group_no) AS last_group_no
     *  FROM groups
     *  WHERE acad_year = :acadYear
     *  AND trimester = :trimester
     *  AND module_code = :moduleCode
     *  AND lab_group = :labGroup;
     */
    public function getLastGroupByParams(string $acadYear, string $trimester, string $moduleCode, int $labGroup): ?Group;

    public function addGroup(Group $group): void;
}
?>