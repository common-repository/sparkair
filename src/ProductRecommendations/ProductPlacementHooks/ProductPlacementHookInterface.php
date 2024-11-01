<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks;

use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\ProductsManagerInterface;
interface ProductPlacementHookInterface extends \JsonSerializable, ModuleInterface
{
    public function getKey();
    public function render();
    public function renderSingle(ProductRecommendationPostModel $productRecommendationPostModel);
    public function getProductsManager() : ProductsManagerInterface;
    public function getAnalyticsEventKey() : string;
    public function getAnalyticsEventEncodedValue(ProductRecommendationPostModel $productRecommendationPostModel) : string;
}
