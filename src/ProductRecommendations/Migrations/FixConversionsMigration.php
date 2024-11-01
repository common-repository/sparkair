<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Migrations;

use Sparkair\SparkPlugins\SparkWoo\Common\Migrations\MigrationInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics\AnalyticsEventRepository;
// This file can be removed when releasing a new version, also remove it from common.yaml
class FixConversionsMigration implements MigrationInterface
{
    protected AnalyticsEventRepository $eventRepository;
    protected PluginMeta $pluginMeta;
    public function __construct(PluginMeta $pluginMeta, AnalyticsEventRepository $eventRepository)
    {
        $this->pluginMeta = $pluginMeta;
        $this->eventRepository = $eventRepository;
    }
    public function up() : void
    {
        $events = $this->eventRepository->getConversionEventsSinceUpdate();
        foreach ($events as $event) {
            $isConvertable = $this->eventRepository->getConvertableClickEvent($event['plugin'], $event['productId'], $event['userId'], $event['sessionId'], $event['timestamp']);
            if (empty($isConvertable)) {
                $this->eventRepository->delete($event['id']);
            }
        }
    }
    public function down() : void
    {
        // Pass
    }
    public function getVersion()
    {
        if ($this->pluginMeta->slug === "sparkrvp") {
            return "1.2.5";
        } else {
            if ($this->pluginMeta->slug === "sparkrvp-pro") {
                return "1.2.5";
            } else {
                if ($this->pluginMeta->slug === "sparkfbt") {
                    return "1.1.8";
                } else {
                    if ($this->pluginMeta->slug === "sparkfbt-pro") {
                        return "1.1.8";
                    } else {
                        if ($this->pluginMeta->slug === "sparkfp") {
                            return "1.0.5";
                        } else {
                            if ($this->pluginMeta->slug === "sparkfp-pro") {
                                return "1.0.5";
                            }
                        }
                    }
                }
            }
        }
        return null;
    }
}
