<?php

namespace App\Entity;

class LabGroup { 
    private string $labGroupCode;
    private string $moduleCode;

    public function __construct(string $labGroupCode, string $moduleCode) {
        $this->labGroupCode = $labGroupCode;
        $this->moduleCode = $moduleCode;
    }

    public function getLabGroupCode(): string {
        return $this->labGroupCode;
    }

    public function getModuleCode(): string {
        return $this->moduleCode;
    }
}
?>
