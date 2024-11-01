<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations;

use Sparkair\SparkPlugins\SparkWoo\Common\Admin\AdminPageTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\WPHelpers;
class AIToolsModule implements ModuleInterface
{
    use AdminPageTrait;
    const ADMIN_PAGE_NAME = 'ai-tools';
    private string $adminMenuHomeName;
    protected GlobalVariables $globalVariables;
    public function __construct(string $adminMenuHomeName, GlobalVariables $globalVariables)
    {
        $this->adminMenuHomeName = $adminMenuHomeName;
        $this->globalVariables = $globalVariables;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_menu', $this, 'addAdminPage');
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function getAdminPageUrl() : string
    {
        return GlobalVariables::SPARKWOO_PREFIX . $this::ADMIN_PAGE_NAME;
    }
    public function addAdminPage()
    {
        $toolsPageName = $this->getAdminPageUrl();
        if (!WPHelpers::hasSubMenuPage($this->adminMenuHomeName, $toolsPageName)) {
            add_submenu_page(GlobalVariables::SPARKWOO_PREFIX . 'do-not-show', 'AI Tools', 'AI Tools', 'manage_options', $toolsPageName, array($this, 'loadAdminPageContent'));
        }
    }
}
