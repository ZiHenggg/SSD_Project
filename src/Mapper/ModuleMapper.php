<?php
namespace App\Mapper;

use App\Entity\Module;
use App\Repository\ModuleRepository;
use PDO;

class ModuleMapper implements ModuleRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getModule(string $moduleCode): ?Module {
        $stmt = $this->dbConnection->prepare("SELECT * FROM modules WHERE moduleCode = :moduleCode");
        $stmt->bindParam(':moduleCode', $moduleCode);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Module(
                $result['moduleCode'],
                $result['moduleName']
            );
        }

        return null; // Return null if no module found
    }

} 
?>
