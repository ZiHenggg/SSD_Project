<?php
namespace App\Repository;

use App\Entity\StudentStats;

interface StudentStatsRepository
{

    public function getTotalReviews(int $studentId): int;
    public function getAverageRating(int $studentId): float;

}
?>
