<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

/**
 * SparkPlugins\SparkWoo\Common\Database\OrderItem
 *
 * @property int $order_item_id
 * @property int $order_id
 * @property int $product_id
 */
class OrderItemModel extends AbstractModel implements ModelInterface
{
    protected static string $tableName = 'wc_order_product_lookup';
}
