<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Prediction;

use Sparkair\SparkPlugins\SparkWoo\Common\Admin\UserProfileSectionModule;
use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
class RecommendationsAdminModule implements ModuleInterface
{
    protected PluginMeta $pluginMeta;
    protected PredictorInterface $predictor;
    public function __construct(PluginMeta $pluginMeta, PredictorInterface $predictor)
    {
        $this->pluginMeta = $pluginMeta;
        $this->predictor = $predictor;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction(UserProfileSectionModule::USER_PROFILE_ACTION, $this, 'addUserProfileRecommendations');
        $loader->addAction('add_meta_boxes', $this, 'addProductSimilaritiesMetaBox', 20, 1);
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function addUserProfileRecommendations($user)
    {
        $id = $user->ID;
        $predictionCollection = $this->predictor->getRecommendationsByUserId($id);
        $predictionCollection->filterPreviouslyBought($user->ID);
        $predictionCollection->filterNonExistingProducts();
        $predictionCollection->sortByPrediction();
        $predictionCollection->setCount(10);
        echo '<div class="col-span-12 sm:col-span-6 xl:col-span-4 rounded-lg border bg-white shadow-sm focus:outline-none  px-4 py-5 text-sm text-gray-700 sm:rounded-md sm:p-6">';
        echo '<span class="font-bold text-base">SparkAIR</span>';
        echo '<p>Top recommended products for this user</p>';
        if (\count($predictionCollection) == 0) {
            echo '<p><em>SparkAIR has not created a user profile yet, please (re)train your model or there is not enough data to base recommendations on.</em></p>';
        } else {
            echo '<div>';
            $counter = 1;
            foreach ($predictionCollection as $prediction) {
                $productId = $prediction->getProductId();
                $product = \wc_get_product($productId);
                if (!$product) {
                    $text = $productId;
                } else {
                    $permalink = $product->get_permalink();
                    $title = $product->get_title();
                    $text = '<a target="_blank" href="' . esc_attr($permalink) . '">' . esc_html($title) . '</a>';
                }
                echo '
        <div class="flex py-0.5">
          <div class="shrink-0 w-6 font-bold text-right pr-2">' . esc_html($counter) . '.</div>
          <div class=" grow">' . \wp_kses($text, array('a' => array('href' => array(), 'title' => array(), 'class' => array()))) . '</div>
          <div class="shrink-0 inline-flex w-10 items-center pl-2 justify-end">
            <span class="whitespace-nowrap text-right items-center rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800">' . esc_html($prediction->getRating(\true)) . ' ☆</span>
          </div>
        </div>';
                $counter += 1;
            }
            echo '</div>';
        }
        echo '</div>';
    }
    public function addProductSimilaritiesMetaBox()
    {
        add_meta_box($this->pluginMeta->prefix . '_product_metabox', 'SparkAIR Recommendations', array($this, 'addProductSimilarities'), array('product'), 'side', 'default');
    }
    public function addProductSimilarities($post)
    {
        $ids = array($post->ID);
        $predictionCollection = $this->predictor->getSimilaritiesByProductIds($ids);
        $predictionCollection->filterNonExistingProducts();
        $predictionCollection->filterProductIds($ids);
        $predictionCollection->sortByPrediction();
        $predictionCollection->setCount(10);
        echo '<div class="sparkwoo-admin">';
        echo '<p>Users who like this product also like</p>';
        if (\count($predictionCollection) == 0) {
            echo '<p><em>SparkAIR has not created a profile for this product, please (re)train your model again or there is not enough data to base recommendations on.</em></p>';
        } else {
            echo '<div>';
            /** @var Prediction $prediction */
            foreach ($predictionCollection as $prediction) {
                $productId = $prediction->getProductId();
                $product = \wc_get_product($productId);
                if (!$product) {
                    $text = 'ID ' . $productId . ' doesn\'t exist anymore';
                } else {
                    $permalink = $product->get_permalink();
                    $title = $product->get_title();
                    $text = '<a target="_blank" href="' . esc_attr($permalink) . '">' . esc_html($title) . '</a>';
                }
                echo '
        <div class="flex py-0.5">
          <div class=" grow">' . \wp_kses($text, array('a' => array('href' => array(), 'title' => array(), 'class' => array()))) . '</div>
          <div class="shrink-0 inline-flex w-10 items-center pl-2 justify-end">
            <span class="whitespace-nowrap text-right items-center rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800">' . esc_html($prediction->getSimilarity(\true)) . '%</span>
          </div>
        </div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
