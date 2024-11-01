<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Models;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Utils\DateFunctions;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\AbstractPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
class AirModelPostModel extends AbstractPostModel implements PostModelInterface
{
    protected const SKIP_JSON_PROPERTIES = array('userIndexIdMap', 'itemIndexIdMap', 'sRoot', 'Ur', 'Vr', 'itemMeans');
    protected int $id = 0;
    protected int $features = 17;
    protected float $ratingPerOrder = 4;
    protected int $ratingCountOrder = 2;
    protected float $ratingPerView = 0.5;
    protected int $ratingCountView = 8;
    protected string $paramsHashTrained = '';
    protected string $paramsHashCurrent = '';
    protected bool $tuneAutomatically = \true;
    protected ?string $modelActivatedDateTime = '';
    protected ?string $trainingInitiatedDateTime = '';
    protected ?string $trainingStartedDateTime = '';
    protected ?string $trainingFinishedDateTime = '';
    protected ?string $trainingErroredDateTime = '';
    protected ?string $trainingStatusMessage = '';
    protected ?string $trainingLastSuccessDateTime = '';
    protected ?string $documentId = '';
    protected ?float $rmse = 0;
    protected ?int $usersCount = 0;
    protected ?int $itemsCount = 0;
    protected ?array $userIndexIdMap = array();
    protected ?array $itemIndexIdMap = array();
    protected ?array $sRoot = array();
    protected ?array $Ur = array();
    protected ?array $Vr = array();
    protected ?array $itemMeans = array();
    public function create() : ?AirModelPostModel
    {
        return new AirModelPostModel();
    }
    public static function postType() : string
    {
        return 'sparkair-model';
    }
    public static function postTypeArgs() : array
    {
        $labels = array('name' => __('AIR Models', 'sparkair'), 'singular_name' => __('AIR Model', 'sparkair'), 'menu_name' => __('AIR Models', 'sparkair'), 'all_items' => __('All AIR Models', 'sparkair'), 'view_item' => __('View AIR Model', 'sparkair'), 'add_new_item' => __('Add AIR Model', 'sparkair'), 'add_new' => __('Add AIR Model', 'sparkair'), 'edit_item' => __('Edit AIR Model', 'sparkair'), 'update_item' => __('Update AIR Model', 'sparkair'), 'search_items' => __('Search AIR Models', 'sparkair'), 'not_found' => __('Not found', 'sparkair'), 'not_found_in_trash' => __('Not found in bin', 'sparkair'));
        $args = array('label' => __('AIR Models', 'sparkair'), 'description' => __('AIR Models', 'sparkair'), 'rewrite' => array('slug' => 'sparkair_models'), 'labels' => $labels, 'supports' => array('title', 'custom-fields'), 'hierarchical' => \false, 'public' => \false, 'show_ui' => SPARK_DEV_MODE, 'show_in_menu' => \true, 'show_in_nav_menus' => \false, 'show_in_admin_bar' => \false, 'menu_position' => 5, 'can_export' => \false, 'has_archive' => \false, 'exclude_from_search' => \false, 'publicly_queryable' => \false, 'query_var' => \false, 'capability_type' => 'post', 'show_in_rest' => \false);
        return $args;
    }
    public function sanitizeArrayProperty(string $property, $value)
    {
        $options = array();
        if (\in_array($property, array('userIndexIdMap', 'itemIndexIdMap'))) {
            $options = \FILTER_VALIDATE_INT;
        } else {
            if (\in_array($property, array('sRoot', 'Ur', 'Vr', 'itemMeans'))) {
                $options = \FILTER_VALIDATE_FLOAT;
            } else {
                throw new \InvalidArgumentException();
            }
        }
        return \filter_var_array($value, $options, \false);
    }
    public function validate(string $property, $value)
    {
        $value = parent::validate($property, $value);
        return $value;
    }
    public function postToObject(\WP_Post $post) : ?PostModelInterface
    {
        $model = parent::postToObject($post);
        /** @var AirModelPostModel $model */
        $model->set('paramsHashCurrent', $model->getParamsHash());
        return $model;
    }
    public function resetStatusDateTimes()
    {
        $this->set('trainingInitiatedDateTime', '');
        $this->set('trainingStartedDateTime', '');
        $this->set('trainingFinishedDateTime', '');
        $this->set('trainingErroredDateTime', '');
        return $this->persist();
    }
    public function setStatusNow($status, $statusMessage = null)
    {
        $this->set($status, DateFunctions::nowIso());
        if ($status === 'trainingFinishedDateTime') {
            $this->set('trainingLastSuccessDateTime', DateFunctions::nowIso());
        }
        $this->set('trainingStatusMessage', $statusMessage);
        return $this->persist();
    }
    public function activateNow()
    {
        return $this->setStatusNow('modelActivatedDateTime');
    }
    public function initiateNow()
    {
        return $this->setStatusNow('trainingInitiatedDateTime');
    }
    public function startNow()
    {
        return $this->setStatusNow('trainingStartedDateTime');
    }
    public function finishNow()
    {
        return $this->setStatusNow('trainingFinishedDateTime');
    }
    public function errorNow($message)
    {
        return $this->setStatusNow('trainingErroredDateTime', $message);
    }
    public function isActive()
    {
        return \strlen($this->get('modelActivatedDateTime', '')) > 0;
    }
    public function isTrained()
    {
        return $this->get('usersCount', 0) + $this->get('itemsCount', 0) > 0;
    }
    public function isTraining()
    {
        if (!$this->isInitiated()) {
            return \false;
        }
        if ($this->isStarted() && !$this->isFinished() && !$this->isErrored()) {
            return \true;
        }
        $initiatedTime = new \DateTimeImmutable($this->get('trainingInitiatedDateTime', ''));
        $interval = \intval(\time() - $initiatedTime->getTimeStamp());
        if ($interval < 120 && !$this->isStarted()) {
            return \true;
        }
        return \false;
    }
    public function isErrored()
    {
        return \strlen($this->get('trainingErroredDateTime', '')) > 0;
    }
    public function isStarted()
    {
        return \strlen($this->get('trainingStartedDateTime', '')) > 0;
    }
    public function isFinished()
    {
        return \strlen($this->get('trainingFinishedDateTime', '')) > 0;
    }
    public function isInitiated()
    {
        return \strlen($this->get('trainingInitiatedDateTime', '')) > 0;
    }
    public static function loadActiveAirModel()
    {
        $airPost = new self();
        $args = array('orderby' => 'post_date', 'order' => 'DESC', 'post_type' => self::postType(), 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids', 'meta_query' => array(array('key' => 'modelActivatedDateTime', 'value' => '', 'compare' => '!=')));
        $modelPosts = get_posts($args);
        if (\count($modelPosts) === 0) {
            return null;
        }
        $airPostId = $modelPosts[0];
        return $airPost->load($airPostId);
    }
    public static function loadTimedOutModels()
    {
        $iso15MinsAgo = (new \DateTime('now'))->sub(\DateInterval::createFromDateString('15 minutes'))->format('c');
        $args = array('post_type' => self::postType(), 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids', 'meta_query' => array('relation' => 'AND', array('relation' => 'OR', array('relation' => 'AND', array('key' => 'trainingInitiatedDateTime', 'value' => '', 'compare' => '!='), array('key' => 'trainingInitiatedDateTime', 'value' => $iso15MinsAgo, 'compare' => '<')), array('relation' => 'AND', array('key' => 'trainingStartedDateTime', 'value' => '', 'compare' => '!='), array('key' => 'trainingStartedDateTime', 'value' => $iso15MinsAgo, 'compare' => '<'))), array('key' => 'trainingFinishedDateTime', 'value' => '', 'compare' => '='), array('key' => 'trainingErroredDateTime', 'value' => '', 'compare' => '=')));
        $modelPosts = get_posts($args);
        return \array_map(function ($airPostId) {
            $airPost = new self();
            return $airPost->load($airPostId);
        }, $modelPosts);
    }
    public function getParamsHash()
    {
        return \md5(\wp_json_encode(array($this->get('features'), $this->get('ratingPerOrder'), $this->get('ratingCountOrder'), $this->get('ratingPerView'), $this->get('ratingCountView'))));
    }
    public static function computedProperties() : array
    {
        return array('paramsHashCurrent');
    }
}
