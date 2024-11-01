<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics\AnalyticsEvent;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics\AnalyticsEventModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\ProductsManagerInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials\PartialInterface;
abstract class AbstractProductPlacementHook
{
    protected string $key;
    protected string $name;
    protected string $description;
    protected ProductsManagerInterface $productsManager;
    protected PartialInterface $partial;
    protected PluginMeta $pluginMeta;
    protected GlobalVariables $globalVariables;
    public function __construct($key, $name, $description, $productsManager, $partial, PluginMeta $pluginMeta, GlobalVariables $globalVariables)
    {
        $this->key = $key;
        $this->name = $name;
        $this->description = $description;
        $this->productsManager = $productsManager;
        $this->partial = $partial;
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function getProductsManager() : ProductsManagerInterface
    {
        return $this->productsManager;
    }
    public function getKey()
    {
        return $this->key;
    }
    public function render()
    {
        $models = $this->productsManager->getProductRecommendationPostModelsByHook($this->key);
        foreach ($models as $productRecommendationPostModel) {
            $this->renderSingle($productRecommendationPostModel);
        }
    }
    public function renderSingle(ProductRecommendationPostModel $productRecommendationPostModel)
    {
        $designSettings = $productRecommendationPostModel->get('designSettings');
        $displayCount = \array_key_exists('numberToShow', $designSettings) ? $designSettings['numberToShow'] : 4;
        $showOutOfStockProducts = \array_key_exists('showOutOfStockProducts', $designSettings) ? $designSettings['showOutOfStockProducts'] : \false;
        $recommendedProductIdsCollection = $this->productsManager->getRecommendedProductIdsCollection($productRecommendationPostModel);
        $productIds = $recommendedProductIdsCollection->getItems();
        $hideNoProducts = \array_key_exists('hideNoProducts', $designSettings) ? $designSettings['hideNoProducts'] : \false;
        if ($hideNoProducts && \count($productIds) === 0) {
            return;
        }
        $productQuery = \false;
        if (\count($productIds) > 0) {
            $args = array('post__in' => $productIds, 'post_type' => 'product', 'post_status' => 'publish', 'orderby' => 'post__in', 'posts_per_page' => $displayCount);
            if (!$showOutOfStockProducts) {
                $args['meta_query'] = array(array('key' => '_stock_status', 'value' => 'outofstock', 'compare' => '!='));
            }
            $productQuery = new \WP_Query($args);
        }
        if (\false !== $productQuery) {
            do_action($this->pluginMeta->prefix . 'product_recommendations_analytics_event', AnalyticsEventModel::RENDER, $this, $this->pluginMeta, $productRecommendationPostModel, $productQuery->posts);
        }
        $analyticsEventKey = $this->getAnalyticsEventKey();
        $analyticsEventValue = $this->getAnalyticsEventEncodedValue($productRecommendationPostModel);
        $callbackLinkFilter = function ($postLink, $post) use($analyticsEventKey, $analyticsEventValue) {
            if (is_admin()) {
                return $postLink;
            }
            $url = add_query_arg(array($analyticsEventKey => $analyticsEventValue), $postLink);
            $url = \wp_nonce_url($url, $analyticsEventKey);
            return $url;
        };
        $callbackCartLinkFilter = function ($button, $product) use($analyticsEventKey, $analyticsEventValue) {
            $callbackReplace = function ($matches) use($analyticsEventKey, $analyticsEventValue) {
                $nonce = \wp_create_nonce($analyticsEventKey);
                $link = add_query_arg(array($analyticsEventKey => $analyticsEventValue, '_wpnonce' => $nonce), $matches[1]);
                return 'href="' . $link . '" data-' . $analyticsEventKey . '_nonce=' . $nonce . ' data-' . $analyticsEventKey . '="' . $analyticsEventValue . '"';
            };
            return \preg_replace_callback('/href="([^"]*)"/', $callbackReplace, $button);
        };
        add_filter('post_type', $callbackLinkFilter, 20, 2);
        add_filter('post_type_link', $callbackLinkFilter, 20, 2);
        add_filter('woocommerce_loop_add_to_cart_link', $callbackCartLinkFilter, 20, 2);
        if (\false !== $productQuery) {
            $products = \array_map('wc_get_product', $productQuery->posts);
        }
        $this->partial->render($productRecommendationPostModel, \false !== $productQuery ? $products : array(), $this);
        remove_filter('post_type', $callbackLinkFilter, 20);
        remove_filter('post_type_link', $callbackLinkFilter, 20);
        remove_filter('woocommerce_loop_add_to_cart_link', $callbackCartLinkFilter, 20, 2);
    }
    public function jsonSerialize() : array
    {
        $json = array();
        $class = \get_class($this);
        foreach (\get_class_vars($class) as $name => $_) {
            $json[$name] = $this->{$name};
        }
        return $json;
    }
    public function getAnalyticsEventKey() : string
    {
        return $this->pluginMeta->prefix . 'analytics_event';
    }
    public function getAnalyticsEventEncodedValue(ProductRecommendationPostModel $productRecommendationPostModel) : string
    {
        return \base64_encode(\wp_json_encode(array('placementHook' => $this->getKey(), 'productsManager' => $this->getProductsManager()->getSlug(), 'plugin' => $this->pluginMeta->slug, 'recommendationId' => $productRecommendationPostModel->get('id'))));
    }
}
