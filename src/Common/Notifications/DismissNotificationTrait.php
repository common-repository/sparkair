<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Notifications;

use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\StringOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
trait DismissNotificationTrait
{
    public function handleDismissedBoolean(PluginMeta $pluginMeta, BooleanOption $dismissedOption, string $getParamSuffix, bool $default = \false) : bool
    {
        if ($dismissedOption->getValue($default)) {
            return \true;
        }
        if ($this->validateAndSet($pluginMeta, $dismissedOption, $getParamSuffix, \true)) {
            return \true;
        }
        return \false;
    }
    public function handleDismissedMonthly(PluginMeta $pluginMeta, StringOption $dismissedOption, string $getParamSuffix) : bool
    {
        $lastDismissed = $dismissedOption->getValue(\gmdate('Y-m-d', \strtotime('-2 month')));
        if ($lastDismissed > \gmdate('Y-m-d', \strtotime('-1 month'))) {
            return \true;
        }
        if ($this->validateAndSet($pluginMeta, $dismissedOption, $getParamSuffix, \gmdate('Y-m-d'))) {
            return \true;
        }
        return \false;
    }
    public function getDismissUrl(PluginMeta $pluginMeta, string $getParamSuffix) : string
    {
        $getParamKey = $pluginMeta->prefix . $getParamSuffix;
        return add_query_arg(array('_wpnonce' => \wp_create_nonce($getParamKey), $getParamKey => 1));
    }
    private function validateAndSet(PluginMeta $pluginMeta, OptionInterface $dismissedOption, string $getParamSuffix, $value) : bool
    {
        $getParamKey = $pluginMeta->prefix . $getParamSuffix;
        if (\array_key_exists($getParamKey, $_GET) && $_GET[$getParamKey] === '1' && $this->verifyNonce($getParamKey)) {
            $dismissedOption->setValue($value);
            $this->redirectWithoutParams($getParamKey);
            return \true;
        }
        return \false;
    }
    private function redirectWithoutParams(string $getParamKey)
    {
        \wp_redirect(remove_query_arg(array('_wpnonce', $getParamKey)));
        exit;
    }
    private function verifyNonce($getParamKey)
    {
        if (!\array_key_exists('_wpnonce', $_REQUEST)) {
            return \false;
        }
        $nonce = sanitize_text_field(\wp_unslash($_REQUEST['_wpnonce']));
        return \wp_verify_nonce($nonce, $getParamKey);
    }
}
