<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction;

use Sparkair\SparkPlugins\SparkWoo\Common\Collections\AbstractCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Collections\CollectionInterface;
class PredictionCollection extends AbstractCollection implements CollectionInterface, \JsonSerializable
{
    /**
     * @var Prediction[]
     */
    protected array $items;
    public function __construct(array $items = array())
    {
        parent::__construct($items);
    }
    public function filterPreviouslyBought(int $userId)
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT product_id FROM {$wpdb->prefix}wc_order_product_lookup WHERE customer_id = %d", array($userId)), ARRAY_N);
        $boughtItemIds = \array_merge(...\array_values($results));
        $items = array();
        foreach ($this->items as $item) {
            if (\in_array($item->getProductId(), $boughtItemIds)) {
                continue;
            }
            $items[] = $item;
        }
        $this->items = $items;
        return $this;
    }
    public function filterNonExistingProducts()
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product'", ARRAY_N);
        $existingProducts = \array_merge(...\array_values($results));
        $items = array();
        foreach ($this->items as $item) {
            if (!\in_array($item->getProductId(), $existingProducts)) {
                continue;
            }
            $items[] = $item;
        }
        $this->items = $items;
        return $this;
    }
    public function filterProductIds($productIds)
    {
        if (empty($productIds)) {
            return $this;
        }
        $items = array();
        foreach ($this->items as $item) {
            if (\in_array($item->getProductId(), $productIds)) {
                continue;
            }
            $items[] = $item;
        }
        $this->items = $items;
        return $this;
    }
    public function sortByPrediction()
    {
        \usort($this->items, function (Prediction $a, Prediction $b) {
            $aValue = $a->getSimilarity() !== null ? $a->getSimilarity() : $a->getRating();
            $bValue = $b->getSimilarity() !== null ? $b->getSimilarity() : $b->getRating();
            return $bValue <=> $aValue;
        });
        return $this;
    }
    public function setCount($count)
    {
        $this->items = \array_slice($this->items, 0, $count, \true);
        return $this;
    }
    public function unique()
    {
        $tempArray = \array_unique(\array_map(function ($p) {
            return $p->getProductId();
        }, $this->items));
        $this->items = \array_values(\array_intersect_key($this->items, $tempArray));
        return $this;
    }
    public function getTopProductIds($count) : array
    {
        $this->sortByPrediction();
        $this->unique();
        $this->setCount($count);
        return \array_map(function ($p) {
            return $p->getProductId();
        }, $this->items);
    }
    public function getItemByProductId($productId) : ?Prediction
    {
        foreach ($this->items as $item) {
            if ($item->getProductId() === $productId) {
                return $item;
            }
        }
        return null;
    }
}
