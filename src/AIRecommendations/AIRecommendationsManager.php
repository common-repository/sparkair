<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction\PredictorInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\AbstractProductsManager;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\ProductsManagerInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\RecommendedProductIdsCollection;
class AIRecommendationsManager extends AbstractProductsManager implements ProductsManagerInterface
{
    protected PredictorInterface $predictor;
    public function __construct($slug, $title, $description, $shortcode, PredictorInterface $predictor)
    {
        parent::__construct($slug, $title, $description, $shortcode);
        $this->predictor = $predictor;
    }
    public function getRecommendedProductIdsCollection(ProductRecommendationPostModel $postModel) : RecommendedProductIdsCollection
    {
        global $product;
        $currentProductIds = array();
        $predictionCollection = $this->predictor->getEmptyCollection();
        if (\function_exists('is_checkout') && \is_checkout() || \function_exists('is_cart') && \is_cart()) {
            foreach (WC()->cart->get_cart() as $cartItem) {
                $currentProductIds[] = $cartItem['product_id'];
            }
        } else {
            if (is_single() && $product) {
                $currentProductIds = array($product->get_id());
            }
        }
        if (\count($currentProductIds) > 0) {
            $predictionCollection = $this->predictor->getSimilaritiesByProductIds($currentProductIds);
        }
        // If no product ids and recommendations are found, we will try to get recommendations for the user
        // If user is not logged in, we will show the default recommendations
        if ($predictionCollection->isEmpty()) {
            $userId = get_current_user_id();
            $predictionCollection = $this->predictor->getRecommendationsByUserId($userId, \true);
        }
        if (!empty($userId)) {
            $predictionCollection->filterPreviouslyBought($userId);
        }
        $predictionCollection->filterNonExistingProducts();
        $predictionCollection->filterProductIds($currentProductIds);
        $predictionCollection->sortByPrediction();
        $predictionCollection->unique();
        $productIds = $predictionCollection->map(function ($p) {
            return $p->getProductId();
        });
        return (new RecommendedProductIdsCollection($productIds))->filterCurrentlyInCart()->filterCurrent();
    }
}
