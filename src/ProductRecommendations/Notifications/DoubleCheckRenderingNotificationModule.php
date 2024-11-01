<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Notifications;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
class DoubleCheckRenderingNotificationModule implements ModuleInterface
{
    use DismissNotificationTrait;
    public const DISMISS_PARAM_NAME = 'dismiss_frontend_rendering_notification';
    protected NotificationModule $notificationModule;
    protected BooleanOption $dismissedOption;
    protected PluginMeta $pluginMeta;
    public function __construct(NotificationModule $notificationModule, BooleanOption $dismissedOption, PluginMeta $pluginMeta)
    {
        $this->notificationModule = $notificationModule;
        $this->dismissedOption = $dismissedOption;
        $this->pluginMeta = $pluginMeta;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'sendNotification');
    }
    public function sendNotification() : void
    {
        if ($this->handleDismissedBoolean($this->pluginMeta, $this->dismissedOption, self::DISMISS_PARAM_NAME, \false)) {
            return;
        }
        $dismissButton = array('url' => $this->getDismissUrl($this->pluginMeta, self::DISMISS_PARAM_NAME), 'text' => 'Dismiss this message', '_blank' => \false);
        $args = array('post_type' => ProductRecommendationPostModel::postType());
        $q = new \WP_Query($args);
        $posts = $q->posts;
        $count = 0;
        foreach ($posts as $post) {
            $pm = new ProductRecommendationPostModel();
            $pm = $pm->postToObject($post);
            if ($this->pluginMeta->extra['productsManager']['slug'] === $pm->get('productsManager')) {
                $count++;
            }
        }
        if ($count === 0) {
            $this->dismissedOption->setValue(\true);
        } else {
            $this->notificationModule->notify('⚠️ Action required: Double-check your product recommendation rendering settings', 'We have updated the way product recommendations are rendered on your website. Please double-check them on your webshop and tune the settings to ensure the best experience for your customers.', 'warning', array($dismissButton));
        }
    }
}
