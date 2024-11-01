<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Plugins;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
class Plugin implements PluginInterface
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Loader
     */
    protected Loader $loader;
    protected PluginMeta $pluginMeta;
    protected iterable $modules;
    public function __construct(PluginMeta $pluginMeta, $loader, iterable $modules)
    {
        $this->pluginMeta = $pluginMeta;
        $this->loader = $loader;
        $this->modules = $modules;
    }
    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() : void
    {
        foreach ($this->modules as $module) {
            if (is_admin()) {
                $module->defineAdminHooks($this->loader);
            }
            $module->definePublicHooks($this->loader);
        }
        $this->loader->run();
    }
}
