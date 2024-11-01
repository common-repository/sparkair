<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductsManager;

use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
abstract class AbstractProductsManager
{
    protected string $slug;
    protected string $title;
    protected string $description;
    protected string $shortcode;
    public function __construct($slug, $title, $description, $shortcode)
    {
        $this->slug = $slug;
        $this->title = $title;
        $this->description = $description;
        $this->shortcode = $shortcode;
    }
    public function getSlug() : string
    {
        return $this->slug;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function getShortcode() : string
    {
        return $this->shortcode;
    }
    public function getProductRecommendationPostModelsByHook(string $hook) : array
    {
        $args = array('post_type' => ProductRecommendationPostModel::postType());
        $q = new \WP_Query($args);
        $posts = $q->posts;
        $productRecommendationPostModels = array();
        foreach ($posts as $post) {
            $model = new ProductRecommendationPostModel();
            $model = $model->postToObject($post);
            if ($this->getSlug() == $model->get('productsManager') && \in_array($hook, $model->get('pageHooks', array()))) {
                $productRecommendationPostModels[] = $model;
            }
        }
        return $productRecommendationPostModels;
    }
}
