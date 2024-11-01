<?php

namespace Sparkair\SparkPlugins\SparkWoo\ProductRecommendations\Models;

use Sparkair\SparkPlugins\SparkWoo\Common\Collections\CollectionInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\AbstractPostModel;
use Sparkair\SparkPlugins\SparkWoo\Common\Models\PostModelInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMetaCollection;
class ProductRecommendationPostModel extends AbstractPostModel implements PostModelInterface
{
    protected int $id = 0;
    protected string $productsManager;
    protected array $pageHooks;
    protected array $designStyle;
    protected array $designSettings;
    private array $allowedPlacementHookNames;
    private ?PluginMetaCollection $pluginMetaCollection;
    public function __construct(array $allowedPlacementHookNames = array(), PluginMetaCollection $pluginMetaCollection = null)
    {
        $this->allowedPlacementHookNames = $allowedPlacementHookNames;
        $this->pluginMetaCollection = $pluginMetaCollection;
    }
    public function create() : ?ProductRecommendationPostModel
    {
        $object = new ProductRecommendationPostModel($this->allowedPlacementHookNames, $this->pluginMetaCollection);
        return $object;
    }
    public static function postType() : string
    {
        return 'sp-woo-prpt';
    }
    public static function postTypeArgs() : array
    {
        $labels = array('name' => __('Product recommendations', 'sparkair'), 'singular_name' => __('Product recommendation', 'sparkair'), 'menu_name' => __('Product recommendations', 'sparkair'), 'all_items' => __('All product recommendations', 'sparkair'), 'view_item' => __('View product recommendation', 'sparkair'), 'add_new_item' => __('Add product recommendation', 'sparkair'), 'add_new' => __('Add product recommendation', 'sparkair'), 'edit_item' => __('Edit product recommendation', 'sparkair'), 'update_item' => __('Update product recommendation', 'sparkair'), 'search_items' => __('Search product recommendations', 'sparkair'), 'not_found' => __('Not found', 'sparkair'), 'not_found_in_trash' => __('Not found in bin', 'sparkair'));
        $args = array('label' => __('Product recommendations', 'sparkair'), 'description' => __('Product recommendations', 'sparkair'), 'rewrite' => array('slug' => 'product_recommendations'), 'labels' => $labels, 'supports' => array('title', 'custom-fields'), 'hierarchical' => \false, 'public' => \false, 'show_ui' => SPARK_DEV_MODE, 'show_in_menu' => \true, 'show_in_nav_menus' => \false, 'show_in_admin_bar' => \false, 'menu_position' => 5, 'can_export' => \false, 'has_archive' => \false, 'exclude_from_search' => \false, 'publicly_queryable' => \false, 'query_var' => \false, 'capability_type' => 'post', 'show_in_rest' => \false);
        return $args;
    }
    public function sanitizeArrayProperty(string $property, $value)
    {
        $options = array();
        if ('designSettings' === $property) {
            $options = array('titleAboveProducts' => \FILTER_UNSAFE_RAW, 'showAddToCartButton' => \FILTER_VALIDATE_BOOLEAN, 'showPrice' => \FILTER_VALIDATE_BOOLEAN, 'hideNoProducts' => \FILTER_VALIDATE_BOOLEAN, 'showOutOfStockProducts' => \FILTER_VALIDATE_BOOLEAN, 'numberToShow' => array('filter' => \FILTER_VALIDATE_INT, 'flags' => \FILTER_REQUIRE_SCALAR), 'numberPerRow' => array('filter' => \FILTER_VALIDATE_INT, 'flags' => \FILTER_REQUIRE_SCALAR), 'numberPerRowSm' => array('filter' => \FILTER_VALIDATE_INT, 'flags' => \FILTER_REQUIRE_SCALAR), 'columnMargin' => \FILTER_VALIDATE_FLOAT, 'columnMarginUnit' => \FILTER_UNSAFE_RAW, 'useThemeColumnsSetting' => \FILTER_VALIDATE_BOOLEAN, 'useThemeNumberOfColumns' => \FILTER_VALIDATE_BOOLEAN, 'sliderEnabled' => \FILTER_VALIDATE_BOOLEAN, 'sliderShowArrows' => \FILTER_VALIDATE_BOOLEAN, 'sliderArrowsVariant' => \FILTER_UNSAFE_RAW, 'sliderArrowInside' => \FILTER_VALIDATE_BOOLEAN, 'sliderShowIndicator' => \FILTER_VALIDATE_BOOLEAN, 'sliderIndicatorVariant' => \FILTER_UNSAFE_RAW, 'sliderAuto' => \FILTER_VALIDATE_BOOLEAN, 'showAddAllToCart' => \FILTER_VALIDATE_BOOLEAN, 'showLoginSuggestion' => \FILTER_VALIDATE_BOOLEAN, 'titleShopTheCombination' => \FILTER_UNSAFE_RAW, 'showMatchPercentage' => \FILTER_VALIDATE_BOOLEAN);
        } else {
            if ('designStyle' === $property) {
                $options = array('custom' => \FILTER_VALIDATE_BOOLEAN, 'addToCartButtonColor' => \FILTER_UNSAFE_RAW, 'addToCartButtonTextColor' => \FILTER_UNSAFE_RAW, 'backgroundColor' => \FILTER_UNSAFE_RAW, 'titleColor' => \FILTER_UNSAFE_RAW, 'paddingX' => \FILTER_VALIDATE_FLOAT, 'paddingXUnit' => \FILTER_UNSAFE_RAW, 'paddingY' => \FILTER_VALIDATE_FLOAT, 'paddingYUnit' => \FILTER_UNSAFE_RAW, 'titleTag' => \FILTER_UNSAFE_RAW, 'titleBold' => \FILTER_VALIDATE_BOOLEAN, 'titleItalic' => \FILTER_VALIDATE_BOOLEAN, 'titleUnderlined' => \FILTER_VALIDATE_BOOLEAN, 'titleMarginBottom' => \FILTER_VALIDATE_FLOAT, 'titleMarginBottomUnit' => \FILTER_UNSAFE_RAW);
                $value['addToCartButtonColor'] = sanitize_hex_color($value['addToCartButtonColor']);
                $value['addToCartButtonTextColor'] = sanitize_hex_color($value['addToCartButtonTextColor']);
                $value['backgroundColor'] = sanitize_hex_color($value['backgroundColor']);
                $value['titleColor'] = sanitize_hex_color($value['titleColor']);
            } else {
                if ('pageHooks' === $property) {
                    $options = \FILTER_UNSAFE_RAW;
                } else {
                    throw new \InvalidArgumentException();
                }
            }
        }
        return \filter_var_array($value, $options, \false);
    }
    public function validate(string $property, $value)
    {
        $value = parent::validate($property, $value);
        if ('productsManager' === $property) {
            if (!empty($this->pluginMetaCollection)) {
                $validItems = \array_unique($this->pluginMetaCollection->map(function ($i) {
                    return $i->extra['productsManager']['slug'];
                }));
                if (!\in_array($value, $validItems)) {
                    $value = '';
                }
            }
        } else {
            if ('designSettings' === $property) {
                // no validation needed
            } else {
                if ('designStyle' === $property) {
                    // no validation needed
                } else {
                    if ('pageHooks' === $property) {
                        if (!empty($this->allowedPlacementHookNames)) {
                            $newPageHooks = array();
                            foreach ($value as $hook) {
                                if (\in_array($hook, $this->allowedPlacementHookNames)) {
                                    $newPageHooks[] = $hook;
                                }
                            }
                            $value = $newPageHooks;
                        }
                    }
                }
            }
        }
        return $value;
    }
}
