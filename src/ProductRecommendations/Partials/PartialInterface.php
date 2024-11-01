<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials;

use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks\ProductPlacementHookInterface;
interface PartialInterface
{
    /**
     * @var ProductRecommendationPostModel $productRecommendationPostModel
     * @var \WC_Product[] $products
     */
    public function render(ProductRecommendationPostModel $productRecommendationPostModel, array $products, ProductPlacementHookInterface $placementHook = null);
}
