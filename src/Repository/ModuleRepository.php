<?php
namespace App\Repository;
use App\Entity\Module; 

interface ModuleRepository {
    public function getModule(string $moduleCode): ?Module;
    public function getAllModules(): array;
}
?>