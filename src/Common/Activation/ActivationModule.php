<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

use Sparkair\SparkPlugins\SparkWoo\Common\Admin\AdminPageTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\WPHelpers;
class ActivationModule implements ModuleInterface
{
    use AdminPageTrait;
    const ACTIVATION_PAGE_NAME = 'welcome';
    private PluginMeta $pluginMeta;
    private GlobalVariables $globalVariables;
    private string $firstTimePluginUsageOption;
    private string $adminMenuHomeName;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables, string $firstTimePluginUsageOption, string $adminMenuHomeName)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
        $this->firstTimePluginUsageOption = $firstTimePluginUsageOption;
        $this->adminMenuHomeName = $adminMenuHomeName;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'pluginActivate');
        $loader->addAction('admin_menu', $this, 'addAdminPage');
    }
    public function pluginActivate() : void
    {
        if (get_option($this->firstTimePluginUsageOption, \false)) {
            delete_option($this->firstTimePluginUsageOption);
            if (!isset($_GET['activate-multi'])) {
                \wp_redirect(add_query_arg(array('page' => $this->getActivatorPageUrl(), 'spwoo_plugin_slug' => $this->pluginMeta->slug), admin_url('admin.php')));
            }
        }
    }
    public function getActivatorPageUrl() : string
    {
        return GlobalVariables::SPARKWOO_PREFIX . $this::ACTIVATION_PAGE_NAME;
    }
    public function addAdminPage()
    {
        $welcomePageName = $this->getActivatorPageUrl();
        if (!WPHelpers::hasSubMenuPage($this->adminMenuHomeName, $welcomePageName)) {
            add_submenu_page(GlobalVariables::SPARKWOO_PREFIX . 'do-not-show', 'Thank you for choosing a Spark Plugin!', 'Thank you', 'manage_options', $welcomePageName, array($this, 'loadAdminPageContent'));
        }
    }
}
