<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations;

use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
use Sparkair\SparkPlugins\SparkWoo\Common\Installation\AbstractUninstaller;
use Sparkair\SparkPlugins\SparkWoo\Common\Installation\UninstallerInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionsCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
class Uninstaller extends AbstractUninstaller implements UninstallerInterface
{
    private string $pluginGroup;
    public function __construct(PluginMeta $pluginMeta, PluginMetaCollection $pluginMetaCollection, OptionInterface $deleteOptionsOption, OptionInterface $deletePostsOption, iterable $postModels, OptionsCollection $options, CacheManager $cacheManager, string $pluginGroup)
    {
        parent::__construct($pluginMeta, $pluginMetaCollection, $deleteOptionsOption, $deletePostsOption, $postModels, $options, $cacheManager);
        $this->pluginGroup = $pluginGroup;
    }
    public function uninstall() : void
    {
        if (!$this->pluginMetaCollection->hasOthersInstalled($this->pluginMeta, $this->pluginGroup)) {
            $this->removePosts();
        }
        $this->removeOptions();
        $this->cacheManager->clear();
    }
}
