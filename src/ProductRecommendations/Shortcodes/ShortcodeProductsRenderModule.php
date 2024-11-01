<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Shortcodes;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks\ProductPlacementHookInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\ProductsManagerInterface;
class ShortcodeProductsRenderModule implements ModuleInterface
{
    private ProductPlacementHookInterface $shortcodePlacementHook;
    private PostModelInterface $productRecommendationPostModel;
    private ProductsManagerInterface $productsManager;
    public function __construct(ProductPlacementHookInterface $shortcodePlacementHook, PostModelInterface $productRecommendationPostModel, ProductsManagerInterface $productsManager)
    {
        $this->shortcodePlacementHook = $shortcodePlacementHook;
        $this->productRecommendationPostModel = $productRecommendationPostModel;
        $this->productsManager = $productsManager;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addFilter('init', $this, 'addProductRecommendationsShortCode');
    }
    public function addProductRecommendationsShortCode()
    {
        add_shortcode($this->productsManager->getShortcode(), array($this, 'renderProductRecommendationsShortCode'));
    }
    public function renderProductRecommendationsShortCode($attrs, $content = '')
    {
        $params = shortcode_atts(array('id' => \false), $attrs);
        $id = $params['id'];
        if (!$id) {
            return;
        }
        $productRecommendationPostModel = $this->productRecommendationPostModel->load($id);
        if (!$productRecommendationPostModel) {
            return '';
        }
        if ($productRecommendationPostModel->get('status') !== 'publish') {
            return '';
        }
        \ob_start();
        echo '<div class="woocommerce">';
        $this->shortcodePlacementHook->renderSingle($productRecommendationPostModel);
        echo '</div>';
        return \ob_get_clean();
    }
}
