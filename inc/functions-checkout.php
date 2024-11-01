<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
// Tax Exempt Refresh Checkout
add_action( 'wp_footer', 'tefw_update_checkout' );
function tefw_update_checkout() {
    if ( !is_checkout() ) {
        // if is_tax_exempt session is set, remove it
        if ( WC()->session->__isset( 'is_tax_exempt' ) ) {
            WC()->session->__unset( 'is_tax_exempt' );
        }
    }
}

// AJAX: Trigger zero tax if eligible for Tax exemption
add_action( 'woocommerce_checkout_update_order_review', 'tefw_tax_exemption' );
function tefw_tax_exemption(  $post_data  ) {
    parse_str( $post_data, $output );
    WC()->session->set( 'is_tax_exempt', false );
    $settings = get_option( 'tefw_settings' );
    $customer_id = get_current_user_id();
    if ( !empty( $output ) && isset( $output['tefw_exempt'] ) ) {
        WC()->session->set( 'is_tax_exempt', true );
    }
    WC()->cart->calculate_totals();
}

// Set tax class for product and product variations
add_action( 'woocommerce_loaded', 'register_tefw_tax_filters' );
function register_tefw_tax_filters() {
    add_filter(
        'woocommerce_product_get_tax_class',
        'tefw_change_product_tax_class',
        1,
        2
    );
    add_filter(
        'woocommerce_product_variation_get_tax_class',
        'tefw_change_product_tax_class',
        1,
        2
    );
}

function tefw_change_product_tax_class(  $tax_class, $product  ) {
    $settings = get_option( 'tefw_settings' );
    $is_exempt = true;
    $vat_class = $settings['tefw_class'] ?? 'Zero rate';
    if ( $vat_class == 'standard' ) {
        $vat_class = '';
    }
    if ( WC()->session && WC()->session->get( 'is_tax_exempt' ) && $is_exempt ) {
        $tax_class = $vat_class;
    }
    return $tax_class;
}

// Clear session on order complete
add_action(
    'woocommerce_thankyou',
    'tefw_reset_after_order',
    10,
    1
);
function tefw_reset_after_order(  $order_id  ) {
    if ( WC()->session->__isset( 'is_tax_exempt' ) ) {
        WC()->session->__unset( 'is_tax_exempt' );
    }
}

// Adjust the shipping tax
add_filter(
    'woocommerce_package_rates',
    'tefw_adjust_shipping_tax',
    10,
    2
);
function tefw_adjust_shipping_tax(  $rates, $package  ) {
    $settings = get_option( 'tefw_settings' );
    if ( $settings['tefw_shipping_enable'] ?? 1 ) {
        if ( WC()->session->get( 'is_tax_exempt' ) ) {
            foreach ( $rates as $rate ) {
                $taxes = array_map( '__return_zero', $rate->taxes );
                $rate->set_taxes( $taxes );
            }
        }
    }
    return $rates;
}

// Adjust the order tax
add_action(
    'woocommerce_checkout_create_order_line_item',
    'tefw_adjust_order_tax',
    10,
    4
);
function tefw_adjust_order_tax(
    $item,
    $cart_item_key,
    $values,
    $order
) {
    if ( isset( $values['subscription_sign_up_fee'] ) || isset( $values['_subscription_sign_up_fee'] ) ) {
        return;
    }
    if ( $order ) {
        $settings = get_option( 'tefw_settings' );
        $is_exempt = $order->get_meta( 'tefw_exempt' );
        $product_id = $values['product_id'];
        if ( $is_exempt ) {
            $item->set_subtotal( $values['data']->get_price() );
            $item->set_total( $values['data']->get_price() );
        }
    }
}

