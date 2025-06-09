<?php
namespace App\Mapper;

use App\Entity\Reply;
use App\Repository\StudentStatsRepository;
use PDO;

class StudentStatsMapper implements StudentStatsRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getTotalReviews(int $studentId): int {
    $stmt = $this->dbConnection->prepare("SELECT totalReviews FROM studentStats WHERE studentId = :studentId");
    $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && isset($result['totalReviews']) ? (int) $result['totalReviews'] : 0;
    }

    public function getAverageRating(int $studentId): float {
        $stmt = $this->dbConnection->prepare("SELECT averageRating FROM studentStats WHERE studentId = :studentId");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && isset($result['averageRating']) ? (float) $result['averageRating'] : 0.0;
    }

    
} 
?>
