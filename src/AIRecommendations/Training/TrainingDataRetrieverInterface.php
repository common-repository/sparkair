<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

interface TrainingDataRetrieverInterface
{
    public function retrieve() : array;
}
