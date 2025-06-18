<?php

namespace App\Entity;

class Module { 
    private string $moduleCode;
    private string $moduleName;

    public function __construct(string $moduleCode, string $moduleName) {
        $this->moduleCode = $moduleCode;
        $this->moduleName = $moduleName;
    }
    
    public function getModuleCode(): string {
        return $this->moduleCode;
    }
    public function getModuleName(): string {
        return $this->moduleName;
    }
}
?>
