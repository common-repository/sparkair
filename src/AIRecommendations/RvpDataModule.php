<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations;

use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationHookInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\StylesScripts\ScriptsDataProviderInterface;
class RvpDataModule implements ActivationHookInterface, ModuleInterface, ScriptsDataProviderInterface
{
    use DismissNotificationTrait;
    public const DISMISS_PARAM_NAME = 'dismiss_rvp_data';
    protected BooleanOption $sparkAirDismissedRvpDataOption;
    protected PluginMeta $pluginMeta;
    protected PluginMetaCollection $pluginMetaCollection;
    protected NotificationModule $notificationModule;
    public function __construct(PluginMeta $pluginMeta, PluginMetaCollection $pluginMetaCollection, BooleanOption $sparkAirDismissedRvpDataOption, NotificationModule $notificationModule)
    {
        $this->pluginMeta = $pluginMeta;
        $this->pluginMetaCollection = $pluginMetaCollection;
        $this->sparkAirDismissedRvpDataOption = $sparkAirDismissedRvpDataOption;
        $this->notificationModule = $notificationModule;
    }
    public function run() : void
    {
        if ($this->sparkAirDismissedRvpDataOption->getValue(\false)) {
            return;
        }
        $this->sparkAirDismissedRvpDataOption->setValue(\false);
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'dismissRvpDataNotification');
        $loader->addAction('admin_init', $this, 'showRvpVersionNotification');
    }
    public function isRvpInstalled() : bool
    {
        $sparkRvp = $this->pluginMetaCollection->getItemBy('slug', 'sparkrvp');
        $sparkRvpPro = $this->pluginMetaCollection->getItemBy('slug', 'sparkrvp-pro');
        return $sparkRvp->isInstalled() || $sparkRvpPro->isInstalled();
    }
    public function isRvp120Installed() : bool
    {
        $sparkRvp = $this->pluginMetaCollection->getItemBy('slug', 'sparkrvp');
        $sparkRvpPro = $this->pluginMetaCollection->getItemBy('slug', 'sparkrvp-pro');
        $v = '1.2.0';
        return $sparkRvp->isInstalled() && $sparkRvp->version >= $v || $sparkRvpPro->isInstalled() && $sparkRvpPro->version >= $v;
    }
    public function dismissRvpDataNotification() : void
    {
        if ($this->isRvpInstalled()) {
            return;
        }
        if ($this->handleDismissedBoolean($this->pluginMeta, $this->sparkAirDismissedRvpDataOption, self::DISMISS_PARAM_NAME, \false)) {
            return;
        }
        $this->notificationModule->notify($this->pluginMeta->name . ' works better with SparkRVP', 'Enhance the performance of SparkAIR by leveraging a richer dataset. The information collected through <a class="font-bold" href="https://www.sparkplugins.com/sparkrvp" target="_blank">SparkRVP</a>, specifically the Recently Viewed Products data for each visitor, offers a broader scope for refining the model. This abundance of data also provides you with extra options for fine-tuning the model.', 'info', array(array('url' => 'https://www.sparkplugins.com/sparkrvp', 'text' => 'Get SparkRVP', '_blank' => \true), array('url' => $this->getDismissUrl($this->pluginMeta, self::DISMISS_PARAM_NAME), 'text' => 'Dismiss this message', '_blank' => \false)));
    }
    public function showRvpVersionNotification() : void
    {
        if (!$this->isRvpInstalled()) {
            return;
        }
        if ($this->isRvp120Installed()) {
            return;
        }
        $this->notificationModule->notify('SparkRVP is installed! But wrong version...', 'Please update SparkRVP to version 1.2.0 or higher to get the most out of SparkAIR.', 'info', array(array('url' => 'https://www.sparkplugins.com/sparkrvp', 'text' => 'Get SparkRVP', '_blank' => \true)));
    }
    public function getScriptData() : array
    {
        return array('rvpInstalled' => $this->isRvpInstalled(), 'rvpUrl' => $this->pluginMetaCollection->getItemBy('slug', 'sparkrvp')->websiteUrl);
    }
}
