<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class DeactivationFeedbackModule implements ModuleInterface, ApiInterface
{
    use ApiTrait;
    protected PluginMeta $pluginMeta;
    protected GlobalVariables $globalVariables;
    protected ApiInterface $api;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables, ApiInterface $api)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
        $this->api = $api;
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
        return $this->api->getNameSpace() . '/deactivate';
    }
    public function registerRoutes()
    {
        $namespace = $this->getNamespace();
        register_rest_route($namespace, '/feedback', array(array('methods' => \WP_REST_Server::CREATABLE, 'callback' => array($this, 'sendFeedback'), 'permission_callback' => array($this, 'alwaysAllowPermissionCheck'), 'args' => array())));
    }
    public function sendFeedback(\WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $reason = $data['reason'] ?? '';
        $elaborate = $data['elaborate'] ?? '';
        $email = get_option('admin_email');
        $websiteUrl = get_site_url();
        $plugin = $this->pluginMeta->name;
        \wp_mail('hello@sparkplugins.com', 'Deactivation Feedback', "Reason: {$reason}\nElaborate: {$elaborate}\nEmail: {$email}\nWebsite: {$websiteUrl}\nPlugin: {$plugin}");
        return rest_ensure_response(array('success' => \true));
    }
}
