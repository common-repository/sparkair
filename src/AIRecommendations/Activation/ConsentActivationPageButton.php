<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Activation;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\DataConsentModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationPageButton;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class ConsentActivationPageButton extends ActivationPageButton implements ModuleInterface
{
    use DismissNotificationTrait;
    private PluginMeta $pluginMeta;
    public function __construct(PluginMeta $pluginMeta, BooleanOption $sparkAirDataConsentOption)
    {
        parent::__construct('Agree to use data');
        $this->pluginMeta = $pluginMeta;
        $this->hidden = $sparkAirDataConsentOption->getValue(\false);
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'setUrl');
    }
    public function setUrl() : void
    {
        $this->url = $this->getDismissUrl($this->pluginMeta, DataConsentModule::CONSENT_PARAM_NAME);
    }
}
