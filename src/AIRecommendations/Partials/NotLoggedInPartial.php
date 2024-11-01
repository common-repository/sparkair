<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Partials;

use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials\PartialInterface;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials\ProductRecommendationsPartial;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks\ProductPlacementHookInterface;
class NotLoggedInPartial implements PartialInterface
{
    protected ProductRecommendationsPartial $defaultPartial;
    public function __construct(PartialInterface $defaultPartial)
    {
        $this->defaultPartial = $defaultPartial;
    }
    public function render(ProductRecommendationPostModel $productRecommendationPostModel, array $products, ProductPlacementHookInterface $placementHook = null)
    {
        $prefix = GlobalVariables::SPARKWOO_PREFIX;
        $id = $productRecommendationPostModel->get('id', 'preview');
        $designSettings = $productRecommendationPostModel->get('designSettings');
        $showLoginSuggestion = \array_key_exists('showLoginSuggestion', $designSettings) ? $designSettings['showLoginSuggestion'] : \false;
        $callBack = function () use($id) {
            ?>
      <div class="shrink-0 w-full md:w-96 md:max-w-[30%] sparkwoo-not-logged-in-<?php 
            echo esc_attr($id);
            ?>">
        <div class="not-logged-in-partial">
          <h5><?php 
            esc_html_e('Want a personalized experience?', 'sparkair');
            ?></h5>
          <p><?php 
            esc_html_e('Log in to get personalized product recommendations', 'sparkair');
            ?></p>
          <a href="<?php 
            echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')));
            ?>" class="wp-element-button button login-button"><?php 
            esc_html_e('Log In', 'sparkair');
            ?></a>
        </div>
      </div>
<?php 
        };
        $do = !is_user_logged_in() && $showLoginSuggestion;
        if ($do) {
            if (0 === \count($products)) {
                add_action($prefix . 'product_recommendations_products' . $id, function () {
                });
            }
            add_action($prefix . 'product_recommendations_aside_products' . $id, $callBack);
        }
        $this->defaultPartial->render($productRecommendationPostModel, $products, $placementHook);
        if ($do) {
            remove_action($prefix . 'product_recommendations_aside_products' . $id, $callBack);
        }
    }
}
