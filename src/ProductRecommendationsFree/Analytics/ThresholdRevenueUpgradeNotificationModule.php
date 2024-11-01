<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendationsFree\Analytics;

use Sparkair\SparkPlugins\SparkWoo\Common\Database\Database;
use Sparkair\SparkPlugins\SparkWoo\Common\Database\OrderItem;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\StringOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics\AnalyticsEvent;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics\AnalyticsEventRepository;
class ThresholdRevenueUpgradeNotificationModule implements ModuleInterface
{
    use DismissNotificationTrait;
    private PluginMeta $pluginMeta;
    private NotificationModule $notificationModule;
    private StringOption $dismissedOptionMonthly;
    private AnalyticsEventRepository $eventRepository;
    private int $revenueThreshold;
    private const DISMISS_PARAM_KEY = 'dismiss-threshold-revenue-upgrade-notification';
    public function __construct(PluginMeta $pluginMeta, NotificationModule $notificationModule, StringOption $dismissedOptionMonthly, AnalyticsEventRepository $eventRepository, int $revenueThreshold)
    {
        $this->pluginMeta = $pluginMeta;
        $this->notificationModule = $notificationModule;
        $this->dismissedOptionMonthly = $dismissedOptionMonthly;
        $this->eventRepository = $eventRepository;
        $this->revenueThreshold = $revenueThreshold;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'addNotification');
    }
    public function addNotification()
    {
        if ($this->handleDismissedMonthly($this->pluginMeta, $this->dismissedOptionMonthly, self::DISMISS_PARAM_KEY)) {
            return;
        }
        $revenue = $this->eventRepository->calculateTotalRevenueForProductsManager($this->pluginMeta->extra['productsManager']['slug']);
        if ($revenue < $this->revenueThreshold) {
            $this->dismissedOptionMonthly->setValue(\gmdate('Y-m-d'));
            return;
        }
        $dismissUrl = $this->getDismissUrl($this->pluginMeta, self::DISMISS_PARAM_KEY);
        $url = add_query_arg(array('utm_source' => 'plugin', 'utm_medium' => 'notification', 'utm_id' => $this->pluginMeta->slug, 'utm_campaign' => 'threshold-revenue-upgrade'), $this->pluginMeta->websiteUrl);
        $this->notificationModule->notify('Wow, you have already earned ' . \wp_strip_all_tags(\wc_price($revenue)) . ' using ' . $this->pluginMeta->name . ' 🎉', 'Want to boost your revenue even more? Upgrade to PRO and access exclusive features like real-time advanced Analytics to monitor your earnings instantly. Plus, enjoy a suite of premium benefits designed to enhance your ' . $this->pluginMeta->name . ' experience. Don\'t miss out — upgrade now and maximize your success!', 'info', array(array('url' => $url, 'text' => 'Get ' . $this->pluginMeta->name . ' PRO now! 🚀', '_blank' => \true), array('url' => $dismissUrl, 'text' => 'Dismiss for some time', '_blank' => \false)));
    }
}
