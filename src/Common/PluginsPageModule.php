<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class PluginsPageModule implements ModuleInterface
{
    private PluginMeta $pluginMeta;
    private iterable $modules;
    public function __construct(PluginMeta $pluginMeta, $modules)
    {
        $this->pluginMeta = $pluginMeta;
        $this->modules = $modules;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addFilter('plugin_action_links_' . $this->pluginMeta->getPluginName(), $this, 'addPluginPageActionLinks', 10, 2);
        $loader->addFilter('plugin_row_meta', $this, 'addPluginPageRowLinks', 10, 2);
    }
    public function addPluginPageActionLinks($links)
    {
        $settingsUrl = esc_url(add_query_arg('page', GlobalVariables::SPARKWOO_PREFIX . 'settings', get_admin_url() . 'admin.php'));
        $settingsLink = '<a href="' . $settingsUrl . '">' . __('Settings', 'sparkair') . '</a>';
        $upgradeProLink = '<a target="_blank" href="' . $this->pluginMeta->websiteUrl . '">' . __('Upgrade PRO', 'sparkair') . '</a>';
        $prependLinks = array('settings' => $settingsLink);
        $appendLinks = array();
        if (!$this->pluginMeta->isPro) {
            $appendLinks['pro'] = $upgradeProLink;
        }
        return \array_merge($prependLinks, $links, $appendLinks);
    }
    public function addPluginPageRowLinks($links, $file)
    {
        if ($file != $this->pluginMeta->getPluginName()) {
            return $links;
        }
        $supportLink = '<a target="_blank" href="https://www.sparkplugins.com/support/">' . __('Support', 'sparkair') . '</a>';
        $prependLinks = array();
        $appendLinks = array('support' => $supportLink);
        if (SPARK_DEV_MODE) {
            $appendLinks['modules'] = '<br>Modules: <ul><li>' . \join('</li><li>', \array_map(function ($module) {
                return \str_replace('SparkPlugins\\SparkWoo\\', '', \get_class($module));
            }, \iterator_to_array($this->modules))) . '</li></ul>';
        }
        return \array_merge($prependLinks, $links, $appendLinks);
    }
}
