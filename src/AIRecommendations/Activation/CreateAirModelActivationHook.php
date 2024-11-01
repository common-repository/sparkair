<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Activation;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationHookInterface;
class CreateAirModelActivationHook implements ActivationHookInterface
{
    public function run() : void
    {
        $posts = new \WP_Query(array('post_type' => AirModelPostModel::postType(), 'post_status' => 'any', 'numberposts' => -1));
        if ($posts->found_posts > 0) {
            return;
        }
        $airPost = new AirModelPostModel();
        $airPost->set('name', get_bloginfo('name') . '\'s first model');
        $airPost->activateNow();
    }
}
