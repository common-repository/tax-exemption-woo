<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
// Add multipart to user profile form
add_action( 'user_edit_form_tag', function () {
    echo ' enctype="multipart/form-data"';
} );
// Add fields to user profile editor
add_action( 'show_user_profile', 'tefw_add_user_profile_fields' );
add_action( 'edit_user_profile', 'tefw_add_user_profile_fields' );
function tefw_add_user_profile_fields(  $user  ) {
    $settings = get_option( 'tefw_settings' );
    ?>
    <h3 id="tefw_fields"><?php 
    echo ( isset( $settings['tefw_text_title'] ) && $settings['tefw_text_title'] ? esc_html( $settings['tefw_text_title'] ) : esc_html__( 'Tax Exemption', 'tax-exemption-woo' ) );
    ?> <?php 
    esc_html_e( 'Information', 'tax-exemption-woo' );
    ?></h3>

    <p class="description"><?php 
    echo esc_html( $settings['tefw_text_description'] ) ?? '';
    ?></p>

    <table class="form-table">

        <tr>
            <th><label for="tefw_exempt"><?php 
    echo esc_html__( 'Tax Exempt', 'tax-exemption-woo' );
    ?></label></th>
            <td>
                <input type="checkbox" name="tefw_exempt" id="tefw_exempt" value="1" <?php 
    checked( 1, get_the_author_meta( 'tefw_exempt', $user->ID ), true );
    ?> />
                <span class="description">("<?php 
    echo ( isset( $settings['tefw_text_checkbox'] ) && $settings['tefw_text_checkbox'] ? esc_html( $settings['tefw_text_checkbox'] ) : esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) );
    ?>")</span>
            </td>
        </tr>

        <?php 
    ?>

        <tr>
            <th><label for="tefw_exempt_name"><?php 
    echo ( isset( $settings['tefw_text_name'] ) && $settings['tefw_text_name'] ? esc_html( $settings['tefw_text_name'] ) : esc_html__( 'Name of person to which Tax Exemption applies', 'tax-exemption-woo' ) );
    ?></label></th>
            <td>
                <input type="text" name="tefw_exempt_name" id="tefw_exempt_name" value="<?php 
    echo esc_attr( get_the_author_meta( 'tefw_exempt_name', $user->ID ) );
    ?>" class="regular-text" /><br />
                <span class="description"><?php 
    esc_html_e( 'Enter the name of the person for which exemption applies.', 'tax-exemption-woo' );
    ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="tefw_exempt_reason"><?php 
    echo ( isset( $settings['tefw_text_reason'] ) && $settings['tefw_text_reason'] ? esc_html( $settings['tefw_text_reason'] ) : esc_html__( 'Reason for Tax Exemption', 'tax-exemption-woo' ) );
    ?></label></th>
            <td>
                <input type="text" name="tefw_exempt_reason" id="tefw_exempt_reason" value="<?php 
    echo esc_attr( get_the_author_meta( 'tefw_exempt_reason', $user->ID ) );
    ?>" class="regular-text" /><br />
                <span class="description"><?php 
    esc_html_e( 'Enter the reason for exemption.', 'tax-exemption-woo' );
    ?></span>
            </td>
			
        </tr>

        <?php 
    ?>

        <?php 
    // Nonce
    wp_nonce_field( 'tefw_nonce_action', 'tefw_nonce' );
    ?>

    </table>
    <?php 
}

