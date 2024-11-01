<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Partials;

use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\StylesScripts\StylesScriptsModule;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models\ProductRecommendationPostModel;
use Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\ProductPlacementHooks\ProductPlacementHookInterface;
class ProductRecommendationsPartial implements PartialInterface
{
    protected BooleanOption $isMultiLanguageOption;
    public function __construct(BooleanOption $isMultiLanguageOption)
    {
        $this->isMultiLanguageOption = $isMultiLanguageOption;
    }
    public function render(ProductRecommendationPostModel $productRecommendationPostModel, array $products, ProductPlacementHookInterface $placementHook = null)
    {
        do_action(GlobalVariables::SPARKWOO_PREFIX . 'product_recommendations_enqueue_public');
        if (!\function_exists('woocommerce_product_loop_start')) {
            return;
        }
        \wc_set_loop_prop('name', 'sparkwoo-cross-sell');
        $postId = $productRecommendationPostModel->get('id', 'preview');
        $extraClass = 'render-' . $postId;
        $designSettings = $productRecommendationPostModel->get('designSettings');
        $showAddToCartButton = \array_key_exists('showAddToCartButton', $designSettings) ? $designSettings['showAddToCartButton'] : \true;
        $showPrice = \array_key_exists('showPrice', $designSettings) ? $designSettings['showPrice'] : \true;
        $titleAboveProducts = \array_key_exists('titleAboveProducts', $designSettings) ? $designSettings['titleAboveProducts'] : '';
        $numberPerRow = \intval(\array_key_exists('numberPerRow', $designSettings) ? $designSettings['numberPerRow'] : 4);
        $numberPerRowSm = \intval(\array_key_exists('numberPerRowSm', $designSettings) ? $designSettings['numberPerRowSm'] : 2);
        $useThemeColumnsSetting = \array_key_exists('useThemeColumnsSetting', $designSettings) ? $designSettings['useThemeColumnsSetting'] : \true;
        $useThemeNumberOfColumns = \array_key_exists('useThemeNumberOfColumns', $designSettings) ? $designSettings['useThemeNumberOfColumns'] : \true;
        $columnMargin = \array_key_exists('columnMargin', $designSettings) ? $this->fixFloat($designSettings['columnMargin']) : 1;
        $columnMarginUnit = \array_key_exists('columnMarginUnit', $designSettings) ? $designSettings['columnMarginUnit'] : 'em';
        $designStyle = $productRecommendationPostModel->get('designStyle');
        $defaultStyle = \array_key_exists('custom', $designStyle) ? !$designStyle['custom'] : \true;
        $addToCartButtonColor = \array_key_exists('addToCartButtonColor', $designStyle) ? $designStyle['addToCartButtonColor'] : \false;
        $addToCartButtonTextColor = \array_key_exists('addToCartButtonTextColor', $designStyle) ? $designStyle['addToCartButtonTextColor'] : \false;
        $backgroundColor = \array_key_exists('backgroundColor', $designStyle) ? $designStyle['backgroundColor'] : \false;
        $titleColor = \array_key_exists('titleColor', $designStyle) ? $designStyle['titleColor'] : \false;
        $paddingX = \array_key_exists('paddingX', $designStyle) ? $this->fixFloat($designStyle['paddingX']) : \false;
        $paddingXUnit = \array_key_exists('paddingXUnit', $designStyle) ? $designStyle['paddingXUnit'] : 'em';
        $paddingY = \array_key_exists('paddingY', $designStyle) ? $this->fixFloat($designStyle['paddingY']) : \false;
        $paddingYUnit = \array_key_exists('paddingYUnit', $designStyle) ? $designStyle['paddingYUnit'] : 'em';
        $titleTag = \array_key_exists('titleTag', $designStyle) ? $designStyle['titleTag'] : 'h4';
        $titleBold = \array_key_exists('titleBold', $designStyle) ? $designStyle['titleBold'] : \false;
        $titleItalic = \array_key_exists('titleItalic', $designStyle) ? $designStyle['titleItalic'] : \false;
        $titleUnderlined = \array_key_exists('titleUnderlined', $designStyle) ? $designStyle['titleUnderlined'] : \false;
        $titleMarginBottom = \array_key_exists('titleMarginBottom', $designStyle) ? $this->fixFloat($designStyle['titleMarginBottom']) : \false;
        $titleMarginBottomUnit = \array_key_exists('titleMarginBottomUnit', $designStyle) ? $designStyle['titleMarginBottomUnit'] : 'em';
        if (!$showAddToCartButton) {
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
        }
        if (!$showPrice) {
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price');
        }
        $flexPercentage = \round(100 / \max($numberPerRow, 1), 7);
        $flexPercentageSm = 100 / \max(\min($numberPerRowSm, 2), 1);
        $columnMarginCalculated = $columnMargin / 2 . $columnMarginUnit;
        $flexClasses = function ($classes) use($flexPercentage, $flexPercentageSm, $useThemeColumnsSetting, $columnMarginCalculated) {
            if (!$useThemeColumnsSetting) {
                $classes = \array_merge(array('md:!flex-[0_0_' . $flexPercentage . '%]', 'md:!max-w-[' . $flexPercentage . '%]'), $classes);
                $classes = \array_merge(array('!flex-[0_0_' . $flexPercentageSm . '%]', '!max-w-[' . $flexPercentageSm . '%]'), $classes);
                $classes = \array_filter($classes, function ($class) {
                    return !\in_array($class, array('first', 'last'));
                });
                $classes = \array_merge(array('!mx-0', '!w-auto', '!self-stretch', '!box-border'), $classes);
            }
            $classes = \array_merge(array('sparkwoo-pr-item', 'relative'), $classes);
            return $classes;
        };
        add_filter('woocommerce_post_class', $flexClasses, 10, 3);
        $customStyleHandle = GlobalVariables::SPARKWOO_PREFIX . StylesScriptsModule::PUBLIC_HANDLE_PREFIX . $extraClass;
        \wp_register_style($customStyleHandle, \false);
        \wp_enqueue_style($customStyleHandle);
        \wp_add_inline_style($customStyleHandle, '
      .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item,
      .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item,
      .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item,
      .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item,
      .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item,
      .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-item {
        ' . ($columnMargin ? 'padding-left: ' . esc_html($columnMarginCalculated) . '; padding-right: ' . esc_html($columnMarginCalculated) . ';' : '') . '
      }

      .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' .products,
      .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .products,
      .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' .products,
      .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' .products,
      .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .products,
      .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' .products {
        ' . ($columnMargin ? 'margin-left: -' . esc_html($columnMarginCalculated) . '; margin-right: -' . esc_html($columnMarginCalculated) . ';' : '') . '
      }
      ');
        if (!$defaultStyle) {
            \wp_add_inline_style($customStyleHandle, '
        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' ,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' ,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' ,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' ,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' ,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . '  {
          ' . ($backgroundColor ? 'background: ' . esc_html($backgroundColor) . ' !important;' : '') . '
          ' . ($paddingX ? 'padding-left: ' . esc_html($paddingX . $paddingXUnit) . ' !important; padding-right: ' . esc_html($paddingX . $paddingXUnit) . ' !important;' : '') . '
          ' . ($paddingY ? 'padding-top: ' . esc_html($paddingY . $paddingYUnit) . ' !important; padding-bottom: ' . esc_html($paddingY . $paddingYUnit) . ' !important;' : '') . '
        }

        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' .sparkwoo-pr-title {
          ' . ($titleColor ? 'color: ' . esc_html($titleColor) . ' !important;' : '') . '
          ' . ($titleBold ? 'font-weight: bold !important;' : '') . '
          ' . ($titleItalic ? 'font-style: italic !important;' : '') . '
          ' . ($titleUnderlined ? 'text-decoration: underline !important;' : '') . '
          ' . ($titleMarginBottom ? 'margin-bottom: ' . esc_html($titleMarginBottom . $titleMarginBottomUnit) . ' !important;' : '') . '
        }
    ');
            if ($showAddToCartButton) {
                \wp_add_inline_style($customStyleHandle, '
        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' a.button.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' button.button.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' a.button.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' button.button.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover {
          ' . ($addToCartButtonColor ? 'background: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonColor ? 'background-color: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonTextColor ? 'color: ' . esc_html($addToCartButtonTextColor) . ' !important;' : '') . '
        }

        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button:hover,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' a.button:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' button.button:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' input.button:hover,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button:hover,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' a.button:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' button.button:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' input.button:hover {
          ' . ($addToCartButtonColor ? 'background: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonColor ? 'background-color: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonTextColor ? 'color: ' . esc_html($addToCartButtonTextColor) . ' !important;' : '') . '
          
        }

        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' a.button,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' button.button,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' input.button,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' a.button,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' button.button,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' input.button {
          ' . ($addToCartButtonColor ? 'background: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonTextColor ? 'color: ' . esc_html($addToCartButtonTextColor) . ' !important;' : '') . '
          
        }

        .woocommerce #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' a.button.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' button.button.alt:hover,
        .woocommerce .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce-page #content .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover,
        .woocommerce-page #respond .sparkwoo-pr.' . esc_html($extraClass) . ' input#submit.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' a.button.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' button.button.alt:hover,
        .woocommerce-page .sparkwoo-pr.' . esc_html($extraClass) . ' input.button.alt:hover {
          ' . ($addToCartButtonColor ? 'background: ' . esc_html($addToCartButtonColor) . ' !important;' : '') . '
          ' . ($addToCartButtonTextColor ? 'color: ' . esc_html($addToCartButtonTextColor) . ' !important;' : '') . '
        }
      ');
            }
        }
        ?>
    <?php 
        $productsActionName = GlobalVariables::SPARKWOO_PREFIX . 'product_recommendations_products' . $postId;
        if (!has_action($productsActionName)) {
            add_action($productsActionName, function () use($products, $postId, $numberPerRow, $useThemeNumberOfColumns, $useThemeColumnsSetting, $columnMarginCalculated) {
                ?>
        <div class="grow relative w-full">
          <?php 
                if (\count($products) === 0) {
                    ?>
            <p><?php 
                    esc_html_e('Unfortunately, there are no products to show here...', 'sparkair');
                    ?></p>
          <?php 
                } else {
                    ?>
            <?php 
                    if (!$useThemeNumberOfColumns && $useThemeColumnsSetting) {
                        // Set columns WC default
                        $defaultColumnsWc = \wc_get_loop_prop('columns');
                        \wc_set_loop_prop('columns', $numberPerRow);
                        // Set columns WM
                        if (\function_exists('Sparkair\\woodmart_loop_prop') && \function_exists('Sparkair\\woodmart_set_loop_prop')) {
                            $defaultColumnsWm = woodmart_loop_prop('products_columns');
                            woodmart_set_loop_prop('products_columns', $numberPerRow);
                        }
                    }
                    $originalPost = $GLOBALS['post'];
                    $startLoop = \woocommerce_product_loop_start(\false);
                    if ($useThemeColumnsSetting) {
                        echo $startLoop;
                    } else {
                        echo \preg_replace('/(class="(([^"]*\\s)|))(products)((\\s[^"]*)|")/i', '$1 !flex !flex-wrap !gap-x-0 $4$5', $startLoop);
                    }
                    $originalPost = $GLOBALS['post'];
                    foreach ($products as $product) {
                        $GLOBALS['post'] = get_post($product->get_id());
                        setup_postdata($GLOBALS['post']);
                        \wc_get_template_part('content', 'product');
                    }
                    $GLOBALS['post'] = $originalPost;
                    \woocommerce_product_loop_end();
                    $GLOBALS['post'] = $originalPost;
                    if (!$useThemeNumberOfColumns && $useThemeColumnsSetting) {
                        // Restore columns WC default
                        \wc_set_loop_prop('columns', $defaultColumnsWc);
                        // Restore columns WM
                        if (\function_exists('Sparkair\\woodmart_set_loop_prop')) {
                            woodmart_set_loop_prop('products_columns', $defaultColumnsWm);
                        }
                    }
                    \wp_reset_postdata();
                    do_action(GlobalVariables::SPARKWOO_PREFIX . 'product_recommendations_after_products' . $postId);
                    ?>
          <?php 
                }
                ?>
        </div>
    <?php 
            });
        }
        ?>
    <div class="sparkwoo-public">
      <div class="sparkwoo-pr my-4 <?php 
        echo esc_attr($extraClass);
        ?>">
        <?php 
        if ('' != $titleAboveProducts && $titleAboveProducts) {
            ?>
          <<?php 
            echo esc_attr($titleTag);
            ?> class="sparkwoo-pr-title">
            <?php 
            if ($this->isMultiLanguageOption->getValue()) {
                ?>
              <?php 
                esc_html_e('We recommend these products for you!', 'sparkair');
                ?>
            <?php 
            } else {
                ?>
              <?php 
                echo esc_html($titleAboveProducts);
                ?>
            <?php 
            }
            ?>
          </<?php 
            echo esc_html($titleTag);
            ?>>
        <?php 
        }
        ?>
        <div class="flex md:flex-row flex-col md:space-x-5">
          <?php 
        do_action($productsActionName);
        ?>
          <?php 
        do_action(GlobalVariables::SPARKWOO_PREFIX . 'product_recommendations_aside_products' . $postId);
        ?>
        </div>
      </div>
    </div>

<?php 
        remove_filter('woocommerce_post_class', $flexClasses, 10);
        if (!$showAddToCartButton) {
            add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
        }
        if (!$showPrice) {
            add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price');
        }
    }
    private function fixFloat($float)
    {
        return \floatval(\str_replace(',', '.', $float));
    }
}
