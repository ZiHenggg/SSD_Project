<?php

namespace App\Entity;

class StudentStats { 
    private int $studentStatsId;
    private int $studentId;
    private int $totalReviews;
    private float $averageRating;

    // Getters
    public function getStudentStatsId(): int {
        return $this->studentStatsId;
    }
    public function getStudentId(): int {
        return $this->studentId;
    }
    public function getTotalReviews(): int {
        return $this->totalReviews;
    }
    public function getAverageRating(): float {
        return $this->averageRating;
    }

    // Setters
    private function setStudentStatsId(int $studentStatsId): void {
        $this->studentStatsId = $studentStatsId;
    }
    private function setStudentId(int $studentId): void {
        $this->studentId = $studentId;
    }
    private function setTotalReviews(int $totalReviews): void {
        $this->totalReviews = $totalReviews;
    }
    private function setAverageRating(float $averageRating): void {
        $this->averageRating = $averageRating;
    }

    public function __construct(int $studentStatsId, int $studentId, int $totalReviews, float $averageRating) {
        $this->setStudentStatsId($studentStatsId);
        $this->setStudentId($studentId);
        $this->setTotalReviews($totalReviews);
        $this->setAverageRating($averageRating);
    }

}
?>
