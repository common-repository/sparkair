<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Api;

use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training\TrainerInterface;
use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Utils\DateFunctions;
class AirApiModule implements ApiInterface, ModuleInterface
{
    use ApiTrait;
    protected PluginMeta $pluginMeta;
    protected GlobalVariables $globalVariables;
    protected ApiInterface $api;
    protected TrainerInterface $trainer;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables, ApiInterface $api, TrainerInterface $trainer)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
        $this->api = $api;
        $this->trainer = $trainer;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('rest_api_init', $this, 'registerRoutes');
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'testTrain');
    }
    public function getNameSpace()
    {
        return $this->api->getNameSpace() . '/ai-tools';
    }
    public function registerRoutes()
    {
        $namespace = $this->getNamespace();
        register_rest_route($namespace, '/models/(?P<id>[\\d]+)/train', array(array('methods' => \WP_REST_Server::CREATABLE, 'callback' => array($this, 'trainModel'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
        register_rest_route($namespace, '/models/(?P<id>[\\d]+)/activate', array(array('methods' => \WP_REST_Server::CREATABLE, 'callback' => array($this, 'activateModel'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
    }
    public function trainModel(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $airPost = new AirModelPostModel();
            $airPost = $airPost->load($params['id']);
            $this->trainer->train($airPost);
            return new \WP_REST_Response($this->prepareResponse($airPost), 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', $e, array('status' => 400));
        }
    }
    public function activateModel(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $posts = get_posts(['post_type' => AirModelPostModel::postType(), 'post_status' => 'any', 'numberposts' => -1]);
            foreach ($posts as $post) {
                update_post_meta($post->ID, 'modelActivatedDateTime', null);
            }
            update_post_meta($params['id'], 'modelActivatedDateTime', DateFunctions::nowIso());
            return new \WP_REST_Response(null, 204);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Error activating model.', array('status' => 400));
        }
    }
    public function testTrain()
    {
        if (!isset($_GET['sparkair-train'])) {
            return;
        }
        $airPost = new AirModelPostModel();
        $airPost = $airPost->load(\intval($_GET['sparkair-train']));
        $this->trainer->train($airPost);
    }
}
