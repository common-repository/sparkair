<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction;

use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
use Sparkair\MathPHP\LinearAlgebra\MatrixFactory;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Utils\PredictionHelperFunctions;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class SvdPredictor implements PredictorInterface
{
    private CacheManager $cacheManager;
    private string $predictionsCacheKeyPrefix;
    public function __construct(CacheManager $cacheManager, string $predictionsCacheKeyPrefix)
    {
        $this->cacheManager = $cacheManager;
        $this->predictionsCacheKeyPrefix = $predictionsCacheKeyPrefix;
    }
    public function getSimilaritiesByProductIds(array $productIds, PostModelInterface $airModel = null) : PredictionCollection
    {
        if (null === $airModel) {
            $airModel = AirModelPostModel::loadActiveAirModel();
        }
        $collection = new PredictionCollection();
        if (empty($airModel)) {
            return $collection;
        }
        $VrArray = $airModel->get('Vr');
        if (empty($VrArray)) {
            return $collection;
        }
        $Vr = MatrixFactory::create($VrArray);
        $productIds = \array_unique($productIds);
        $predictions = array();
        foreach ($productIds as $productId) {
            $itemIndex = $this->getItemIndexById($airModel, $productId);
            if (!$itemIndex) {
                continue;
            }
            for ($itemI = 0; $itemI < $Vr->getN(); $itemI++) {
                $col = $Vr->getColumn($itemI);
                $similarity = PredictionHelperFunctions::cosineSimilarity($Vr->getColumn($itemIndex), $col);
                $itemId = $this->getItemIdByIndex($airModel, $itemI);
                if (!$itemId) {
                    continue;
                }
                $prediction = new Prediction($itemId, null, $similarity);
                $predictions[] = $prediction;
            }
        }
        $collection->setItems($predictions);
        return $collection;
    }
    public function getRecommendationsByUserId(int $userId, bool $useMeansWhenUserNotTrained = \false, PostModelInterface $airModel = null) : PredictionCollection
    {
        if (null === $airModel) {
            $airModel = AirModelPostModel::loadActiveAirModel();
        }
        $collection = new PredictionCollection();
        if (empty($airModel)) {
            return $collection;
        }
        $VrArray = $airModel->get('Vr');
        if (empty($VrArray)) {
            return $collection;
        }
        $UrArray = $airModel->get('Ur');
        if (empty($UrArray)) {
            return $collection;
        }
        $userIndex = $this->getUserIndexById($airModel, $userId);
        if (\false === $userIndex) {
            if ($useMeansWhenUserNotTrained) {
                $userRatings = $airModel->get('itemMeans');
            } else {
                return $collection;
            }
        } else {
            $cacheKey = $this->predictionsCacheKeyPrefix . $airModel->get('id') . '_' . $userIndex;
            $userRatings = $this->cacheManager->get($cacheKey);
            if (!$userRatings) {
                $sRoot = MatrixFactory::create($airModel->get('sRoot'));
                $itemMeans = MatrixFactory::create(array($airModel->get('itemMeans')));
                $Ur = MatrixFactory::create(array($UrArray[$userIndex]));
                $Vr = MatrixFactory::create($VrArray);
                $UsV = $Ur->multiply($sRoot)->multiply($sRoot->multiply($Vr))->add($itemMeans);
                $UsVArray = $UsV->getMatrix();
                $userRatings = $UsVArray[0];
                $this->cacheManager->set($cacheKey, $userRatings);
            }
        }
        $predictions = array();
        foreach ($userRatings as $itemIndex => $rating) {
            $itemId = $this->getItemIdByIndex($airModel, $itemIndex);
            if (!$itemId) {
                continue;
            }
            $prediction = new Prediction($itemId, $rating);
            $predictions[] = $prediction;
        }
        $collection->setItems($predictions);
        return $collection;
    }
    public function getMatch(int $productId, int $userId) : ?int
    {
        $airModel = AirModelPostModel::loadActiveAirModel();
        $predictionCollection = $this->getRecommendationsByUserId($userId, \true, $airModel);
        if ($predictionCollection->isEmpty()) {
            return null;
        }
        $prediction = $predictionCollection->getItemByProductId($productId);
        if (null === $prediction) {
            return null;
        }
        $rating = $prediction->getRating();
        if (null === $rating) {
            return null;
        }
        return \round($rating / 5 * 100);
    }
    public function getEmptyCollection() : PredictionCollection
    {
        return new PredictionCollection();
    }
    private function getUserIdByIndex($airModel, int $index)
    {
        $userIndexIdMap = $airModel->get('userIndexIdMap');
        if (!\array_key_exists($index, $userIndexIdMap)) {
            return \false;
        }
        return $userIndexIdMap[$index];
    }
    private function getItemIdByIndex($airModel, int $index)
    {
        $itemIndexIdMap = $airModel->get('itemIndexIdMap');
        if (!\array_key_exists($index, $itemIndexIdMap)) {
            return \false;
        }
        return $itemIndexIdMap[$index];
    }
    private function getUserIndexById($airModel, int $id)
    {
        $userIndexIdMap = $airModel->get('userIndexIdMap');
        $userIdIndexMap = \array_flip($userIndexIdMap);
        if (!\array_key_exists($id, $userIdIndexMap)) {
            return \false;
        }
        return $userIdIndexMap[$id];
    }
    private function getItemIndexById($airModel, int $id)
    {
        $itemIndexIdMap = $airModel->get('itemIndexIdMap');
        $itemIdIndexMap = \array_flip($itemIndexIdMap);
        if (!\array_key_exists($id, $itemIdIndexMap)) {
            return \false;
        }
        return $itemIdIndexMap[$id];
    }
}
