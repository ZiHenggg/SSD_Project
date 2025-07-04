<?php
namespace App\Repository;
use App\Entity\LabGroup; 

interface LabGroupRepository {
    public function getLabGroupsByModuleCode(string $moduleCode): array;
    public function getAllLabGroups(): array;
}
?>