<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Preview;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials\PartialInterface;
class ProductPreviewModule implements ModuleInterface
{
    protected PluginMeta $pluginMeta;
    protected PartialInterface $partial;
    protected GlobalVariables $globalVariables;
    protected string $template;
    public function __construct(PluginMeta $pluginMeta, PartialInterface $partial, GlobalVariables $globalVariables, string $template)
    {
        $this->pluginMeta = $pluginMeta;
        $this->partial = $partial;
        $this->globalVariables = $globalVariables;
        $this->template = $template;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addFilter('admin_init', $this, 'removeDeprecatedPages');
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addFilter('template_include', $this, 'loadProductPreview');
    }
    public function removeDeprecatedPages() : void
    {
        $args = array('post_type' => 'page', 'posts_per_page' => 1, 'post_status' => 'any', 'meta_key' => '_wp_page_template', 'meta_value' => $this->template, 'fields' => 'ids');
        $pages = get_posts($args);
        if (\count($pages) === 0) {
            return;
        }
        foreach ($pages as $pageId) {
            \wp_delete_post($pageId, \true);
        }
    }
    public function loadProductPreview($template) : string
    {
        if (!current_user_can('manage_options') || !isset($_GET['sparkwooProductRecommendationPostModelData']) || !isset($_GET['sparkwooPluginSlug'])) {
            return $template;
        }
        $newTemplate = \realpath($this->globalVariables->getPluginDirPath() . '/' . $this->template);
        if ('' == $newTemplate) {
            return $template;
        }
        return $newTemplate;
    }
}
