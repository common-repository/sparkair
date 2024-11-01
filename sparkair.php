<?php



/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sparkplugins.com
 *
 * @wordpress-plugin
 * Plugin Name:           SparkAIR - Recommendations for WooCommerce driven by AI!
 * Plugin URI:            https://www.sparkplugins.com/sparkair
 * Description:           Personalized Product Recommendations for WooCommerce Driven by AI!
 * Version:               1.0.6
 * Author:                SparkPlugins
 * Author URI:            https://sparkplugins.com/
 * License:               GPL-2.0+
 * License URI:           http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:           sparkair
 * Domain Path:           /languages
 * Requires at least:     6.4
 * Requires PHP:          7.4
 * Requires Plugins:      woocommerce
 * WC requires at least:  8.0
 * WC tested up to:       8.8
 */
if (!\defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';
use Sparkair\Symfony\Component\Config\ConfigCache;
use Sparkair\Symfony\Component\Config\FileLocator;
use Sparkair\Symfony\Component\DependencyInjection\ContainerBuilder;
use Sparkair\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Sparkair\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
// If this file is called directly, abort.
if (!\defined('WPINC')) {
    die;
}
if (!\function_exists('Sparkair\\Sparkair_getSparkPluginsContainer')) {
    function Sparkair_getSparkPluginsContainer()
    {
        $file = __DIR__ . '/cache/container.php';
        $containerConfigCache = new ConfigCache($file, \SPARK_DEV_MODE);
        if (!$containerConfigCache->isFresh()) {
            $container = new ContainerBuilder();
            $fileLocator = new FileLocator(__DIR__);
            $loader = new YamlFileLoader($container, $fileLocator);
            $loader->load('config/services-sparkair.yaml');
            $container->compile();
            $dumper = new PhpDumper($container);
            $containerConfigCache->write($dumper->dump(['class' => 'Sparkair_Container']), $container->getResources());
        }
        require_once $file;
        return new Sparkair_Container();
    }
}
if (!\function_exists('Sparkair\\Sparkair_activateSparkPlugin')) {
    function Sparkair_activateSparkPlugin()
    {
        $container = Sparkair_getSparkPluginsContainer();
        $activator = $container->get('activator');
        $activator->activate();
    }
}
if (!\function_exists('Sparkair\\Sparkair_deactivateSparkPlugin')) {
    function Sparkair_deactivateSparkPlugin()
    {
        $container = Sparkair_getSparkPluginsContainer();
        $deactivator = $container->get('deactivator');
        $deactivator->deactivate();
    }
}
register_activation_hook(__FILE__, 'Sparkair_activateSparkPlugin');
register_deactivation_hook(__FILE__, 'Sparkair_deactivateSparkPlugin');
if (!\defined('SPARK_DEV_MODE')) {
    \define('SPARK_DEV_MODE', \false);
}
if (!\defined('SPARK_DEV_HOST_ADMIN')) {
    \define('SPARK_DEV_HOST_ADMIN', \false);
}
if (!\defined('SPARK_DEV_HOST_PUBLIC')) {
    \define('SPARK_DEV_HOST_PUBLIC', \false);
}
if (!\function_exists('Sparkair\\Sparkair_runSparkPlugin')) {
    function Sparkair_runSparkPlugin()
    {
        $container = Sparkair_getSparkPluginsContainer();
        $globalVariables = $container->get('global-variables');
        $globalVariables->setPluginDir(\dirname(plugin_basename(__FILE__)));
        $globalVariables->setPluginDirPath(plugin_dir_path(__FILE__));
        $globalVariables->setPluginUrl(plugin_dir_url(__FILE__));
        $globalVariables->setPluginFilePath(__FILE__);
        $plugin = $container->get('plugin');
        $plugin->run();
    }
}
Sparkair_runSparkPlugin();
