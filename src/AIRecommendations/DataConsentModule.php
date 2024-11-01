<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations;

use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationHookInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class DataConsentModule implements ActivationHookInterface, ModuleInterface
{
    use DismissNotificationTrait;
    public const CONSENT_PARAM_NAME = 'data_consent';
    protected BooleanOption $sparkAirDataConsentOption;
    protected PluginMeta $pluginMeta;
    protected NotificationModule $notificationModule;
    public function __construct(PluginMeta $pluginMeta, BooleanOption $sparkAirDataConsentOption, NotificationModule $notificationModule)
    {
        $this->pluginMeta = $pluginMeta;
        $this->sparkAirDataConsentOption = $sparkAirDataConsentOption;
        $this->notificationModule = $notificationModule;
    }
    public function run() : void
    {
        if ($this->sparkAirDataConsentOption->getValue(\false)) {
            return;
        }
        $this->sparkAirDataConsentOption->setValue(\false);
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'consentNotification');
    }
    public function consentNotification() : void
    {
        if ($this->handleDismissedBoolean($this->pluginMeta, $this->sparkAirDataConsentOption, self::CONSENT_PARAM_NAME)) {
            return;
        }
        $consentUrl = $this->getDismissUrl($this->pluginMeta, self::CONSENT_PARAM_NAME);
        $this->notificationModule->notify($this->pluginMeta->name . ' plugin needs a consent', 'We are utilizing anonymous shopping data to build a model. Kindly grant consent to <a href="https://www.sparkplugins.com/sparkair/terms" target="_blank">our terms</a> to use this plugin.', 'info', array(array('url' => $consentUrl, 'text' => 'I agree to use my data', '_blank' => \false)));
    }
}