// Add fields to checkout form
$settings = get_option( 'tefw_settings' );
$vat_location = ( isset( $settings['tefw_location'] ) ? $settings['tefw_location'] : 'afterbilling' );
if ( $vat_location === 'afterbilling' ) {
    add_action( 'woocommerce_after_checkout_billing_form', 'tefw_custom_checkout_fields' );
} elseif ( empty( $vat_location ) || $vat_location === 'beforebilling' ) {
    add_action( 'woocommerce_before_checkout_billing_form', 'tefw_custom_checkout_fields' );
} elseif ( $vat_location === 'afterorder' ) {
    add_action( 'woocommerce_after_order_notes', 'tefw_custom_checkout_fields' );
} elseif ( $vat_location === 'beforeorder' ) {
    add_action( 'woocommerce_before_order_notes', 'tefw_custom_checkout_fields' );
}
function tefw_custom_checkout_fields(  $checkout  ) {
    wp_nonce_field( 'tefw_custom_checkout_nonce', 'tefw_nonce' );
    $settings = get_option( 'tefw_settings' );
    $show_link = 0;
    $is_exempt = tefw_user_enabled();
    $tefw_exempt = $checkout->get_value( 'tefw_exempt' );
    $tefw_avatax_enable = ( isset( $settings['tefw_avatax_enable'] ) ? $settings['tefw_avatax_enable'] : 0 );
    $approval_enable = ( isset( $settings['tefw_approval_enable'] ) ? $settings['tefw_approval_enable'] : 0 );
    if ( !$is_exempt && $approval_enable ) {
        $tefw_certificate_register_enable = ( isset( $settings['tefw_certificate_register_enable'] ) ? $settings['tefw_certificate_register_enable'] : 1 );
        if ( $tefw_certificate_register_enable ) {
            $tefw_approval_message = ( isset( $settings['tefw_approval_message'] ) ? $settings['tefw_approval_message'] : 1 );
            if ( $tefw_approval_message ) {
                $show_link = 1;
            }
        }
    }
    if ( $is_exempt ) {
        $settings = get_option( 'tefw_settings' );
        $vat_checkbox = ( isset( $settings['tefw_text_checkbox'] ) && $settings['tefw_text_checkbox'] ? esc_html( $settings['tefw_text_checkbox'] ) : esc_html__( 'I want to claim tax exemption', 'tax-exemption-woo' ) );
        $vat_details = $settings['tefw_text_details'] ?? '';
        $vat_name = ( isset( $settings['tefw_text_name'] ) && $settings['tefw_text_name'] ? esc_html( $settings['tefw_text_name'] ) : esc_html__( 'Name of person to which Tax exemption applies', 'tax-exemption-woo' ) );
        $tefw_name_required = ( isset( $settings['tefw_name_required'] ) ? $settings['tefw_name_required'] : 1 );
        if ( $tefw_name_required ) {
            $vat_name .= ' <abbr class="required">*</abbr>';
        }
        $vat_reason = ( isset( $settings['tefw_text_reason'] ) && $settings['tefw_text_reason'] ? esc_html( $settings['tefw_text_reason'] ) : esc_html__( 'Reason for Tax exemption', 'tax-exemption-woo' ) );
        $tefw_reason_required = ( isset( $settings['tefw_reason_required'] ) ? $settings['tefw_reason_required'] : 1 );
        if ( $tefw_reason_required ) {
            $vat_reason .= ' <abbr class="required">*</abbr>';
        }
        $vat_expiration = ( isset( $settings['tefw_text_expiration'] ) && $settings['tefw_text_expiration'] ? esc_html( $settings['tefw_text_expiration'] ) : esc_html__( 'Expiration Date', 'tax-exemption-woo' ) );
        $vat_expiration .= ' <abbr class="required">*</abbr>';
        if ( !$tefw_avatax_enable ) {
            echo '<div id="tefw_fields">';
        }
        $vat_location = ( isset( $settings['tefw_location'] ) ? $settings['tefw_location'] : 'afterbilling' );
        if ( $vat_location === 'afterorder' || $vat_location === 'beforeorder' ) {
            echo '<br/>';
        }
        $tefw_hide_checkout = 0;
        $tefw_hide_checkbox = 0;
        // Checkbox
        if ( !$tefw_hide_checkbox ) {
            woocommerce_form_field( 'tefw_exempt', array(
                'type'        => 'checkbox',
                'class'       => array('form-row-wide'),
                'input_class' => array('woocommerce-form__input', 'woocommerce-form__input-checkbox', 'input-checkbox'),
                'label'       => $vat_checkbox,
                'description' => $vat_details,
            ), $tefw_exempt );
        }
        if ( !$tefw_hide_checkbox ) {
            echo '<div id="tefw_additional_fields" style="display:none; margin-top: 5px;">';
            $description = ( isset( $settings['tefw_text_description'] ) ? esc_html( $settings['tefw_text_description'] ) : '' );
            if ( $description ) {
                echo '<p class="tefw_fields_content description">' . wp_kses_post( $description ) . '</p>';
            }
            if ( !$tefw_avatax_enable && !$tefw_hide_checkout ) {
                do_action( 'tefw_before_fields' );
                $tefw_name_show = ( isset( $settings['tefw_name_show'] ) ? $settings['tefw_name_show'] : 1 );
                if ( $tefw_name_show ) {
                    woocommerce_form_field( 'tefw_exempt_name', array(
                        'type'     => 'text',
                        'class'    => array('form-row-wide'),
                        'label'    => wp_kses_post( $vat_name ),
                        'required' => false,
                    ), $checkout->get_value( 'tefw_exempt_name' ) );
                }
                $tefw_reason_show = ( isset( $settings['tefw_reason_show'] ) ? $settings['tefw_reason_show'] : 1 );
                if ( $tefw_reason_show ) {
                    woocommerce_form_field( 'tefw_exempt_reason', array(
                        'type'     => 'text',
                        'class'    => array('form-row-wide'),
                        'label'    => wp_kses_post( $vat_reason ),
                        'required' => false,
                    ), $checkout->get_value( 'tefw_exempt_reason' ) );
                }
                do_action( 'tefw_after_fields' );
            }
            echo '</div>';
        }
        if ( !$tefw_avatax_enable ) {
            echo '<br/></div>';
        }
    }
}

