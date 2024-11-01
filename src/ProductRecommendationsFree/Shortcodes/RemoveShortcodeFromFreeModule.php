<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendationsFree\Shortcodes;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager\ProductsManagerInterface;
class RemoveShortcodeFromFreeModule implements ModuleInterface
{
    use DismissNotificationTrait;
    const DISMISS_SHORTCODE_REMOVED = 'dismiss_shortcode_removed';
    protected PluginMeta $pluginMeta;
    protected ProductsManagerInterface $productsManager;
    protected NotificationModule $notificationModule;
    protected BooleanOption $shortcodeRemovedNotifictionOption;
    public function __construct(ProductsManagerInterface $productsManager, NotificationModule $notificationModule, PluginMeta $pluginMeta, BooleanOption $shortcodeRemovedNotifictionOption)
    {
        $this->productsManager = $productsManager;
        $this->notificationModule = $notificationModule;
        $this->pluginMeta = $pluginMeta;
        $this->shortcodeRemovedNotifictionOption = $shortcodeRemovedNotifictionOption;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'shortcodeRemovedNotification');
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addFilter('init', $this, 'doNotRenderAnything');
    }
    public function doNotRenderAnything()
    {
        add_shortcode($this->productsManager->getShortcode(), array($this, 'emptyRenderer'));
    }
    public function emptyRenderer($attrs, $content = '')
    {
        return "";
    }
    public function shortcodeRemovedNotification() : void
    {
        $shortcodeRemovedDismissed = $this->handleDismissedBoolean($this->pluginMeta, $this->shortcodeRemovedNotifictionOption, self::DISMISS_SHORTCODE_REMOVED, \false);
        $proButton = array('url' => $this->pluginMeta->websiteUrl, 'text' => 'Get PRO', '_blank' => \true);
        $dismissButton = array('url' => $this->getDismissUrl($this->pluginMeta, self::DISMISS_SHORTCODE_REMOVED), 'text' => 'Dismiss this message', '_blank' => \false);
        if (!$shortcodeRemovedDismissed) {
            $this->notificationModule->notify('Shortcode functionality has been moved to PRO version', 'The shortcode feature is no longer available in the free version of the plugin. Please upgrade to the PRO version to continue using this feature and enjoy other benefits such as blocks, analytics and more.', 'warning', array($proButton, $dismissButton));
        }
    }
}
