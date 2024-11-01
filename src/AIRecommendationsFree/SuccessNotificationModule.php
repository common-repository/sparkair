<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendationsFree;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\DismissNotificationTrait;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class SuccessNotificationModule implements ModuleInterface
{
    use DismissNotificationTrait;
    public const DISMISS_SUCCESS_PARAM_NAME = 'dismiss_success';
    public const DISMISS_OUTDATED_PARAM_NAME = 'dismiss_outdated';
    protected NotificationModule $notificationModule;
    protected BooleanOption $sparkAirDismissedTrainingSuccessMessageOption;
    protected BooleanOption $sparkAirDismissedDataOutdatedMessageOption;
    protected PluginMeta $pluginMeta;
    public function __construct(NotificationModule $notificationModule, BooleanOption $sparkAirDismissedTrainingSuccessMessageOption, BooleanOption $sparkAirDismissedDataOutdatedMessageOption, PluginMeta $pluginMeta)
    {
        $this->notificationModule = $notificationModule;
        $this->sparkAirDismissedTrainingSuccessMessageOption = $sparkAirDismissedTrainingSuccessMessageOption;
        $this->sparkAirDismissedDataOutdatedMessageOption = $sparkAirDismissedDataOutdatedMessageOption;
        $this->pluginMeta = $pluginMeta;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'modelSuccessNotifications');
    }
    public function modelSuccessNotifications() : void
    {
        $trainSuccessDismissed = $this->handleDismissedBoolean($this->pluginMeta, $this->sparkAirDismissedTrainingSuccessMessageOption, self::DISMISS_SUCCESS_PARAM_NAME, \true);
        $dataOutdatedDismissed = $this->handleDismissedBoolean($this->pluginMeta, $this->sparkAirDismissedDataOutdatedMessageOption, self::DISMISS_OUTDATED_PARAM_NAME, \false);
        /** @var AirModelPostModel $airPost */
        $airPost = AirModelPostModel::loadActiveAirModel();
        $proButton = array('url' => $this->pluginMeta->websiteUrl, 'text' => 'Get PRO', '_blank' => \true);
        $dismissSuccessButton = array('url' => $this->getDismissUrl($this->pluginMeta, self::DISMISS_SUCCESS_PARAM_NAME), 'text' => 'Dismiss this message', '_blank' => \false);
        if (!$trainSuccessDismissed && $airPost && $airPost->isFinished()) {
            $this->notificationModule->notify('AI model has been deployed!', 'Your webshop has been enhanced with a custom model leveraging recent user data. For weekly updates of your AI model with the latest information, consider upgrading to our <strong>PRO plan</strong>.', 'success', array($proButton, $dismissSuccessButton));
        }
        $dismissOutdatedButton = array('url' => $this->getDismissUrl($this->pluginMeta, self::DISMISS_OUTDATED_PARAM_NAME), 'text' => 'Dismiss this message', '_blank' => \false);
        if ($airPost && \strtotime($airPost->get('trainingFinishedDateTime', \gmdate('Y-m-d H:i:s'))) < \strtotime('-1 week')) {
            if (!$dataOutdatedDismissed) {
                $this->notificationModule->notify('Your AI model is outdated...', 'The AI model used for recommendations is outdated. For weekly updates of your AI model with the latest information, consider upgrading to our <strong>PRO plan</strong>.', 'warning', array($proButton, $dismissOutdatedButton));
            }
        }
    }
}