// Require the additional fields based on the checkbox
add_filter( 'woocommerce_checkout_process', 'tefw_checkbox_req_fields', 9999 );
function tefw_checkbox_req_fields(  $fields  ) {
    $settings = get_option( 'tefw_settings' );
    $tefw_hide_checkout = ( isset( $settings['tefw_hide_checkout'] ) ? $settings['tefw_hide_checkout'] : 0 );
    if ( $tefw_hide_checkout ) {
        return $fields;
    }
    if ( isset( $_POST['tefw_exempt'] ) && $_POST['tefw_exempt'] || WC()->session->get( 'is_tax_exempt' ) ) {
        $tefw_name_show = ( isset( $settings['tefw_name_show'] ) ? $settings['tefw_name_show'] : 1 );
        $tefw_name_required = ( isset( $settings['tefw_name_required'] ) ? $settings['tefw_name_required'] : 1 );
        if ( !$_POST['tefw_exempt_name'] && $tefw_name_show && $tefw_name_required ) {
            wc_add_notice( esc_html__( 'Please enter name for Tax exemption.', 'tax-exemption-woo' ), 'error' );
        }
        $tefw_reason_show = ( isset( $settings['tefw_reason_show'] ) ? $settings['tefw_reason_show'] : 1 );
        $tefw_reason_required = ( isset( $settings['tefw_reason_required'] ) ? $settings['tefw_reason_required'] : 1 );
        if ( !$_POST['tefw_exempt_reason'] && $tefw_reason_show && $tefw_reason_required ) {
            wc_add_notice( esc_html__( 'Please enter reason for Tax exemption.', 'tax-exemption-woo' ), 'error' );
        }
    }
    return $fields;
}

