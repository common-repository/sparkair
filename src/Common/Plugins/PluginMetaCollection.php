<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Plugins;

use Sparkair\SparkPlugins\SparkWoo\Common\Collections\AbstractCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Collections\CollectionInterface;
class PluginMetaCollection extends AbstractCollection implements CollectionInterface, \JsonSerializable
{
    /**
     * @var PluginMeta[]
     */
    protected array $items;
    public function __construct(iterable $items)
    {
        $itemsWithoutTest = \array_filter(array(...$items), function ($item) {
            return !$item->test || SPARK_DEV_MODE;
        });
        parent::__construct($itemsWithoutTest);
    }
    public function jsonSerialize($extended = \false) : array
    {
        $json = array();
        foreach ($this->items as $item) {
            $class = \get_class($item);
            $subJson = array();
            foreach (\get_class_vars($class) as $name => $_) {
                $subJson[$name] = $item->{$name};
            }
            $subJson['installed'] = $item->isInstalled();
            \array_push($json, $subJson);
        }
        return $json;
    }
    public function hasOthersInstalled(PluginMeta $currentPlugin, $groupName)
    {
        foreach ($this->items as $item) {
            if ($currentPlugin->slug === $item->slug) {
                continue;
            }
            if (!\in_array($groupName, $item->groups)) {
                continue;
            }
            if ($item->isInstalled()) {
                return \true;
            }
        }
        return \false;
    }
    public function getInstalledProPlugins()
    {
        $plugins = array();
        foreach ($this->items as $item) {
            if ($item->isInstalled() && $item->isPro) {
                $plugins[] = $item;
            }
        }
        return $plugins;
    }
}
