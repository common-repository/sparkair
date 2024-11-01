<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction;

use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
interface PredictorInterface
{
    public function getSimilaritiesByProductIds(array $productIds, PostModelInterface $airModel = null) : PredictionCollection;
    public function getRecommendationsByUserId(int $userId, bool $useMeansWhenUserNotTrained = \false, PostModelInterface $airModel = null) : PredictionCollection;
    public function getEmptyCollection() : PredictionCollection;
    public function getMatch(int $productId, int $userId);
}
