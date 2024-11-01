<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Api;

use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionsCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class BaseApiModule extends \WP_REST_Controller implements ApiInterface, ModuleInterface
{
    use ApiTrait;
    private $apiVersion;
    private PluginMeta $pluginMeta;
    /**
     * @var PostModelInterface[]
     */
    private iterable $postModels = array();
    private OptionsCollection $options;
    private CacheManager $cacheManager;
    public function __construct(PluginMeta $pluginMeta, string $apiVersion, iterable $postModels, OptionsCollection $options, CacheManager $cacheManager)
    {
        $this->pluginMeta = $pluginMeta;
        $this->apiVersion = $apiVersion;
        $this->postModels = $postModels;
        $this->options = $options;
        $this->cacheManager = $cacheManager;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('rest_api_init', $this, 'registerRoutes');
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function getNameSpace()
    {
        return $this->pluginMeta->slug . '/v' . $this->apiVersion;
    }
    protected function getPostModel(string $postType) : ?PostModelInterface
    {
        foreach ($this->postModels as $postModel) {
            if ($postType == $postModel::postType()) {
                return $postModel;
            }
        }
        return null;
    }
    /**
     * Register the routes for the objects of the controller.
     */
    public function registerRoutes()
    {
        $namespace = $this->getNamespace();
        foreach ($this->postModels as $postModel) {
            register_rest_route($namespace, '/(?P<postType>' . $postModel::postType() . ')', array(array('methods' => \WP_REST_Server::READABLE, 'callback' => array($this, 'getPosts'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array()), array('methods' => \WP_REST_Server::CREATABLE, 'callback' => array($this, 'addPost'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
            register_rest_route($namespace, '/(?P<postType>' . $postModel::postType() . ')/(?P<id>[\\d]+)', array(array('methods' => \WP_REST_Server::READABLE, 'callback' => array($this, 'getPost'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array()), array('methods' => \WP_REST_Server::EDITABLE, 'callback' => array($this, 'editPost'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array()), array('methods' => \WP_REST_Server::DELETABLE, 'callback' => array($this, 'deletePost'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
        }
        register_rest_route($namespace, '/options', array(array('methods' => \WP_REST_Server::READABLE, 'callback' => array($this, 'getOptions'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array()), array('methods' => \WP_REST_Server::EDITABLE, 'callback' => array($this, 'updateOptions'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
        register_rest_route($namespace, '/cache', array(array('methods' => \WP_REST_Server::DELETABLE, 'callback' => array($this, 'clearCache'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
    }
    /**
     * Get all items from collection
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|WP_REST_Response
     */
    public function getPosts(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $postModel = $this->getPostModel($params['postType']);
            $args = array('orderby' => 'post_date', 'order' => 'DESC', 'post_type' => $params['postType'], 'numberposts' => -1, 'post_status' => 'any');
            $model_posts = get_posts($args);
            $models = array();
            foreach ($model_posts as $post) {
                \array_push($models, $postModel->postToObject($post));
            }
            $data = $this->prepareResponse($models);
            return new \WP_REST_Response($data, 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Items can not be loaded', array('status' => 400));
        }
    }
    public function getPost(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $postModel = $this->getPostModel($params['postType']);
            $object = $postModel->load($params['id']);
            if (!$object) {
                return new \WP_Error(404, 'Item niet gevonden');
            }
            $data = $this->prepareResponse($object);
            return new \WP_REST_Response($data, 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Item can not be loaded', array('status' => 400));
        }
    }
    public function addPost(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $body = $request->get_json_params();
            $postModel = $this->getPostModel($params['postType']);
            $model = $postModel->fromArray($body);
            $model->persist();
            $object = $postModel->load($model->get('id'));
            $data = $this->prepareResponse($object);
            return new \WP_REST_Response($data, 201);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Item can not be added', array('status' => 400));
        }
    }
    public function editPost(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $body = $request->get_json_params();
            $postModel = $this->getPostModel($params['postType']);
            $object = $postModel->load($params['id']);
            if (!$object) {
                return new \WP_Error(404, 'Item niet gevonden');
            }
            $newObject = $postModel->fromArray(\array_merge(array('id' => $params['id']), $body));
            $newObject->persist();
            $updatedObject = $postModel->load($newObject->get('id'));
            $data = $this->prepareResponse($updatedObject);
            return new \WP_REST_Response($data, 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Item can not be editted', array('status' => 400));
        }
    }
    public function deletePost(\WP_REST_Request $request)
    {
        try {
            $params = $request->get_params();
            $postModel = $this->getPostModel($params['postType']);
            $object = $postModel->load($params['id']);
            if (!$object) {
                return new \WP_Error(404, 'Item niet gevonden');
            }
            $object->delete();
            return new \WP_REST_Response(null, 204);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Items can not be deleted', array('status' => 400));
        }
    }
    public function getOptions(\WP_REST_Request $request)
    {
        try {
            $data = $this->prepareResponse($this->options->getOptionKeyValue());
            return new \WP_REST_Response($data, 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Options can not be loaded', array('status' => 400));
        }
    }
    public function updateOptions(\WP_REST_Request $request)
    {
        try {
            $body = $request->get_json_params();
            foreach ($body as $optionKey => $optionValue) {
                foreach ($this->options->getItems() as $option) {
                    if ($option->getName() != $optionKey) {
                        continue;
                    }
                    $option->setValue($optionValue);
                }
            }
            $data = $this->prepareResponse($this->options->getOptionKeyValue());
            return new \WP_REST_Response($data, 200);
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Options can not be loaded', array('status' => 400));
        }
    }
    public function clearCache()
    {
        try {
            $cleared = $this->cacheManager->clear();
            if ($cleared) {
                return new \WP_REST_Response(null, 204);
            } else {
                return new \WP_Error('error', 'Cache can not be cleared', array('status' => 500));
            }
        } catch (\Exception $e) {
            return new \WP_Error('error', 'Cache can not be cleared', array('status' => 400));
        }
    }
}
