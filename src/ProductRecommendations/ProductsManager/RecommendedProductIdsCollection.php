<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager;

use Sparkair\SparkPlugins\SparkWoo\Common\Collections\AbstractCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Collections\CollectionInterface;
class RecommendedProductIdsCollection extends AbstractCollection implements CollectionInterface
{
    public function takeFirst(int $count) : RecommendedProductIdsCollection
    {
        $this->items = \array_slice($this->items, 0, $count);
        return $this;
    }
    public function filterCurrentlyInCart() : RecommendedProductIdsCollection
    {
        if (empty(WC()->cart)) {
            return $this;
        }
        $cartIds = \array_column(WC()->cart->get_cart(), 'product_id');
        $this->filter(function ($productId) use($cartIds) {
            return !\in_array($productId, $cartIds);
        });
        return $this;
    }
    public function filterCurrent() : RecommendedProductIdsCollection
    {
        global $product;
        if (is_single() && $product) {
            $this->filter(function ($productId) use($product) {
                return $productId !== $product->get_id();
            });
        }
        return $this;
    }
}
