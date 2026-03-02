<?php
add_filter('woocommerce_add_cart_item', 'site_force_lottery_to_normal_in_cart', 10, 1);
add_filter('woocommerce_get_cart_item_from_session', 'site_force_lottery_to_normal_in_cart', 10, 1);

function site_force_lottery_to_normal_in_cart($cart_item) {
    if (empty($cart_item['data']) || ! is_a($cart_item['data'], 'WC_Product')) {
        return $cart_item;
    }

    $product = $cart_item['data'];

    if ($product->get_type() !== 'lottery') {
        return $cart_item;
    }

    $cart_item['_original_product_type'] = 'lottery';
    $cart_item['_forced_product_type']   = 'simple';
    $cart_item['data']                   = new WC_Product_Simple($product->get_id());

    return $cart_item;
}

add_action('woocommerce_before_calculate_totals', function($cart) {
    if (! $cart || is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    foreach ($cart->get_cart() as $key => $item) {
        $cart->cart_contents[$key] = site_force_lottery_to_normal_in_cart($item);
    }
}, 20);

add_action('wp_footer', function() {
    if (! function_exists('is_checkout') || ! is_checkout()) {
        return;
    }
    ?>
    <script>
    (function () {
        const applyTypeBadges = () => {
            document.querySelectorAll('.wc-block-components-order-summary-item__image').forEach((imageWrap) => {
                if (imageWrap.querySelector('.site-product-type-badge')) return;
                const badge = document.createElement('div');
                badge.className = 'site-product-type-badge';
                badge.textContent = 'Type: normal';
                badge.style.fontSize = '12px';
                badge.style.marginTop = '4px';
                imageWrap.appendChild(badge);
            });
        };

        applyTypeBadges();
        const observer = new MutationObserver(applyTypeBadges);
        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>
    <?php
}, 99);
