<?php

/**
* Plugin Name: Tax Exemption for WooCommerce
* Description: Tax Exemption plugin for WooCommerce. Allow customers to declare tax exemption eligibility, and provide tax exemption details.
* Version: 1.5.1
* Author: Elliot Sowersby, RelyWP
* Author URI: https://www.relywp.com
* License: GPLv3
* Text Domain: tax-exemption-woo
*
* WC requires at least: 3.7
* WC tested up to: 9.2.3
*
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
/* Freemius */
if ( function_exists( 'tefw_fs' ) ) {
    tefw_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'tefw_fs' ) ) {
        // Create a helper function for easy SDK access.
        function tefw_fs() {
            global $tefw_fs;
            if ( !isset( $tefw_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $tefw_fs = fs_dynamic_init( array(
                    'id'             => '12856',
                    'slug'           => 'tax-exemption-woo',
                    'premium_slug'   => 'tax-exemption-woo-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_74cda11e314c4e891f2bc93266523',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'       => 'tax-exemption-woo',
                        'first-path' => 'admin.php?page=tax-exemption-woo',
                        'account'    => false,
                        'contact'    => false,
                        'support'    => false,
                        'pricing'    => false,
                        'parent'     => array(
                            'slug' => 'woocommerce',
                        ),
                    ),
                    'is_live'        => true,
                ) );
            }
            return $tefw_fs;
        }

        // Init Freemius.
        tefw_fs();
        // Signal that SDK was initiated.
        do_action( 'tefw_fs_loaded' );
    }
    // Check if WooCommerce Installed and Active
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        // Settings
        $settings = get_option( 'tefw_settings', array() );
        // HPOS Compatible
        add_action( 'before_woocommerce_init', function () {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
        // Include admin styles and scripts
        add_action( 'admin_enqueue_scripts', 'tefw_admin_styles' );
        function tefw_admin_styles(  $hook  ) {
            if ( isset( $_GET['page'] ) && $_GET['page'] == 'tax-exemption-woo' ) {
                wp_enqueue_style(
                    'tefw-admin-styles',
                    plugin_dir_url( __FILE__ ) . 'css/admin.css',
                    array(),
                    '1.1.0',
                    'all'
                );
                wp_enqueue_script(
                    'tefw-admin-scripts',
                    plugin_dir_url( __FILE__ ) . 'js/admin.js',
                    array('jquery'),
                    '1.1.0',
                    true
                );
                wp_enqueue_script(
                    'tefw-admin-users-scripts',
                    plugin_dir_url( __FILE__ ) . 'js/admin-users.js',
                    array('jquery'),
                    '1.1.0',
                    true
                );
                wp_localize_script( 'tefw-admin-users-scripts', 'tefw_ajax_object', array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'update_tefw_nonce' ),
                ) );
            }
        }

        // JS to show or hide the additional VAT fields
        add_action( 'wp_footer', 'tefw_fields_scripts' );
        function tefw_fields_scripts() {
            if ( is_checkout() || is_account_page() ) {
                // Fields
                wp_enqueue_script(
                    'tefw-fields-script',
                    plugins_url( 'js/exemption-fields.js', __FILE__ ),
                    array('jquery'),
                    '1.1.0',
                    true
                );
                wp_enqueue_script( 'tefw-fields-script' );
                // Checkout
                wp_register_script(
                    'tefw_checkout_script',
                    plugins_url( 'js/update-checkout.js', __FILE__ ),
                    array('jquery'),
                    '1.1.0',
                    true
                );
                wp_enqueue_script( 'tefw_checkout_script' );
                // Styles
                wp_register_style(
                    'tefw_checkout_style',
                    plugins_url( 'css/styles.css', __FILE__ ),
                    array(),
                    '1.2.0',
                    'all'
                );
                wp_enqueue_style( 'tefw_checkout_style' );
            }
            // Block Checkout
            if ( function_exists( 'wc_block_checkout' ) && wc_block_checkout()->is_block_checkout_page() ) {
                wp_register_script(
                    'tefw_block_checkout_script',
                    plugins_url( 'js/block-checkout.js', __FILE__ ),
                    array('jquery'),
                    '1.1.0',
                    true
                );
                wp_enqueue_script( 'tefw_block_checkout_script' );
            }
        }

    }
    // Includes
    include_once plugin_dir_path( __FILE__ ) . 'inc/admin-options.php';
    $enable = ( isset( $settings['tefw_enable'] ) ? $settings['tefw_enable'] : 1 );
    if ( $enable && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        include_once plugin_dir_path( __FILE__ ) . 'inc/functions.php';
        include_once plugin_dir_path( __FILE__ ) . 'inc/functions-block-checkout.php';
        include_once plugin_dir_path( __FILE__ ) . 'inc/functions-checkout.php';
        include_once plugin_dir_path( __FILE__ ) . 'inc/functions-account.php';
        include_once plugin_dir_path( __FILE__ ) . 'inc/functions-users.php';
    }
}