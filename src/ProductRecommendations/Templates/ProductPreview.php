<?php

namespace Sparkair;

use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
if (!\defined('ABSPATH')) {
    exit;
}
if (!current_user_can('manage_options') || !isset($_GET['sparkwooProductRecommendationPostModelData']) || !isset($_GET['sparkwooPluginSlug'])) {
    \wp_redirect(home_url());
    // Verwijzen naar de startpagina of een andere pagina/URL.
    exit;
}
$pluginSlug = sanitize_text_field($_GET['sparkwooPluginSlug']);
$pluginSlugCamel = \str_replace('-', '', \ucwords($pluginSlug, '-'));
$functionName = $pluginSlugCamel . '_getSparkPluginsContainer';
if (!\function_exists($functionName)) {
    \wp_redirect(home_url());
    // Verwijzen naar de startpagina of een andere pagina/URL.
    exit;
}
$container = $functionName();
$productRecommendationPostModelData = sanitize_text_field(\wp_unslash($_GET['sparkwooProductRecommendationPostModelData']));
$productRecommendationPostModel = $container->get('product-recommendations-model')->fromArray(\json_decode(\urldecode(\base64_decode($productRecommendationPostModelData)), \true));
$designSettings = $productRecommendationPostModel->get('designSettings');
$args = array('post_type' => 'product', 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page' => \array_key_exists('numberToShow', $designSettings) ? $designSettings['numberToShow'] : 1);
$products = new \WP_Query($args);
$products = \array_map('wc_get_product', $products->posts);
$handle = GlobalVariables::SPARKWOO_PREFIX . 'no-admin-bar';
\wp_register_style($handle, \false);
\wp_enqueue_style($handle);
\wp_add_inline_style($handle, ' #wpadminbar {
  display: none !important;
}');
?>

<html style="margin-top: 0px !important;">

<head>
  <?php 
\wp_head();
?>
</head>

<body <?php 
body_class(array('woocommerce'));
?>>
  <main id="main" class="site-main" role="main">
    <div style="margin: 0 2.75rem;">
      <?php 
$container->get('product-recommendations-partial')->render($productRecommendationPostModel, $products);
?>
    </div>
  </main>
  <?php 
\wp_footer();
?>
</body>

</html><?php 
