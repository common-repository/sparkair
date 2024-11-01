<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Repositories;

use Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training\TrainingDataRecordCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\OrderItemModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Repositories\AbstractRepository;
use Sparkair\SparkPlugins\SparkWoo\Common\Repositories\RepositoryInterface;
class OrderItemRepository extends AbstractRepository implements RepositoryInterface
{
    protected static string $model = OrderItemModel::class;
    public function fillUserOrdersTrainingData(TrainingDataRecordCollection $trainingDataCollection, $since, $to, $max)
    {
        global $wpdb;
        $table = $this::getTable();
        $userItemOrders = $wpdb->get_results($wpdb->prepare("SELECT customer_id as userId, product_id as itemId, count(*) as itemInOrdersCount, max(date_created) as sinceDate\n        FROM (\n            SELECT customer_id, product_id, date_created\n            FROM {$table}\n            WHERE date_created >= %s AND date_created <= %s\n            ORDER BY date_created DESC\n            LIMIT %d\n        ) AS x\n        GROUP BY customer_id, product_id\n        ", $since, $to, $max), ARRAY_A);
        $minSinceDate = \gmdate('Y-m-d H:i:s');
        foreach ($userItemOrders as $record) {
            $trainingDataCollection->setUserItemOrders(\intval($record['userId']), \intval($record['itemId']), \intval($record['itemInOrdersCount']));
            $minSinceDate = \min($minSinceDate, $record['sinceDate']);
        }
        $trainingDataCollection->setSince($minSinceDate);
    }
}
