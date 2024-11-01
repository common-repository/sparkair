<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Cache;

use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class CacheManager
{
    private PluginMeta $pluginMeta;
    public function __construct($pluginMeta)
    {
        $this->pluginMeta = $pluginMeta;
    }
    /**
     * Get data from cache
     *
     * @return bool|mixed
     */
    public function get($key, $force = \false)
    {
        if (!$force && SPARK_DEV_MODE) {
            return \false;
        }
        return get_transient($this->getPrefix() . $key);
    }
    /**
     * Set cache data and save in option with TTL
     */
    public function set($key, $data, $expire = null) : bool
    {
        if (empty($expire)) {
            $expire = \strtotime('tomorrow') - \time();
        }
        return set_transient($this->getPrefix() . $key, $data, $expire);
    }
    private function getPrefix()
    {
        return $this->pluginMeta->prefix;
    }
    public function clear($key = null, $keyIsPrefix = \false)
    {
        if (empty($key) || $keyIsPrefix) {
            global $wpdb;
            $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE %s", '_transient_' . $wpdb->esc_like($this->getPrefix()) . '%'));
            return \true;
        } else {
            return delete_transient($this->getPrefix() . $key);
        }
    }
}
