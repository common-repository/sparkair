<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
interface TrainerInterface
{
    public function train(AirModelPostModel $airPost, bool $activateAfterTraining = \false) : void;
}
