<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Plugins;

class GlobalVariables
{
    const SPARKWOO_PREFIX = 'sparkwoo_';
    private $pluginDirPath = '';
    private $pluginDir = '';
    private $pluginUrl = '';
    private $pluginFilePath = '';
    public function setPluginDirPath($v)
    {
        $this->pluginDirPath = \rtrim($v, '/');
    }
    public function getPluginDirPath()
    {
        return $this->pluginDirPath;
    }
    public function setPluginDir($v)
    {
        $this->pluginDir = \rtrim($v, '/');
    }
    public function getPluginDir()
    {
        return $this->pluginDir;
    }
    public function setPluginUrl($v)
    {
        $this->pluginUrl = \rtrim($v, '/');
    }
    public function getPluginUrl()
    {
        return $this->pluginUrl;
    }
    public function setPluginFilePath($v)
    {
        $this->pluginFilePath = $v;
    }
    public function getPluginFilePath()
    {
        return $this->pluginFilePath;
    }
}
