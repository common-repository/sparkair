<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

class Activator
{
    /**
     * @var ActivationHooksInterface[]
     */
    private iterable $activationHooks;
    public function __construct(iterable $activationHooks)
    {
        $this->activationHooks = $activationHooks;
    }
    public function activate() : void
    {
        foreach ($this->activationHooks as $activationHook) {
            $activationHook->run();
        }
    }
}
