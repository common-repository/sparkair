<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class DefaultWooCommercePlacementHook extends AbstractProductPlacementHook implements ProductPlacementHookInterface
{
    protected $actionName;
    public function __construct($key, $name, $description, $productsManager, $partial, PluginMeta $pluginMeta, GlobalVariables $globalVariables, $actionName)
    {
        parent::__construct($key, $name, $description, $productsManager, $partial, $pluginMeta, $globalVariables);
        $this->actionName = $actionName;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction($this->actionName, $this, 'render');
    }
}
