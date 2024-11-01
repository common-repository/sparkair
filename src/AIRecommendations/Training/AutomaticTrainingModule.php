<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training\TrainerInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class AutomaticTrainingModule implements ModuleInterface
{
    protected TrainerInterface $trainer;
    protected PluginMeta $pluginMeta;
    protected string $cronHook;
    public function __construct(TrainerInterface $trainer, PluginMeta $pluginMeta)
    {
        $this->trainer = $trainer;
        $this->pluginMeta = $pluginMeta;
        $this->cronHook = $pluginMeta->prefix . 'automatic_first_training';
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('init', $this, 'scheduleEvent');
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction($this->cronHook, $this, 'firstTimeTraining');
    }
    public function scheduleEvent() : void
    {
        if (!\wp_next_scheduled($this->cronHook)) {
            \wp_schedule_single_event(\time(), $this->cronHook);
        }
    }
    public function firstTimeTraining() : void
    {
        $airPost = AirModelPostModel::loadActiveAirModel();
        /** @var AirModelPostModel $airPost */
        if (!empty($airPost) && !$airPost->isTrained() && !$airPost->isTraining()) {
            $this->trainer->train($airPost);
        }
    }
}
