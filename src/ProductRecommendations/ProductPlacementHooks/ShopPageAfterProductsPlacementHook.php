<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks;

class ShopPageAfterProductsPlacementHook extends DefaultWooCommercePlacementHook implements ProductPlacementHookInterface
{
    protected $actionName;
    public function render()
    {
        if (is_single()) {
            return;
        }
        parent::render();
    }
}
