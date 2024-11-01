<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction;

class Prediction
{
    protected int $productId;
    protected ?float $similarity;
    protected ?float $rating;
    public function __construct(int $productId, float $rating = null, float $similarity = null)
    {
        $this->productId = $productId;
        $this->rating = $rating;
        $this->similarity = $similarity;
    }
    public function getProductId()
    {
        return $this->productId;
    }
    public function getSimilarity($percentage = \false)
    {
        if (null === $this->similarity) {
            return $percentage ? 0 : null;
        }
        return $percentage ? \round($this->similarity * 100) : $this->similarity;
    }
    public function getRating($rounded = \false)
    {
        if (null === $this->rating) {
            return $rounded ? 0 : null;
        }
        return $rounded ? \round(\round($this->rating * 2) / 2, 1) : $this->rating;
    }
}
