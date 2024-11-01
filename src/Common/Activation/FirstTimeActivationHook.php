<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

class FirstTimeActivationHook implements ActivationHookInterface
{
    protected $firstTimePluginUsageOption;
    public function __construct($firstTimePluginUsageOption)
    {
        $this->firstTimePluginUsageOption = $firstTimePluginUsageOption;
    }
    public function run() : void
    {
        add_option($this->firstTimePluginUsageOption, \true);
    }
}
