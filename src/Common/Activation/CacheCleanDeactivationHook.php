<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
class CacheCleanDeactivationHook implements DeactivationHookInterface
{
    protected CacheManager $cacheManager;
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
    public function run() : void
    {
        $this->cacheManager->clear();
        flush_rewrite_rules(\false);
    }
}
