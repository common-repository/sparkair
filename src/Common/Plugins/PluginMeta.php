<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Plugins;

class PluginMeta
{
    public string $name;
    public string $slug;
    public string $websiteUrl;
    public string $prefix;
    public string $version;
    public bool $isPro;
    public bool $test;
    public bool $soon;
    public array $groups;
    public array $extra;
    public function __construct(string $name, string $slug, string $websiteUrl, string $prefix, string $version, bool $isPro, bool $test, bool $soon, array $groups, array $extra)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->websiteUrl = $websiteUrl;
        $this->prefix = $prefix;
        $this->version = $version;
        $this->isPro = $isPro;
        $this->test = $test;
        $this->soon = $soon;
        $this->groups = $groups;
        $this->extra = $extra;
    }
    public function getPluginName()
    {
        return "{$this->slug}/{$this->slug}.php";
    }
    public function getPluginData()
    {
        return get_plugins()["{$this->slug}/{$this->slug}.php"];
    }
    public function isInstalled()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        return \is_plugin_active($this->getPluginName());
    }
}
