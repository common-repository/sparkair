<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
class OtherPluginsModule implements ModuleInterface
{
    protected PluginMeta $pluginMeta;
    protected PluginMetaCollection $pluginMetaCollection;
    protected NotificationModule $notificationModule;
    protected array $pluginsRequired = array();
    protected array $pluginsConflict = array();
    public function __construct(PluginMeta $pluginMeta, PluginMetaCollection $pluginMetaCollection, NotificationModule $notificationModule, array $pluginsRequired, array $pluginsConflict)
    {
        $this->pluginMeta = $pluginMeta;
        $this->pluginMetaCollection = $pluginMetaCollection;
        $this->notificationModule = $notificationModule;
        $this->pluginsRequired = $pluginsRequired;
        $this->pluginsConflict = $pluginsConflict;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'otherPluginRequiredNotice');
        $loader->addAction('admin_init', $this, 'otherPluginConflictsNotice');
    }
    private function getRequiredPluginsNotInstalled()
    {
        $plugins = array();
        foreach ($this->pluginsRequired as $plugin) {
            $pluginPath = $plugin['plugin'];
            if (!\function_exists('is_plugin_active') || \is_plugin_active($pluginPath)) {
                continue;
            }
            \array_push($plugins, $plugin);
        }
        return $plugins;
    }
    public function hasRequiredPluginsNotInstalled()
    {
        return \count($this->getRequiredPluginsNotInstalled()) > 0;
    }
    public function otherPluginRequiredNotice() : void
    {
        if (!$this->hasRequiredPluginsNotInstalled()) {
            return;
        }
        $plugins = $this->getRequiredPluginsNotInstalled();
        $namesText = \implode(', ', \array_map(function ($plugin) {
            return $plugin['name'];
        }, $plugins));
        $this->notificationModule->notify('Required plugins are not installed', 'In order to enjoy all the benefits coming with <strong>' . $this->pluginMeta->getPluginData()['Name'] . '</strong>, the installation of <strong>' . $namesText . ' </strong> is required.', 'warning');
    }
    private function getConflictedPluginsNotInstalled()
    {
        $plugins = array();
        foreach ($this->pluginsConflict as $pluginSlug) {
            $plugin = $this->pluginMetaCollection->getItemBy('slug', $pluginSlug);
            if (!!$plugin && $plugin->isInstalled()) {
                \array_push($plugins, $plugin);
            }
        }
        return $plugins;
    }
    public function hasConflictedPluginsInstalled()
    {
        return \count($this->getConflictedPluginsNotInstalled()) > 0;
    }
    public function otherPluginConflictsNotice() : void
    {
        if (!$this->hasConflictedPluginsInstalled()) {
            return;
        }
        $plugins = $this->getConflictedPluginsNotInstalled();
        $slugs = \implode(', ', \array_map(function ($plugin) {
            return "'" . $plugin->slug . "'";
        }, $plugins));
        $this->notificationModule->notify('Some plugins may conflict with ' . $this->pluginMeta->name . ' plugin', 'It appears that you have the free version of the ' . $this->pluginMeta->name . ' plugin installed, which may be causing conflicts. Please <strong><a href="' . admin_url('plugins.php') . '">deactivate</a></strong> the plugin with the slug(s) <em>' . $slugs . '</em>.', 'warning');
    }
}
