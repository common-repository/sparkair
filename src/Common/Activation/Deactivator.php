<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

class Deactivator implements DeactivatorInterface
{
    /**
     * @var DeactivationHooksInterface[]
     */
    private iterable $deactivationHooks;
    public function __construct(iterable $deactivationHooks)
    {
        $this->deactivationHooks = $deactivationHooks;
    }
    public function deactivate() : void
    {
        foreach ($this->deactivationHooks as $deactivationHook) {
            $deactivationHook->run();
        }
    }
}
