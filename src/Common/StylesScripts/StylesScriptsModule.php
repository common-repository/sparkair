<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\StylesScripts;

use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationPageContent;
use Sparkair\SparkPlugins\SparkWoo\Common\Api\ApiInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionsCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
use Sparkair\SparkPlugins\SparkWoo\StylesScripts\ScriptsDataProviderInterface;
class StylesScriptsModule implements ModuleInterface
{
    const PUBLIC_HANDLE_PREFIX = 'public_';
    const ADMIN_HANDLE_PREFIX = 'admin_';
    private PluginMeta $pluginMeta;
    private PluginMetaCollection $pluginMetaCollection;
    private GlobalVariables $globalVariables;
    protected ApiInterface $api;
    protected OptionsCollection $options;
    protected ActivationPageContent $activationPageContent;
    protected array $moduleScriptHandles = array();
    /**
     * @var ScriptsDataProviderInterface[] $dataProviders
     */
    protected array $dataProviders = array();
    public function __construct(PluginMeta $pluginMeta, PluginMetaCollection $pluginMetaCollection, GlobalVariables $globalVariables, ApiInterface $api, OptionsCollection $options, ActivationPageContent $activationPageContent, iterable $dataProviders)
    {
        $this->pluginMeta = $pluginMeta;
        $this->pluginMetaCollection = $pluginMetaCollection;
        $this->globalVariables = $globalVariables;
        $this->api = $api;
        $this->options = $options;
        $this->activationPageContent = $activationPageContent;
        $this->dataProviders = \iterator_to_array($dataProviders);
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('init', $this, 'registerAdminStyles');
        $loader->addAction('init', $this, 'registerAdminScripts');
        $loader->addFilter('wp_script_attributes', $this, 'addTypeModuleAttribute', 10, 1);
        if ($this->defaultlyEnqueueScripts()) {
            $loader->addAction('init', $this, 'enqueueStylesForAdmin');
            $loader->addAction('init', $this, 'enqueueScriptsForAdmin');
        }
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction(GlobalVariables::SPARKWOO_PREFIX . 'product_recommendations_enqueue_public', $this, 'enqueuePublicScriptAndStyles');
    }
    public static function getAdminPrefix()
    {
        return GlobalVariables::SPARKWOO_PREFIX . self::ADMIN_HANDLE_PREFIX;
    }
    public static function getPublicPrefix()
    {
        return GlobalVariables::SPARKWOO_PREFIX . self::PUBLIC_HANDLE_PREFIX;
    }
    public function enqueuePublicScriptAndStyles() : void
    {
        $this->registerModuleStyle($this::getPublicPrefix(), 'style', 'app-public/dist/sparkwoo.css');
        $this->registerModuleScript($this::getPublicPrefix(), 'app', 'app-public/dist/sparkwoo.js', 'src/sparkwoo.ts', SPARK_DEV_HOST_PUBLIC);
        add_filter('wp_script_attributes', array($this, 'addTypeModuleAttribute'), 10, 1);
    }
    /**
     * Register the stylesheets for the admin area.
     */
    public function registerAdminStyles()
    {
        $this->registerModuleStyle($this::getAdminPrefix(), 'style', 'app-admin/dist/sparkwoo.css', \false);
    }
    /**
     * Register the JavaScript for the admin area.
     */
    public function registerAdminScripts()
    {
        $this->registerModuleScript(self::getAdminPrefix(), 'app', 'app-admin/dist/sparkwoo.js', 'src/sparkwoo.ts', SPARK_DEV_HOST_ADMIN, \false);
        $jsConstName = 'WP_SPARK_PLUGINS_META';
        $attrs = array();
        if (isset($_GET['page'])) {
            $pageName = \explode('_', sanitize_key($_GET['page']));
            $attrs['page'] = \count($pageName) == 2 ? $pageName[1] : 'home';
        }
        $scriptNameMetaData = $this::getMetaDataScriptHandle();
        if (!\wp_script_is($scriptNameMetaData, 'enqueued') && !\wp_script_is($scriptNameMetaData, 'registered')) {
            \wp_register_script($scriptNameMetaData, '', array(), SPARK_DEV_MODE ? \wp_rand(10, 1000000) : $this->pluginMeta->slug . '-' . $this->pluginMeta->version, \true);
            \wp_localize_script($scriptNameMetaData, $jsConstName, \array_merge(array(
                // 'nonce_verify' => wp_verify_nonce($_REQUEST['X-WP-Nonce'], 'wp_rest'),
                'nonce' => \wp_create_nonce('wp_rest'),
                'imagePrefix' => $this->globalVariables->getPluginUrl() . '/assets/images',
                'env' => SPARK_DEV_MODE ? 'dev' : 'prod',
                'allSparkPlugins' => $this->pluginMetaCollection,
                'currentPluginSlug' => isset($_GET['spwoo_plugin_slug']) ? sanitize_key($_GET['spwoo_plugin_slug']) : null,
                'currencySymbol' => \function_exists('get_woocommerce_currency_symbol') ? \get_woocommerce_currency_symbol() : '€',
            ), $attrs));
            \wp_add_inline_script($scriptNameMetaData, '
        var WP_SPARK_PLUGINS_META = WP_SPARK_PLUGINS_META || {};
        var WP_SPARK_PLUGINS_DATA =  WP_SPARK_PLUGINS_DATA || {};

        function setPluginData(pluginName, data) {
          WP_SPARK_PLUGINS_DATA[pluginName] = data;
        }
      ', 'before');
        }
        \wp_add_inline_script($scriptNameMetaData, \sprintf('setPluginData("%s", %s);', esc_js($this->pluginMeta->slug), \wp_json_encode(\array_merge(array('meta' => $this->pluginMeta, 'apiUrl' => esc_url_raw(rest_url()) . $this->api->getNameSpace(), 'options' => $this->options, 'activationPageContent' => $this->activationPageContent), ...\array_map(function ($d) {
            return $d->getScriptData();
        }, $this->dataProviders)))), 'before');
    }
    public static function getMetaDataScriptHandle()
    {
        return self::getAdminPrefix() . 'meta_data';
    }
    public static function enqueueStylesForAdmin() : void
    {
        \wp_enqueue_style(self::getAdminPrefix() . 'style');
        if (SPARK_DEV_MODE) {
            self::enqueueScriptsForAdmin();
        }
    }
    public static function enqueueScriptsForAdmin() : void
    {
        \wp_enqueue_script(self::getAdminPrefix() . 'app');
        \wp_enqueue_script(self::getMetaDataScriptHandle());
    }
    private function registerModuleScript($prefix, $name, $distFile, $devFile, $devHost, $enqueue = \true)
    {
        $scriptHandle = $prefix . $name;
        $viteScriptHandle = $prefix . 'vite';
        if (!SPARK_DEV_MODE) {
            // App for prod.
            \wp_register_script($scriptHandle, $this->globalVariables->getPluginUrl() . '/' . $distFile, array(), $this->pluginMeta->slug . '-' . $this->pluginMeta->version, \true);
        } else {
            // App vite for dev.
            \wp_register_script($viteScriptHandle, $devHost . '/@vite/client', array(), \wp_rand(10, 1000000), \true);
            \wp_enqueue_script($viteScriptHandle);
            // App for dev.
            \wp_register_script($scriptHandle, $devHost . '/' . $devFile, array(), \wp_rand(10, 1000000), \true);
        }
        if ($enqueue) {
            \wp_enqueue_script($scriptHandle);
        }
        $this->moduleScriptHandles[] = $scriptHandle;
        $this->moduleScriptHandles[] = $viteScriptHandle;
    }
    private function registerModuleStyle($prefix, $name, $distFile, $enqueue = \true)
    {
        if (!SPARK_DEV_MODE) {
            \wp_register_style($prefix . $name, $this->globalVariables->getPluginUrl() . '/' . $distFile, array(), $this->pluginMeta->slug . '-' . $this->pluginMeta->version, 'all');
            if ($enqueue) {
                \wp_enqueue_style($prefix . $name);
            }
        }
    }
    public function addTypeModuleAttribute($attributes)
    {
        $mappedHandles = \array_map(function ($handle) {
            return $handle . '-js';
        }, $this->moduleScriptHandles);
        if (isset($attributes['id']) && (\in_array($attributes['id'], $mappedHandles) || \in_array($attributes['id'], $this->moduleScriptHandles))) {
            $attributes['type'] = 'module';
        }
        return $attributes;
    }
    public function defaultlyEnqueueScripts() : bool
    {
        return is_admin() && \str_contains(esc_url_raw(\wp_unslash($_SERVER['REQUEST_URI'])), 'plugins.php');
    }
}
