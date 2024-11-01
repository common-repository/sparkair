<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class I18nModule implements ModuleInterface
{
    protected PluginMeta $pluginMeta;
    protected GlobalVariables $globalVariables;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('plugins_loaded', $this, 'loadPluginTextdomain');
    }
    public function loadPluginTextdomain()
    {
        load_plugin_textdomain($this->pluginMeta->slug, \false, $this->globalVariables->getPluginDir() . '/languages');
    }
}