// Save the custom checkout fields in the order meta
add_action( 'woocommerce_checkout_order_processed', 'tefw_save_fields_to_order_meta' );
function tefw_save_fields_to_order_meta(  $order_id  ) {
    // Check if exempt
    $is_exempt = tefw_user_enabled();
    // Check if the VAT exemption checkbox is checked
    if ( empty( $_POST['tefw_exempt'] ) ) {
        $tefw_exempt = "";
    } else {
        $tefw_exempt = sanitize_text_field( $_POST['tefw_exempt'] );
    }
    // Save the VAT exemption
    if ( $is_exempt && $tefw_exempt ) {
        // Get the order
        $order = wc_get_order( $order_id );
        $customer_id = $order->get_customer_id();
        // Save the VAT exemption checkbox
        if ( $tefw_exempt ) {
            $order->update_meta_data( 'tefw_exempt', sanitize_text_field( $tefw_exempt ) );
            if ( $customer_id ) {
                update_user_meta( $customer_id, 'tefw_exempt', sanitize_text_field( $tefw_exempt ) );
            }
            $order->update_meta_data( 'is_vat_exempt', 'yes' );
        } else {
            update_user_meta( $customer_id, 'tefw_exempt', '0' );
        }
        // Save the VAT exemption name
        if ( empty( $_POST['tefw_exempt_name'] ) ) {
            $tefw_exempt_name = "";
        } else {
            $tefw_exempt_name = sanitize_text_field( $_POST['tefw_exempt_name'] );
        }
        $order->update_meta_data( 'tefw_exempt_name', sanitize_text_field( $tefw_exempt_name ) );
        if ( $customer_id ) {
            update_user_meta( $customer_id, 'tefw_exempt_name', $tefw_exempt_name );
        }
        // Save the VAT exemption reason
        if ( empty( $_POST['tefw_exempt_reason'] ) ) {
            $tefw_exempt_reason = "";
        } else {
            $tefw_exempt_reason = sanitize_text_field( $_POST['tefw_exempt_reason'] );
        }
        $order->update_meta_data( 'tefw_exempt_reason', sanitize_text_field( $tefw_exempt_reason ) );
        if ( $customer_id ) {
            update_user_meta( $customer_id, 'tefw_exempt_reason', $tefw_exempt_reason );
        }
        $order->save();
    }
}

