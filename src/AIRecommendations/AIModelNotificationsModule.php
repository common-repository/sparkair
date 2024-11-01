<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Notifications\NotificationModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class AIModelNotificationsModule implements ModuleInterface
{
    protected NotificationModule $notificationModule;
    protected BooleanOption $sparkAirDataConsentOption;
    protected PluginMeta $pluginMeta;
    public function __construct(NotificationModule $notificationModule, BooleanOption $sparkAirDataConsentOption, PluginMeta $pluginMeta)
    {
        $this->notificationModule = $notificationModule;
        $this->sparkAirDataConsentOption = $sparkAirDataConsentOption;
        $this->pluginMeta = $pluginMeta;
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'modelNotifications');
    }
    public function modelNotifications() : void
    {
        $consent = $this->sparkAirDataConsentOption->getValue(\false);
        $query = new \WP_Query(array('post_type' => AirModelPostModel::postType(), 'post_status' => 'any', 'numberposts' => -1));
        $posts = $query->get_posts();
        $aiToolsUrl = esc_url(add_query_arg('page', GlobalVariables::SPARKWOO_PREFIX . 'ai-tools', get_admin_url() . 'admin.php'));
        $aiToolsA = '<strong><a href="' . $aiToolsUrl . '">' . __('AI Tools', 'sparkair') . '</a></strong>';
        if (!$query->found_posts) {
            $this->notificationModule->notify('No model found', 'Please create a model in the ' . $aiToolsA . ' section.', 'info');
            return;
        }
        $hasActiveModel = \false;
        $untrainedActive = \false;
        $currentlyTrainingFirstTime = \false;
        $erroredActive = \false;
        $errorMessage = '';
        foreach ($posts as $post) {
            $airPost = new AirModelPostModel();
            /** @var AirModelPostModel $airPost */
            $airPost = $airPost->load($post->ID);
            $untrainedActive = $airPost->isActive() && !$airPost->isTrained();
            $hasActiveModel = $hasActiveModel || $airPost->isActive();
            if (!$airPost->isTrained() && $airPost->isTraining() && $airPost->isActive()) {
                $currentlyTrainingFirstTime = \true;
            }
            if ($airPost->isErrored() && $airPost->isActive()) {
                $errorMessage = $airPost->get('trainingStatusMessage', 'unknown');
                $erroredActive = \true;
            }
        }
        if (!$hasActiveModel) {
            $this->notificationModule->notify('No active model found', 'There is currently no active model for SparkAIR. Please go to the ' . $aiToolsA . ' and check it out, or contact SparkPlugins support.', 'warning');
            return;
        }
        if ($erroredActive && $consent) {
            $this->notificationModule->notify('Something went wrong', 'The preparation of the recommendation engine gave the following error: <br><br><em>' . $errorMessage . '</em><br><br>Please go to the ' . $aiToolsA . ' to investigate the error that occured and to try training again, or contact SparkPlugins support.', 'warning');
            return;
        }
        if ($untrainedActive && $currentlyTrainingFirstTime && $consent) {
            $this->notificationModule->notify('SparkAIR is preparing recommendations', 'The recommendation engine is preparing itself. This may take some time. Please stay tuned.', 'info');
            return;
        }
        if ($untrainedActive && $consent) {
            $this->notificationModule->notify('SparkAIR recommendation engine not ready', 'The preparation of the recommendation engine did not start. Please give your consent, check your cronjobs, go to the ' . $aiToolsA . ' to try it again, or contact SparkPlugins support.', 'warning');
            return;
        }
    }
}
