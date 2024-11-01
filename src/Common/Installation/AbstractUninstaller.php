<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Installation;

use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionsCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
abstract class AbstractUninstaller
{
    protected PluginMeta $pluginMeta;
    protected PluginMetaCollection $pluginMetaCollection;
    protected OptionInterface $deleteOptionsOption;
    protected OptionInterface $deletePostsOption;
    /**
     * @var PostModelInterface[]
     */
    protected iterable $postModels;
    protected OptionsCollection $options;
    protected CacheManager $cacheManager;
    public function __construct(PluginMeta $pluginMeta, PluginMetaCollection $pluginMetaCollection, OptionInterface $deleteOptionsOption, OptionInterface $deletePostsOption, iterable $postModels, OptionsCollection $options, CacheManager $cacheManager)
    {
        $this->pluginMeta = $pluginMeta;
        $this->pluginMetaCollection = $pluginMetaCollection;
        $this->deleteOptionsOption = $deleteOptionsOption;
        $this->deletePostsOption = $deletePostsOption;
        $this->postModels = $postModels;
        $this->options = $options;
        $this->cacheManager = $cacheManager;
    }
    public function uninstall() : void
    {
        $this->removePosts();
        $this->removeOptions();
    }
    protected function removePosts() : void
    {
        if ($this->deletePostsOption->getValue(\false)) {
            foreach ($this->postModels as $postModel) {
                /** @var PostModelInterface $postModel */
                $allPosts = get_posts(array('post_type' => $postModel::postType(), 'numberposts' => -1));
                foreach ($allPosts as $post) {
                    \wp_delete_post($post->ID, \true);
                }
            }
        }
    }
    protected function removeOptions() : void
    {
        if ($this->deleteOptionsOption->getValue(\false)) {
            foreach ($this->options as $option) {
                $option->delete();
            }
        }
    }
}