// Display tax exemption details after the order details on the order paid page
add_action(
    'woocommerce_thankyou',
    'tefw_display_tax_exemption_details',
    15,
    1
);
function tefw_display_tax_exemption_details(  $order_id  ) {
    // Get the order object
    $order = wc_get_order( $order_id );
    // Check if the order is tax exempt
    $is_tax_exempt = $order->get_meta( 'is_vat_exempt' );
    // If the order is tax exempt, display the details
    if ( $is_tax_exempt === 'yes' ) {
        $exempt_name = $order->get_meta( 'tefw_exempt_name' );
        $exempt_reason = $order->get_meta( 'tefw_exempt_reason' );
        $custom_fields = array();
        // Localized strings
        $exempt_name_label = __( 'Exempt Name', 'tax-exemption-woo' );
        $exempt_reason_label = __( 'Exempt Reason', 'tax-exemption-woo' );
        // Display tax exemption details after the order details
        echo '<section class="woocommerce-exemption-details">';
        echo '<br/><h3>' . __( 'Tax Exemption Details', 'tax-exemption-woo' ) . '</h2>';
        echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
        echo '<tbody>';
        // Display exempt name and reason
        echo '<tr>';
        echo '<th scope="row">' . esc_html( $exempt_name_label ) . '</th>';
        echo '<td>' . esc_html( $exempt_name ) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row">' . esc_html( $exempt_reason_label ) . '</th>';
        echo '<td>' . esc_html( $exempt_reason ) . '</td>';
        echo '</tr>';
        // Display custom fields, if any
        foreach ( $custom_fields as $label => $value ) {
            echo '<tr>';
            echo '<th scope="row">' . esc_html( $label ) . '</th>';
            echo '<td>' . esc_html( $value ) . '</td>';
            echo '</tr>';
        }
        // File
        $customer_id = $order->get_customer_id();
        if ( $customer_id ) {
            $exemption_file = get_user_meta( $customer_id, 'tefw_exempt_file', true );
        } else {
            $exemption_file = $order->get_meta( 'tefw_exempt_file' );
        }
        if ( $exemption_file ) {
            $uploads_dir = wp_upload_dir();
            $exemption_file_url = $uploads_dir['baseurl'] . '/tax-exemption/' . $exemption_file;
            echo '<tr>';
            echo '<th scope="row">' . esc_html__( 'Exemption Certificate', 'tax-exemption-woo' ) . '</th>';
            // Link and html arrow
            echo '<td><a href="' . esc_url( $exemption_file_url ) . '" target="_blank">' . esc_html__( 'View', 'tax-exemption-woo' ) . ' &#8599;</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</section>';
    }
}

/* Include Tax Exemption Information at the very bottom of Admin "New Order" Email */
add_action(
    'woocommerce_email_after_order_table',
    'tefw_email_after_order_table',
    10,
    4
);
function tefw_email_after_order_table(
    $order,
    $sent_to_admin,
    $plain_text,
    $email
) {
    if ( !$sent_to_admin ) {
        return;
    }
    $is_tax_exempt = $order->get_meta( 'is_vat_exempt' );
    $options = get_option( 'tefw_settings' );
    $tefw_admin_new_order_email = ( isset( $options['tefw_admin_new_order_email'] ) ? $options['tefw_admin_new_order_email'] : 1 );
    // If the order is tax exempt, display the details
    if ( $is_tax_exempt === 'yes' && $tefw_admin_new_order_email ) {
        $exempt_name = $order->get_meta( 'tefw_exempt_name' );
        if ( !$exempt_name ) {
            return;
        }
        $exempt_reason = $order->get_meta( 'tefw_exempt_reason' );
        $exempt_expiration = $order->get_meta( 'tefw_exempt_expiration' );
        $exempt_file = $order->get_meta( 'tefw_exempt_file' );
        $custom_fields = array();
        // Display tax exemption details after the order details
        $exempt_name_label = __( 'Exempt Name', 'tax-exemption-woo' );
        $exempt_reason_label = __( 'Exempt Reason', 'tax-exemption-woo' );
        $exempt_expiration_label = __( 'Exempt Expiration', 'tax-exemption-woo' );
        echo '<section class="woocommerce-exemption-details">';
        echo '<h3>' . __( 'Tax Exemption Details', 'tax-exemption-woo' ) . '</h2>';
        // Simple list not table
        echo '<ul>';
        // Display exempt name and reason
        echo '<li>' . esc_html( $exempt_name_label ) . ': ' . esc_html( $exempt_name ) . '</li>';
        echo '<li>' . esc_html( $exempt_reason_label ) . ': ' . esc_html( $exempt_reason ) . '</li>';
        if ( $exempt_expiration ) {
            echo '<li>' . esc_html( $exempt_expiration_label ) . ': ' . esc_html( $exempt_expiration ) . '</li>';
        }
        // Display custom fields, if any
        foreach ( $custom_fields as $label => $value ) {
            echo '<li>' . esc_html( $label ) . ': ' . esc_html( $value ) . '</li>';
        }
        // File
        $uploads_dir = wp_upload_dir();
        if ( $exempt_file ) {
            $exemption_file_url = $uploads_dir['baseurl'] . '/tax-exemption/' . $exempt_file;
            echo '<li>' . esc_html__( 'Exemption Certificate', 'tax-exemption-woo' ) . ': <a href="' . esc_url( $exemption_file_url ) . '" target="_blank">' . esc_html__( 'View', 'tax-exemption-woo' ) . ' &#8599;</a></li>';
        }
        echo '</ul><br/>';
        echo '</section>';
    }
}
