<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class HomePlacementHook extends AbstractProductPlacementHook implements ProductPlacementHookInterface
{
    protected $actionName;
    public function __construct($key, $name, $description, $productsManager, $partial, PluginMeta $pluginMeta, GlobalVariables $globalVariables, $actionName)
    {
        parent::__construct($key, $name, $description, $productsManager, $partial, $pluginMeta, $globalVariables);
        $this->actionName = $actionName;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction($this->actionName, $this, 'renderHome');
    }
    public function renderHome($content)
    {
        if (!is_front_page()) {
            return $content;
        }
        \ob_start();
        parent::render();
        $content .= \ob_get_clean();
        return $content;
    }
    public function renderSingle($productRecommendationPostModel)
    {
        echo '<div class="woocommerce">';
        parent::renderSingle($productRecommendationPostModel);
        echo '</div>';
    }
}
