<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Modules;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
interface ModuleInterface
{
    public function defineAdminHooks(Loader $loader) : void;
    public function definePublicHooks(Loader $loader) : void;
}
