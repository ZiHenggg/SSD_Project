<?php

namespace Ngmin\Ict2216G5\Entity;

use DateTime;

class Review {
    private int $reviewId;
    private int $reviewerId;
    private int $revieweeId;
    private int $rating;
    private string $reviewDescription;
    private DateTime $reviewDate;

    // Getters
    public function getReviewId(): int {
        return $this->reviewId;
    }
    public function getReviewerId(): int {
        return $this->reviewerId;
    }
    public function getRevieweeId(): int {
        return $this->revieweeId;
    }
    public function getRating(): int {
        return $this->rating;
    }
    public function getReviewDescription(): string {
        return $this->reviewDescription;
    }
    public function getReviewDate(): DateTime {
        return $this->reviewDate;
    }

    // Constructor
    public function __construct(int $reviewId, int $reviewerId, int $revieweeId, int $rating, string $reviewDescription, string $reviewDate) {
        $this->reviewId = $reviewId;
        $this->reviewerId = $reviewerId;
        $this->revieweeId = $revieweeId;
        $this->rating = $rating;
        $this->reviewDescription = $reviewDescription;
        $this->reviewDate = new DateTime($reviewDate);  // Convert to DateTime object
    }

    // // Setters
    // private function setReviewId(int $reviewId): void {
    //     $this->reviewId = $reviewId;
    // }
    // private function setReviewerId(int $reviewerId): void {
    //     $this->reviewerId = $reviewerId;
    // }
    // private function setRevieweeId(int $revieweeId): void {
    //     $this->revieweeId = $revieweeId;
    // }
    // private function setRating(int $rating): void {
    //     $this->rating = $rating;
    // }
    // private function setReviewDescription(string $reviewDescription): void {
    //     $this->reviewDescription = $reviewDescription;
    // }
    // private function setReviewDate(DateTime $reviewDate): void {
    //     $this->reviewDate = $reviewDate;
    // }
}

?>