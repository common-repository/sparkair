<?php

namespace Sparkair\SparkPlugins\SparkWoo\CommonFree\MailList;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\StringOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class SubscribeToMailListModule implements ModuleInterface
{
    private NotificationModule $notificationModule;
    private BooleanOption $dismissedOption;
    private StringOption $installedTimestampOption;
    private PluginMeta $pluginMeta;
    public function __construct(NotificationModule $notificationModule, BooleanOption $dismissedOption, StringOption $installedTimestampOption, PluginMeta $pluginMeta)
    {
        $this->notificationModule = $notificationModule;
        $this->dismissedOption = $dismissedOption;
        $this->installedTimestampOption = $installedTimestampOption;
        $this->pluginMeta = $pluginMeta;
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
        if ($this->dismissedOption->getValue()) {
            return;
        }
        $timestamp = $this->installedTimestampOption->getValue('');
        if (empty($timestamp)) {
            $timestamp = \strtotime('now');
            $this->installedTimestampOption->setValue($timestamp);
        }
        $weekAgo = \strtotime('-1 week');
        if ($weekAgo < $timestamp) {
            return;
        }
        $firstName = '';
        $email = '';
        if (is_user_logged_in()) {
            $current_user = \wp_get_current_user();
            if (!empty($current_user->user_firstname)) {
                $firstName = $current_user->user_firstname;
            }
            if (!empty($current_user->user_email)) {
                $email = $current_user->user_email;
            }
        }
        $settingsUrl = menu_page_url(GlobalVariables::SPARKWOO_PREFIX . 'settings', \false) . '#/settings/' . $this->pluginMeta->slug;
        $this->notificationModule->notify('We have noticed that you have used <strong>' . $this->pluginMeta->name . '</strong> for a while now...', 'Subscribe to our newsletter to get notifications about new updates, best practices, and many more, just to give your sales an <strong>extra boost</strong>! Don’t worry, we hate SPAM as much as you do...
      <form target="_blank" method="post" action="https://www.sparkplugins.com/" class="flex flex-col md:space-x-2 md:flex-row md:space-y-0 space-y-2">
        <input class="block h-8 w-full md:w-64 rounded-md border-0 p-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-1 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" type="input" name="firstName" placeholder="First name" value="' . $firstName . '" />
        <input class="block h-8 w-full md:w-64 rounded-md border-0 p-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-1 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" type="input" name="email" placeholder="E-mail address" value="' . $email . '" />
        <button type="submit" class="cursor-pointer border-0 rounded-md bg-indigo-600 px-2.5 py-1 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Subscribe!</button>
        <input type="hidden" name="sparkPluginsSubscribeTo" value="' . $this->pluginMeta->slug . '" />
      </form>', 'info', array(array('url' => $settingsUrl, 'text' => 'Dismiss this notification in settings')));
    }
    public function dismissForever()
    {
        $this->dismissedOption->setValue(\true);
    }
}
