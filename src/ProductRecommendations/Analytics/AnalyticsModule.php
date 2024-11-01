<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics;

use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationHookInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Admin\AdminPageTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\WPHelpers;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks\ProductPlacementHookInterface;
use WC;
class AnalyticsModule implements ModuleInterface, ActivationHookInterface, ApiInterface
{
    use AdminPageTrait;
    use ApiTrait;
    protected const ANALYTICS_PAGE_NAME = 'analytics';
    protected const SESSION_COOKIE = GlobalVariables::SPARKWOO_PREFIX . 'session';
    protected PluginMeta $pluginMeta;
    protected GlobalVariables $globalVariables;
    protected AnalyticsEventRepository $eventRepository;
    protected AnalyticsEventFactory $eventFactory;
    protected ApiInterface $api;
    protected string $adminMenuHomeName;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables, AnalyticsEventRepository $eventRepository, AnalyticsEventFactory $eventFactory, ApiInterface $api, string $adminMenuHomeName)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
        $this->adminMenuHomeName = $adminMenuHomeName;
        $this->api = $api;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction($this->pluginMeta->prefix . 'product_recommendations_analytics_event', $this, 'recordEvent', 10, 5);
        $loader->addFilter('init', $this, 'noIndexWhenHavingEvent');
        $loader->addAction('wp', $this, 'recordClickEventsGet');
        $loader->addAction('wc_ajax_add_to_cart', $this, 'recordClickEventsWcAjax');
        $loader->addAction('init', $this, 'startSession');
        $loader->addAction('woocommerce_order_status_on-hold', $this, 'recordConversionsEvents');
        $loader->addAction('woocommerce_order_status_pending', $this, 'recordConversionsEvents');
        $loader->addAction('woocommerce_order_status_processing', $this, 'recordConversionsEvents');
        $loader->addAction('woocommerce_order_status_completed', $this, 'recordConversionsEvents');
        $loader->addAction('rest_api_init', $this, 'registerRoutes');
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('plugins_loaded', $this, 'databaseInstall');
        $loader->addAction('admin_menu', $this, 'addAdminPage');
    }
    public function getNameSpace()
    {
        return $this->api->getNameSpace() . '/analytics';
    }
    public function registerRoutes()
    {
        $namespace = $this->getNamespace();
        register_rest_route($namespace, '/product-renders', array(array('methods' => \WP_REST_Server::DELETABLE, 'callback' => array($this, 'deleteProductRenders'), 'permission_callback' => array($this, 'adminPermissionCheck'), 'args' => array())));
    }
    public function run() : void
    {
        $this->databaseInstall();
    }
    public function databaseInstall() : void
    {
        $tableName = AnalyticsEventModel::getTable();
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$tableName} (\n      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n      timestamp timestamp DEFAULT CURRENT_TIMESTAMP,\n      event varchar(255) NOT NULL,\n      sessionId varchar(255) DEFAULT NULL,\n      productId bigint(20) unsigned NOT NULL,\n      plugin varchar(255) NOT NULL,\n      placementHook varchar(255) DEFAULT NULL,\n      productsManager varchar(255) DEFAULT NULL,\n      recommendationId bigint(20) unsigned DEFAULT NULL,\n      orderId bigint(20) unsigned DEFAULT NULL,\n      orderItemId bigint(20) unsigned DEFAULT NULL,\n      userId bigint(20) unsigned DEFAULT NULL,\n      PRIMARY KEY (id),\n      KEY timestamp (timestamp),\n      KEY event (event),\n      KEY sessionId (sessionId),\n      KEY plugin (plugin),\n      KEY placementHook (placementHook),\n      KEY productsManager (productsManager),\n      KEY userId (userId)\n    ) {$charset_collate};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    private function getAnalyticsPageUrl() : string
    {
        return GlobalVariables::SPARKWOO_PREFIX . $this::ANALYTICS_PAGE_NAME;
    }
    public function addAdminPage()
    {
        $analyticsPageName = $this->getAnalyticsPageUrl();
        if (!WPHelpers::hasSubMenuPage($this->adminMenuHomeName, $analyticsPageName)) {
            add_submenu_page($this->adminMenuHomeName, 'Analytics', 'Analytics', 'manage_options', $analyticsPageName, array($this, 'loadAdminPageContent'));
        }
    }
    public function recordEvent(string $event, ProductPlacementHookInterface $hook, PluginMeta $pluginMeta, ProductRecommendationPostModel $productRecommendation, array $products)
    {
        if ($pluginMeta->slug !== $this->pluginMeta->slug) {
            return;
        }
        foreach ($products as $product) {
            $analyticsEvent = $this->eventFactory->create(array('event' => $event, 'productId' => $product->ID, 'placementHook' => $hook->getKey(), 'productsManager' => $hook->getProductsManager()->getSlug(), 'plugin' => $this->pluginMeta->slug, 'recommendationId' => $productRecommendation->get('id')));
            $this->eventRepository->save($analyticsEvent);
        }
    }
    public function recordClickEventsGet()
    {
        if (!$this->hasAnalyticsEventInGet() || 'product' != get_post_type() || !is_singular()) {
            return;
        }
        if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_text_field(\wp_unslash($_GET['_wpnonce'])), $this->getVariableName())) {
            return;
        }
        $this->recordClickForEncodedEventData(sanitize_text_field(\wp_unslash($_GET[$this->getVariableName()])), get_the_ID());
    }
    public function recordClickEventsWcAjax()
    {
        if (!$this->hasAnalyticsEventInPost() || !isset($_POST['product_id']) || \intval($_POST['product_id']) != $_POST['product_id']) {
            return;
        }
        $nonceKey = $this->getVariableName() . '_nonce';
        if (!isset($_POST[$nonceKey]) || !\wp_verify_nonce(sanitize_text_field(\wp_unslash($_POST[$nonceKey])), $this->getVariableName())) {
            return;
        }
        $this->recordClickForEncodedEventData(sanitize_text_field(\wp_unslash($_POST[$this->getVariableName()])), \intval($_POST['product_id']));
    }
    public function recordClickForEncodedEventData($eventData, $productId)
    {
        $vars = \json_decode(\base64_decode($eventData), \true);
        if (!$vars) {
            return;
        }
        $arrayKeys = array('plugin', 'placementHook', 'productsManager', 'recommendationId');
        foreach ($arrayKeys as $key) {
            if (!\array_key_exists($key, $vars)) {
                return;
            }
        }
        if ($vars['plugin'] != $this->pluginMeta->slug) {
            return;
        }
        $this->eventRepository->save($this->eventFactory->create(\array_merge(array('event' => AnalyticsEventModel::CLICK, 'productId' => $productId), $vars)));
    }
    public function noIndexWhenHavingEvent()
    {
        if ($this->hasAnalyticsEventInGet()) {
            add_filter('wp_robots', function ($robots) {
                $robots['noindex'] = \true;
                return $robots;
            });
        }
    }
    private function hasAnalyticsEventInGet()
    {
        return isset($_GET[$this->getVariableName()]);
    }
    private function hasAnalyticsEventInPost()
    {
        return isset($_POST[$this->getVariableName()]);
    }
    private function getVariableName()
    {
        return $this->pluginMeta->prefix . 'analytics_event';
    }
    public function recordConversionsEvents($orderId)
    {
        $order = new \WC_Order($orderId);
        $sessionId = $this::getSessionHash();
        $userId = $order->get_user_id();
        try {
            foreach ($order->get_items() as $orderItemId => $orderItem) {
                $alreadyConvertedEvents = $this->eventRepository->getConvertedEventCount($orderId, $orderItemId);
                if ($alreadyConvertedEvents > 0) {
                    continue;
                }
                $productId = $orderItem->get_product_id();
                /** @var AnalyticsEventModel $analyticsClickEvent */
                $analyticsClickEvent = $this->eventRepository->getConvertableClickEvent($this->pluginMeta->slug, $productId, $userId, $sessionId);
                if (!$analyticsClickEvent) {
                    continue;
                }
                $newEvent = array('event' => AnalyticsEventModel::CONVERSION, 'productId' => $productId, 'plugin' => $analyticsClickEvent->plugin, 'placementHook' => $analyticsClickEvent->placementHook, 'productsManager' => $analyticsClickEvent->productsManager, 'recommendationId' => $analyticsClickEvent->recommendationId, 'userId' => $userId, 'orderId' => $orderId, 'orderItemId' => $orderItemId);
                if (!empty($analyticsClickEvent->sessionId) && empty($sessionId)) {
                    $newEvent['sessionId'] = $analyticsClickEvent->sessionId;
                }
                $this->eventRepository->save($this->eventFactory->create($newEvent));
            }
        } catch (\Exception $e) {
            // ignore
            \error_log('Error logging conversion: ' . $e->getMessage());
        }
    }
    public function startSession()
    {
        if (self::skipSession()) {
            return;
        }
        if (empty($this::getSessionHash())) {
            $value = \wp_generate_password(12, \false);
        } else {
            $value = $this::getSessionHash();
        }
        \setcookie($this::SESSION_COOKIE, $value, \time() + 60 * 60 * 24 * 7, '/');
    }
    public static function getSessionHash()
    {
        if (self::skipSession()) {
            return null;
        }
        return isset($_COOKIE[self::SESSION_COOKIE]) ? sanitize_text_field(\wp_unslash($_COOKIE[self::SESSION_COOKIE])) : '';
    }
    /**
     * Skip when:
     * - is in admin
     * - is a json request
     * - doing cron
     */
    public static function skipSession()
    {
        return is_admin() || \strpos(isset($_SERVER['REQUEST_URI']) ? esc_url_raw(\wp_unslash($_SERVER['REQUEST_URI'])) : '', 'wp-json') || \defined('DOING_CRON');
    }
    public function deleteProductRenders()
    {
        $this->eventRepository->deleteProductRenders($this->pluginMeta->slug);
        return rest_ensure_response(array('message' => 'Product renders deleted'));
    }
}