// Save custom user profile fields
add_action( 'personal_options_update', 'tefw_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'tefw_save_user_profile_fields' );
function tefw_save_user_profile_fields(  $user_id  ) {
    // Check nonce
    if ( !isset( $_POST['tefw_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tefw_nonce'] ) ), 'tefw_nonce_action' ) ) {
        return false;
    }
    // Check permissions
    if ( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    $settings = get_option( 'tefw_settings' );
    // Save fields
    update_user_meta( $user_id, 'tefw_exempt', sanitize_text_field( $_POST['tefw_exempt'] ) );
    if ( isset( $_POST['tefw_exempt_status'] ) ) {
        update_user_meta( $user_id, 'tefw_exempt_status', sanitize_text_field( $_POST['tefw_exempt_status'] ) );
    }
    update_user_meta( $user_id, 'tefw_exempt_name', sanitize_text_field( $_POST['tefw_exempt_name'] ) );
    update_user_meta( $user_id, 'tefw_exempt_reason', sanitize_text_field( $_POST['tefw_exempt_reason'] ) );
    // Expiration
    $tefw_expiration_enable = ( isset( $settings['tefw_expiration_enable'] ) ? $settings['tefw_expiration_enable'] : 0 );
    if ( $tefw_expiration_enable ) {
        update_user_meta( $user_id, 'tefw_exempt_expiration', sanitize_text_field( $_POST['tefw_exempt_expiration'] ) );
    }
    // File
    if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_certificate_enable'] ) ? $settings['tefw_certificate_enable'] : 1 )) ) {
        // Premium only
        if ( isset( $_FILES['tefw_exempt_file'] ) ) {
            do_action( 'tefw_hook_account_upload_file', $user_id );
        }
    }
    if ( isset( $_POST['tefw_exempt_status'] ) ) {
        do_action(
            'after_update_tefw_exempt_status',
            $user_id,
            $_POST['tefw_exempt_status'],
            1
        );
    }
}

// Display field value on the order edit page
add_action( 'add_meta_boxes', 'tefw_order_meta_box' );
function tefw_order_meta_box() {
    $screen = ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order' );
    add_meta_box(
        'woocommerce-order-tax-exemption',
        'Tax Exemption',
        'tefw_meta_box_content',
        $screen,
        'side',
        'default'
    );
}

// Content callback
function tefw_meta_box_content(  $post_or_order_object  ) {
    $order = ( $post_or_order_object instanceof WP_Post ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object );
    $settings = get_option( 'tefw_settings' );
    $tax_exempt = sanitize_text_field( $order->get_meta( 'tefw_exempt' ) );
    $tefw_avatax_enable = ( isset( $settings['tefw_avatax_enable'] ) ? $settings['tefw_avatax_enable'] : 0 );
    if ( $tefw_avatax_enable ) {
        $tax_exempt_type = "";
        $wc_avatax_tax_exemption = get_user_meta( $order->get_customer_id(), 'wc_avatax_tax_exemption', true );
        if ( $wc_avatax_tax_exemption ) {
            $tax_exempt_type = $wc_avatax_tax_exemption;
            if ( !$tax_exempt_type ) {
                $tax_exempt_type = "";
            }
        }
    }
    $tefw_name = sanitize_text_field( $order->get_meta( 'tefw_exempt_name' ) );
    $tefw_reason = sanitize_text_field( $order->get_meta( 'tefw_exempt_reason' ) );
    $settings = get_option( 'tefw_settings' );
    $tefw_admin_edit_order_enable = ( isset( $settings['tefw_admin_edit_order_enable'] ) ? $settings['tefw_admin_edit_order_enable'] : 1 );
    if ( !$tefw_admin_edit_order_enable ) {
        return;
    }
    if ( !$tefw_avatax_enable ) {
        echo '<p><strong>' . esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) . ':</strong> ' . (( $tax_exempt ? 'Yes' : 'No' )) . '</p>';
    } else {
        if ( $tax_exempt ) {
            echo '<p><strong>' . esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) . ':</strong> Yes</p>';
        }
        if ( $tax_exempt_type ) {
            $entity_use_codes = tefw_avatax_codes();
            echo '<p><strong>' . esc_html__( 'Exemption Type', 'tax-exemption-woo' ) . ':</strong> ' . esc_html( $entity_use_codes[$tax_exempt_type] ) . '</p>';
        }
    }
    if ( $tax_exempt && !empty( $tefw_name ) ) {
        echo '<p><strong>' . esc_html__( 'Exemption Name', 'tax-exemption-woo' ) . ':</strong> ' . esc_html( $tefw_name ) . '</p>';
    }
    if ( $tax_exempt && !empty( $tefw_reason ) ) {
        echo '<p><strong>' . esc_html__( 'Exemption Reason', 'tax-exemption-woo' ) . ':</strong> ' . esc_html( $tefw_reason ) . '</p>';
    }
    $customer_id = $order->get_customer_id();
    $exemption_file = '';
    $tefw_certificate_per_order = ( isset( $settings['tefw_certificate_per_order'] ) ? $settings['tefw_certificate_per_order'] : 0 );
    if ( $customer_id && !$tefw_certificate_per_order ) {
        $exemption_file = get_user_meta( $customer_id, 'tefw_exempt_file', true );
    } else {
        if ( $order->get_meta( 'tefw_exempt_file' ) ) {
            $exemption_file = $order->get_meta( 'tefw_exempt_file' );
        } else {
            if ( $customer_id ) {
                $exemption_file = get_user_meta( $customer_id, 'tefw_exempt_file', true );
            }
        }
    }
    if ( $exemption_file ) {
        $uploads_dir = wp_upload_dir();
        $file_url = esc_url( $uploads_dir['baseurl'] . '/tax-exemption/' . $exemption_file );
        echo '<p><strong>' . esc_html__( 'Certificate', 'tax-exemption-woo' ) . ': </strong><a href="' . esc_html( $file_url ) . '" target="_blank">Download</a></p>';
    }
}

