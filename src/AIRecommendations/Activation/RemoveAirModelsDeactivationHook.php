<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Activation;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Activation\DeactivationHookInterface;
class RemoveAirModelsDeactivationHook implements DeactivationHookInterface
{
    public function run() : void
    {
        $airModels = get_posts(array('post_type' => AirModelPostModel::postType(), 'numberposts' => -1));
        foreach ($airModels as $post) {
            \wp_delete_post($post->ID, \true);
        }
    }
}
