<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Analytics;

use Sparkair\SparkPlugins\SparkWoo\Common\Models\ModelInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\OrderItemModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Repositories\AbstractRepository;
use Sparkair\SparkPlugins\SparkWoo\Common\Repositories\RepositoryInterface;
class AnalyticsEventRepository extends AbstractRepository implements RepositoryInterface
{
    protected static string $model = AnalyticsEventModel::class;
    public function save(ModelInterface $event)
    {
        if (empty($event->sessionId) && empty($event->userId)) {
            return;
        }
        parent::save($event);
    }
    public function getConvertedEventCount(int $orderId, int $orderItemId)
    {
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $query = "SELECT COUNT(*) FROM {$table} WHERE event = 'conversion' AND orderId = %d AND orderItemId = %d";
        $result = $wpdb->get_var($wpdb->prepare($query, $orderId, $orderItemId));
        return $result;
    }
    // This function can be removed when releasing a new version
    public function getConversionEventsSinceUpdate()
    {
        $since = \gmdate('Y-m-d', \strtotime('2024-05-29 00:00:00'));
        $to = \gmdate('Y-m-d H:i:s', \strtotime('now'));
        $table = esc_sql($this::$model::getTable());
        global $wpdb;
        $query = "SELECT *\n      FROM {$table}\n      WHERE event = %s\n      AND timestamp BETWEEN %s AND %s\n      ORDER BY timestamp DESC";
        $results = $wpdb->get_results($wpdb->prepare($query, AnalyticsEventModel::CONVERSION, $since, $to), ARRAY_A);
        if (!$results) {
            return array();
        }
        return $results;
    }
    public function getConvertableClickEvent(string $slug, int $productId, int $userId, ?string $sessionId, string $now = 'now')
    {
        $to = \gmdate('Y-m-d H:i:s', \strtotime($now));
        $datetime = new \DateTime($now, new \DateTimeZone('UTC'));
        $datetime->modify('-7 days');
        $since = $datetime->format('Y-m-d H:i:s');
        $table = esc_sql($this::$model::getTable());
        global $wpdb;
        if (empty($sessionId)) {
            $query = "SELECT *\n        FROM {$table}\n        WHERE event = %s\n        AND plugin = %s\n        AND productId = %d\n        AND userId = %d\n        AND timestamp BETWEEN %s AND %s\n        ORDER BY timestamp DESC\n        LIMIT 1";
            $prepared = $wpdb->prepare($query, AnalyticsEventModel::CLICK, $slug, $productId, $userId, $since, $to);
        } else {
            $query = "SELECT *\n        FROM {$table}\n        WHERE event = %s\n        AND plugin = %s\n        AND productId = %d\n        AND (userId = %d OR sessionId = %s)\n        AND timestamp BETWEEN %s AND %s\n        ORDER BY timestamp DESC\n        LIMIT 1";
            $prepared = $wpdb->prepare($query, AnalyticsEventModel::CLICK, $slug, $productId, $userId, $sessionId, $since, $to);
        }
        $result = $wpdb->get_row($prepared, ARRAY_A);
        if (empty($result)) {
            return null;
        }
        return new AnalyticsEventModel($result);
    }
    public function deleteProductRenders(string $slug)
    {
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $wpdb->query($wpdb->prepare("DELETE FROM {$table}\n      WHERE event = %s \n      AND plugin = %s\n      AND timestamp < %s", AnalyticsEventModel::RENDER, $slug, \gmdate('Y-m-d H:i:s', \strtotime('-90 days'))));
    }
    public function calculateTotalRevenueForProductsManager($productsManager)
    {
        $total = $this->calculateTotalRevenuePerProductsManager([$productsManager]);
        return $total[0]['revenue'];
    }
    public function calculateTotalRevenuePerProductsManager(array $productsManagers)
    {
        global $wpdb;
        $orderItemTable = esc_sql(OrderItemModel::getTable());
        $table = esc_sql($this::$model::getTable());
        $productManagersListString = "'" . \implode("','", esc_sql($productsManagers)) . "'";
        $query = $wpdb->prepare("SELECT SUM(product_gross_revenue) as revenue, productsManager\n      FROM {$table}\n      LEFT JOIN {$orderItemTable} ON {$table}.orderItemId = {$orderItemTable}.order_item_id\n      WHERE event = %s\n      AND productsManager IN ({$productManagersListString})\n      GROUP BY productsManager", AnalyticsEventModel::CONVERSION);
        $analyticsStats = $wpdb->get_results($query, ARRAY_A);
        $data = \array_map(function ($pm) use($analyticsStats) {
            $key = \array_search($pm, \array_column($analyticsStats, 'productsManager'));
            return array('revenue' => \false !== $key ? \intval($analyticsStats[$key]['revenue']) : 0, 'productsManager' => $pm);
        }, $productsManagers);
        return $data;
    }
    public function getTopRecommendationsShown($filterProductManagers, $since, $to)
    {
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $productManagersListString = "'" . \implode("','", esc_sql($filterProductManagers)) . "'";
        $query = $wpdb->prepare("SELECT productsManager, placementHook, count(distinct timestamp) as value\n      FROM {$table}\n      WHERE event = %s\n      AND productsManager IN ({$productManagersListString})\n      AND timestamp BETWEEN %s AND %s\n      GROUP BY productsManager, placementHook\n      ORDER BY value DESC\n      LIMIT 10", AnalyticsEventModel::RENDER, $since, $to);
        $analyticsStats = $wpdb->get_results($query, ARRAY_A);
        return \array_map(function ($s) {
            $s['value'] = \intval($s['value']);
            return $s;
        }, $analyticsStats);
    }
    public function getGraphDataForEvent($event, $filterProductManagers, $since, $to, $sinceCompare, $toCompare, $utcOffset, $calculateRevenue)
    {
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $orderItemTable = esc_sql(OrderItemModel::getTable());
        $valueSelect = $calculateRevenue ? "sum(product_gross_revenue) as value" : "count(*) as value";
        $join = $calculateRevenue ? "LEFT JOIN " . $orderItemTable . " ON " . $orderItemTable . ".order_item_id = " . $table . ".orderItemId" : '';
        $productManagersListString = "'" . \implode("','", esc_sql($filterProductManagers)) . "'";
        $query = $wpdb->prepare("SELECT DATE(CONVERT_TZ(timestamp,'+00:00',%s)) as date, {$valueSelect}\n      FROM {$table}\n      {$join}\n      WHERE event = %s\n      AND productsManager IN ({$productManagersListString})\n      AND (\n        (timestamp BETWEEN %s AND %s)\n        OR (timestamp BETWEEN %s AND %s)\n      )\n      GROUP BY DATE(CONVERT_TZ(timestamp,'+00:00',%s))", $utcOffset, $event, $since, $to, $sinceCompare, $toCompare, $utcOffset);
        $analyticsStats = \array_map(function ($v) {
            $v['value'] = \intval($v['value']);
            return $v;
        }, $wpdb->get_results($query, ARRAY_A));
        return $analyticsStats;
    }
    public function getTopProductsByEvent($event, $productManagers, $since, $to)
    {
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $productManagersListString = "'" . \implode("','", esc_sql($productManagers)) . "'";
        $analyticsStats = $wpdb->get_results($wpdb->prepare("SELECT productId as postId, count(*) as value\n      FROM {$table}\n      WHERE event = %s\n      AND productsManager IN ({$productManagersListString})\n      AND timestamp BETWEEN %s AND %s\n      GROUP BY productId\n      ORDER BY value DESC\n      LIMIT 10", $event, $since, $to), ARRAY_A);
        return $analyticsStats;
    }
}
