<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Admin;

trait AdminPageTrait
{
    /**
     * Load the plugin admin page partial.
     */
    public function loadAdminPageContent()
    {
        require_once $this->globalVariables->getPluginDirPath() . '/src/ProductRecommendations/Templates/AdminContent.php';
    }
}
