<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// If WooCommerce checkout page and woocommerce block checkout, show inline script in footer
add_action( 'wp_footer', 'tefw_block_checkout_script' );
function tefw_block_checkout_script() {

    // If not block checkout page, return
    if( !function_exists('wc_block_checkout') || !wc_block_checkout()->is_block_checkout_page() ) {
        return;
    }

    $tefw_approval_text = (isset($settings['tefw_approval_text']) && $settings['tefw_approval_text']) ? $settings['tefw_approval_text'] : esc_html__( 'Want to claim tax exemption?', 'tax-exemption-woo' );
	$tefw_approval_button = (isset($settings['tefw_approval_button']) && $settings['tefw_approval_button']) ? $settings['tefw_approval_button'] : esc_html__( 'Click here', 'tax-exemption-woo' );
    
    $tefw_hide_checkout = isset($settings['tefw_hide_checkout']) ? $settings['tefw_hide_checkout'] : 0;
    $tefw_hide_checkbox_checkout = isset($settings['tefw_hide_checkbox_checkout']) ? $settings['tefw_hide_checkbox_checkout'] : 0;
    if(!$tefw_hide_checkout && !$tefw_hide_checkbox_checkout) {
    ?>
    <script>
    jQuery(document).ready(function($) {
        setTimeout(function() {
            $('.wc-block-checkout__billing-fields').after('<br/><div class="tefw-message"><p class="tefw_fields_content description"><?php echo $tefw_approval_text; ?> <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>tax-exemption/" target="_blank"><?php echo $tefw_approval_button; ?></a></p></div>');
        }, 500);
    });
    </script>

    <?php
    }
}
add_action( 'woocommerce_checkout_create_order', 'set_customer_tax_exempt_status', 20, 2 );

// Set customer to tax exempt in block checkout
function set_customer_tax_exempt_status( $order ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    // Check if user meta "tefw_exempt" is set
    $customer_id = get_current_user_id();
    $exempt = get_user_meta( $customer_id, 'tefw_exempt', true );
    if ( $exempt ) {
        if( WC()->session ) {
            WC()->session->set('is_tax_exempt', true);
        }
    }
}
add_action( 'woocommerce_checkout_init', 'set_customer_tax_exempt_status', 20, 1 );