// Add custom column to the WooCommerce orders list
add_filter( 'manage_edit-shop_order_columns', 'tefw_add_tax_exempt_column' );
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'tefw_add_tax_exempt_column' );
function tefw_add_tax_exempt_column(  $columns  ) {
    $settings = get_option( 'tefw_settings' );
    $tefw_orders_col_enable = ( isset( $settings['tefw_orders_col_enable'] ) ? $settings['tefw_orders_col_enable'] : 0 );
    if ( !$tefw_orders_col_enable ) {
        return $columns;
    }
    $columns['tax_exempt'] = esc_html__( 'Exempt', 'tax-exemption-woo' );
    return $columns;
}

// Populate the custom column with data
add_action(
    'manage_shop_order_posts_custom_column',
    'tefw_populate_tax_exempt_column',
    10,
    2
);
add_action(
    'manage_woocommerce_page_wc-orders_custom_column',
    'tefw_populate_tax_exempt_column',
    10,
    2
);
function tefw_populate_tax_exempt_column(  $column, $post_id  ) {
    $settings = get_option( 'tefw_settings' );
    $tefw_orders_col_enable = ( isset( $settings['tefw_orders_col_enable'] ) ? $settings['tefw_orders_col_enable'] : 0 );
    $order = wc_get_order( $post_id );
    if ( $column === 'tax_exempt' && $tefw_orders_col_enable ) {
        $tefw_exempt = sanitize_text_field( $order->get_meta( 'tefw_exempt' ) );
        $customer_id = $order->get_customer_id();
        if ( $customer_id ) {
            $exemption_file = get_user_meta( $customer_id, 'tefw_exempt_file', true );
        } else {
            $exemption_file = $order->get_meta( 'tefw_exempt_file' );
        }
        $exemption_file = sanitize_text_field( $exemption_file );
        if ( $tefw_exempt && $exemption_file ) {
            $uploads_dir = wp_upload_dir();
            $file_url = esc_url( $uploads_dir['baseurl'] . '/tax-exemption/' . $exemption_file );
            if ( $file_url ) {
                echo '<a href="' . esc_url( $file_url ) . '" target="_blank"><span class="dashicons dashicons-media-document" title="' . esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) . '"></span></a>';
            } else {
                echo '<span class="dashicons dashicons-yes-alt" title="' . esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) . '"></span>';
            }
        } elseif ( $tefw_exempt ) {
            echo '<span class="dashicons dashicons-yes-alt" title="' . esc_html__( 'Tax Exempt', 'tax-exemption-woo' ) . '"></span>';
        } else {
            echo '-';
        }
        // Center the column
        echo '<style>.column-tax_exempt { text-align: center !important; }</style>';
    }
}
