<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Blocks;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class RegisterBlockModule implements ModuleInterface
{
    protected GlobalVariables $globalVariables;
    protected PluginMeta $pluginMeta;
    protected string $blockName;
    public function __construct(GlobalVariables $globalVariables, PluginMeta $pluginMeta, string $blockName)
    {
        $this->globalVariables = $globalVariables;
        $this->pluginMeta = $pluginMeta;
        $this->blockName = $blockName;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('init', $this, 'registerBlock');
    }
    public function registerBlock() : void
    {
        // register_block_type(
        //   $this->globalVariables->getPluginDirPath() . '/blocks/' . $this->blockName . '/build'
        // );
    }
}
