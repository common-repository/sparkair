<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\RvpDataModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\OrderItemModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Repositories\OrderItemRepository;
class DatabaseTrainingDataRetriever implements TrainingDataRetrieverInterface
{
    protected string $rvpUserMetaKey;
    protected RvpDataModule $rvpDataModule;
    protected OrderItemRepository $orderItemRepository;
    public function __construct(string $rvpUserMetaKey, RvpDataModule $rvpDataModule, OrderItemRepository $orderItemRepository)
    {
        $this->rvpUserMetaKey = $rvpUserMetaKey;
        $this->rvpDataModule = $rvpDataModule;
        $this->orderItemRepository = $orderItemRepository;
    }
    public function retrieve() : array
    {
        $since = \gmdate('Y-m-d', \strtotime('-12 months'));
        $to = \gmdate('Y-m-d H:i:s', \strtotime('now'));
        $trainingDataCollection = new TrainingDataRecordCollection();
        $trainingDataCollection->setSince($since);
        $this->orderItemRepository->fillUserOrdersTrainingData($trainingDataCollection, $since, $to, 10000);
        if ($this->rvpDataModule->isRvp120Installed()) {
            $userIds = $trainingDataCollection->getUserIds();
            global $wpdb;
            $userIdsString = "'" . \implode("','", esc_sql($userIds)) . "'";
            $results = $wpdb->get_results($wpdb->prepare("SELECT {$wpdb->usermeta}.meta_value as rvpData, {$wpdb->usermeta}.user_id as userId FROM {$wpdb->usermeta} \n          WHERE {$wpdb->usermeta}.meta_key = %s\n          AND {$wpdb->usermeta}.user_id IN ({$userIdsString})\n          ", array($this->rvpUserMetaKey)), ARRAY_A);
            foreach ($results as $result) {
                $visited = \unserialize($result['rvpData']);
                $userId = \intval($result['userId']);
                $productViewsCount = array();
                foreach ($visited as $key => $visit) {
                    $productId = \intval($visit['productId']);
                    if ($visit['timestamp'] < \strtotime($trainingDataCollection->getSince())) {
                        break;
                    }
                    if (!\array_key_exists($productId, $productViewsCount)) {
                        $productViewsCount[$productId] = 0;
                    }
                    $productViewsCount[$productId]++;
                }
                foreach ($productViewsCount as $productId => $count) {
                    $trainingDataCollection->setUserItemViews($userId, $productId, $count);
                }
            }
        }
        $merged = \array_reduce(\array_values($trainingDataCollection->getItems()), function ($carry, $item) {
            return \array_merge_recursive($item->toArray(), $carry);
        }, array());
        return $merged;
    }
}
