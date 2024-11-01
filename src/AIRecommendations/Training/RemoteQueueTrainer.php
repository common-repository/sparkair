<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models\AirModelPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Cache\CacheManager;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class RemoteQueueTrainer implements TrainerInterface, ModuleInterface
{
    const SCHEDULE_NAME_TIMEOUT = 'sparkair_timeout_training';
    protected TrainingDataRetrieverInterface $trainingDataRetriever;
    protected BooleanOption $sparkAirDataConsentOption;
    protected string $saasUrl;
    protected string $predictionEndpoint;
    protected ?OptionInterface $licenseKeyOption;
    protected PluginMeta $pluginMeta;
    protected BooleanOption $sparkAirDismissedTrainingSuccessMessageOption;
    protected string $callbackParam = 'sparkair-callback';
    protected string $callbackDryRunParam = 'sparkair-dry-run';
    protected string $cronHook;
    protected CacheManager $cacheManager;
    protected string $predictionsCacheKeyPrefix;
    public function __construct(TrainingDataRetrieverInterface $trainingDataRetriever, BooleanOption $sparkAirDataConsentOption, string $saasUrl, string $predictionEndpoint, PluginMeta $pluginMeta, BooleanOption $sparkAirDismissedTrainingSuccessMessageOption, CacheManager $cacheManager, string $predictionsCacheKeyPrefix, OptionInterface $licenseKeyOption = null)
    {
        $this->trainingDataRetriever = $trainingDataRetriever;
        $this->sparkAirDataConsentOption = $sparkAirDataConsentOption;
        $this->saasUrl = $saasUrl;
        $this->predictionEndpoint = $predictionEndpoint;
        $this->licenseKeyOption = $licenseKeyOption;
        $this->pluginMeta = $pluginMeta;
        $this->sparkAirDismissedTrainingSuccessMessageOption = $sparkAirDismissedTrainingSuccessMessageOption;
        $this->cronHook = $pluginMeta->prefix . 'timeout_training';
        $this->cacheManager = $cacheManager;
        $this->predictionsCacheKeyPrefix = $predictionsCacheKeyPrefix;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('admin_init', $this, 'testTimeout');
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('init', $this, 'scheduleEvent');
        $loader->addAction('init', $this, 'callbackWebhooks');
        $loader->addFilter('cron_schedules', $this, 'addCronScheduleEveryMinute');
        $loader->addAction($this->cronHook, $this, 'timeoutTraining');
    }
    public function train(AirModelPostModel $airPost, bool $activateAfterTraining = \false) : void
    {
        $airPost->resetStatusDateTimes();
        $airPost->initiateNow();
        if (!$this->sparkAirDataConsentOption->getValue(\false)) {
            $airPost->errorNow('Data consent not given.');
            return;
        }
        try {
            $userData = $this->trainingDataRetriever->retrieve();
            $predictionServiceBaseUrl = SPARK_DEV_MODE ? 'http://host.docker.internal:5002' : $this->saasUrl;
            $url = $predictionServiceBaseUrl . $this->predictionEndpoint;
            $callbackUrl = add_query_arg($this->callbackParam, $airPost->get('id'), get_site_url());
            $predictionData = \array_merge(array('userData' => $userData), \array_intersect_key($airPost->jsonSerialize(), \array_flip(array('features', 'ratingPerOrder', 'ratingCountOrder', 'ratingPerView', 'ratingCountView', 'tuneAutomatically'))), array('licenseKey' => empty($this->licenseKeyOption) ? null : $this->licenseKeyOption->getValue(), 'licenseUrl' => home_url(), 'slug' => $this->pluginMeta->slug, 'postId' => $airPost->get('id'), 'callbackUrl' => $callbackUrl, 'callbackDryRunUrl' => add_query_arg($this->callbackDryRunParam, 1, $callbackUrl)));
            $response = \wp_remote_post($url, array('body' => \wp_json_encode($predictionData), 'headers' => array('Content-Type' => 'application/json'), 'timeout' => 60, 'compress' => \true));
            if (\is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
            $responseCode = \wp_remote_retrieve_response_code($response);
            if ($responseCode === 500) {
                throw new \Exception('Unknown error: ' . $responseCode);
            }
            $body = \json_decode(\wp_remote_retrieve_body($response), \true);
            $responseData = $body['data'];
            $this->mergeRemoteDataIntoModel($airPost, $responseData);
        } catch (\Exception $e) {
            $airPost->errorNow($e->getMessage());
            throw $e;
        }
    }
    public function callbackWebhooks() : void
    {
        if (!isset($_GET[$this->callbackParam])) {
            return;
        }
        if (isset($_GET[$this->callbackDryRunParam])) {
            \wp_send_json(array('status' => 'ok'), 200);
            die;
        }
        $postId = \intval($_GET[$this->callbackParam]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $airPost = new AirModelPostModel();
        /** @var AirModelPostModel $airPost */
        $airPost = $airPost->load(\intval($postId));
        if (!$airPost) {
            return;
        }
        $json = \file_get_contents('php://input');
        $postData = \json_decode($json, \true);
        if (!$postData) {
            return;
        }
        $this->mergeRemoteDataIntoModel($airPost, $postData);
        $this->sparkAirDismissedTrainingSuccessMessageOption->setValue(\false);
        $this->cacheManager->clear($this->predictionsCacheKeyPrefix . $airPost->get('id'), \true);
        \wp_send_json(array('status' => 'ok'), 200);
        die;
    }
    private function mergeRemoteDataIntoModel(AirModelPostModel $airModel, $remoteData)
    {
        $this->setModelData($airModel, $remoteData, 'documentId');
        if ($remoteData['status'] === 'errored') {
            $this->setModelData($airModel, $remoteData, 'trainingErroredDateTime');
        } else {
            if ($remoteData['status'] === 'training') {
                $this->setModelData($airModel, $remoteData, 'trainingStartedDateTime');
            } else {
                if ($remoteData['status'] === 'finished') {
                    $this->setModelData($airModel, $remoteData, 'rmse');
                    $this->setModelData($airModel, $remoteData, 'usersCount');
                    $this->setModelData($airModel, $remoteData, 'itemsCount');
                    $this->setModelData($airModel, $remoteData, 'userIndexIdMap');
                    $this->setModelData($airModel, $remoteData, 'itemIndexIdMap');
                    $this->setModelData($airModel, $remoteData, 'sRoot');
                    $this->setModelData($airModel, $remoteData, 'itemMeans');
                    $this->setModelData($airModel, $remoteData, 'Ur');
                    $this->setModelData($airModel, $remoteData, 'Vr');
                    $this->setModelData($airModel, $remoteData, 'trainingFinishedDateTime');
                    $this->setModelData($airModel, $remoteData, 'trainingLastSuccessDateTime', 'trainingFinishedDateTime');
                    // Set used params
                    $this->setModelData($airModel, $remoteData, 'features');
                    $this->setModelData($airModel, $remoteData, 'ratingPerOrder');
                    $this->setModelData($airModel, $remoteData, 'ratingCountOrder');
                    $this->setModelData($airModel, $remoteData, 'ratingPerView');
                    $this->setModelData($airModel, $remoteData, 'ratingCountView');
                    $this->setModelData($airModel, $remoteData, 'tuneAutomatically');
                }
            }
        }
        $this->setModelData($airModel, $remoteData, 'trainingStatusMessage');
        $airModel->set('paramsHashTrained', $airModel->getParamsHash());
        $airModel->persist();
    }
    private function setModelData(AirModelPostModel $airModel, $remoteData, $key, $fromKey = null)
    {
        if (!$fromKey) {
            $fromKey = $key;
        }
        if (\array_key_exists($fromKey, $remoteData)) {
            $airModel->set($key, $remoteData[$fromKey]);
        }
    }
    public function timeoutTraining() : void
    {
        $airPosts = AirModelPostModel::loadTimedOutModels();
        foreach ($airPosts as $airPost) {
            /** @var AirModelPostModel $airPost */
            $airPost->errorNow('Training timed out.');
        }
    }
    public function scheduleEvent() : void
    {
        if (!\wp_next_scheduled($this->cronHook)) {
            \wp_schedule_event(\time(), self::SCHEDULE_NAME_TIMEOUT, $this->cronHook);
        }
    }
    public function addCronScheduleEveryMinute($schedules) : array
    {
        $schedules[self::SCHEDULE_NAME_TIMEOUT] = array('interval' => 60, 'display' => esc_html__('Every Minute', 'sparkair'));
        return $schedules;
    }
    public function testTimeout() : void
    {
        if (isset($_GET['sparkair-timeout'])) {
            $this->timeoutTraining();
        }
    }
}
