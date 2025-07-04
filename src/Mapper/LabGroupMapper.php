<?php
namespace App\Mapper;

use App\Entity\LabGroup;
use App\Repository\LabGroupRepository;
use PDO;

class LabGroupMapper implements LabGroupRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getLabGroupsByModuleCode(string $moduleCode): array {
        $stmt = $this->dbConnection->prepare("SELECT labGroupCode FROM labGroups WHERE moduleCode = :moduleCode");
        $stmt->bindParam(':moduleCode', $moduleCode);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $labGroups = [];

        foreach ($results as $result) {
            $labGroups[] = new LabGroup(
                $result['labGroupCode'],
                $moduleCode
            );
        }

        return $labGroups; // Return an array of lab groups for the specified module
    }

} 
?>
