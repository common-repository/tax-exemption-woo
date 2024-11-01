<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
add_action( 'admin_menu', 'tefw_create_menu', 999 );
function tefw_create_menu() {
    add_submenu_page(
        'woocommerce',
        'Tax Exemption Settings',
        'Tax Exemption',
        'manage_options',
        'tax-exemption-woo',
        'tefw_settings_page'
    );
    add_action( 'admin_init', 'tefw_register_settings' );
}

function tefw_register_settings() {
    register_setting( 'vat-exempt-settings', 'tefw_settings' );
}

function tefw_settings_page() {
    $settings = get_option( 'tefw_settings', array() );
    ?>
    <div class="wrap tax-exemption-settings">
        <h1><?php 
    echo esc_html__( 'Tax Exemption for WooCommerce', 'tax-exemption' );
    ?></h1><br/>

        <?php 
    // Check if WooCommerce Installed and Active
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'This plugin requires WooCommerce to be installed and activated.', 'tax-exemption-woo' ) . '</strong></p></div>';
    } else {
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php 
        echo esc_html__( 'General Settings', 'tax-exemption-woo' );
        ?></a>
            <a href="#pro" class="nav-tab"><?php 
        echo esc_html__( 'Advanced Settings', 'tax-exemption-woo' );
        ?>
            <?php 
        // Premium only
        ?>
            <span class="tefw-pro-badge">PRO</span>
            <?php 
        ?>
            </a>
            <a href="#custom-text" class="nav-tab"><?php 
        echo esc_html__( 'Custom Text', 'tax-exemption-woo' );
        ?></a>
            <?php 
        if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_custom_fields_enable'] ) ? $settings['tefw_custom_fields_enable'] : 1 )) ) {
            // Premium only
            ?>
            <a href="#custom-fields" class="nav-tab"><?php 
            echo esc_html__( 'Custom Fields', 'tax-exemption-woo' );
            ?></a>
            <?php 
        }
        ?>       
            <?php 
        if ( is_plugin_active( 'woocommerce-avatax/woocommerce-avatax.php' ) ) {
            ?>
            <a href="#avatax" class="nav-tab" id="tab-avatax"><?php 
            echo esc_html__( 'AvaTax', 'tax-exemption-woo' );
            ?>
            <?php 
            // Premium only
            ?>
            <span class="tefw-pro-badge">PRO</span>
            <?php 
            ?>
            </a>
            <?php 
        }
        ?>
            <a href="#users" class="nav-tab" id="tab-users"><?php 
        echo esc_html__( 'Exempt Customers', 'tax-exemption-woo' );
        ?></a>
        </h2>
        <?php 
        if ( isset( $_GET['tab'] ) ) {
            ?>
        <script>
        jQuery(document).ready(function($) {
            setTimeout(function() {
                $('#tab-<?php 
            echo esc_html( $_GET['tab'] ?? '' );
            ?>').click();
            }, 100);
        });
        </script>
        <?php 
        }
        ?>
        <?php 
        if ( isset( $_GET['search'] ) ) {
            ?>
        <script>
        jQuery(document).ready(function($) {
            setTimeout(function() {
                $('#tefw_search_field').val('<?php 
            echo esc_html( $_GET['search'] ?? '' );
            ?>');
                $('#tefw_search_field').keyup();
            }, 500);
        });
        </script>
        <?php 
        }
        ?>

        <form method="post" action="options.php" class="nav-tab-content">
            <?php 
        settings_fields( 'vat-exempt-settings' );
        ?>
            <?php 
        do_settings_sections( 'vat-exempt-settings' );
        ?>

            <div id="general" class="tab-panel active">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Enable Tax Exemption', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_enable = ( isset( $settings['tefw_enable'] ) ? $settings['tefw_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_enable]" value="1" <?php 
        checked( $tefw_enable );
        ?> />
                        </td>
                    </tr>
                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Exemption Settings', 'tax-exemption-woo' );
        ?></h2>

                <p>
                    <?php 
        echo esc_html__( 'The following settings allow you to customise how tax exemption is applied to orders.', 'tax-exemption-woo' );
        ?>
                </p>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Tax Class for Tax Exemption', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        // Retrieve the existing tax classes
        $tax_classes = WC_Tax::get_tax_classes();
        // Ensure it's an array
        if ( !is_array( $tax_classes ) ) {
            $tax_classes = [];
        }
        // Check if "Zero rate" is already in the array
        if ( !in_array( 'Zero rate', $tax_classes ) ) {
            if ( method_exists( 'WC_Tax', 'create_tax_class' ) ) {
                $tax_class_name = 'Zero rate';
                $tax_class_slug = sanitize_title( $tax_class_name );
                $tax_class = WC_Tax::create_tax_class( $tax_class_name, $tax_class_slug );
                // Update the zero rate class to 0%
                $tax_rate = array(
                    'tax_rate_country'  => '',
                    'tax_rate_state'    => '',
                    'tax_rate'          => '0',
                    'tax_rate_name'     => 'Zero rate',
                    'tax_rate_priority' => '1',
                    'tax_rate_compound' => '0',
                    'tax_rate_shipping' => '1',
                    'tax_rate_order'    => '1',
                    'tax_rate_class'    => $tax_class_slug,
                );
                if ( method_exists( 'WC_Tax', '_insert_tax_rate' ) ) {
                    $tax_rate_id = WC_Tax::_insert_tax_rate( $tax_rate );
                    $tax_rate['tax_rate_id'] = $tax_rate_id;
                    $tax_rate['tax_rate_class'] = $tax_class_slug;
                }
            }
        }
        ?>
                            <select name="tefw_settings[tefw_class]">
                                <?php 
        $tax_classes = WC_Tax::get_tax_classes();
        $current = $settings['tefw_class'] ?? 'Zero rate';
        echo '<option value="standard" ' . selected( $current, 'standard', false ) . '>' . esc_html__( 'Standard', 'tax-exemption-woo' ) . '</option>';
        foreach ( $tax_classes as $class ) {
            echo '<option value="' . esc_html( $class ) . '" ' . selected( $current, $class, false ) . '>' . esc_html( $class ) . '</option>';
        }
        ?>
                            </select>
                            <br/>
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'Select the tax class that will be applied to the tax exempt orders.', 'tax-exemption-woo' );
        ?>
                            <a href="<?php 
        echo esc_html( admin_url( 'admin.php?page=wc-settings&tab=tax' ) );
        ?>" target="_blank" style="color: #94abb4;"><?php 
        echo esc_html__( 'Edit Tax Classes', 'tax-exemption-woo' );
        ?></a></i>
                            <?php 
        if ( $current != 'Zero rate' ) {
            echo '<br/><br/><i style="font-size: 12px; color: #ff0000;">' . esc_html__( 'Warning: The tax class is not set to "Zero rate". This may result in tax still being applied to orders. To fix this set the above to "Zero rate".', 'tax-exemption-woo' ) . '</i>';
        }
        ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Remove Tax on Shipping', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_shipping_enable = ( isset( $settings['tefw_shipping_enable'] ) ? $settings['tefw_shipping_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_shipping_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_shipping_enable]" value="1" <?php 
        checked( $tefw_shipping_enable, '1' );
        ?> id="tefw_shipping_enable" />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, tax will also be removed from the shipping costs.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Tax Exemption Form', 'tax-exemption-woo' );
        ?></h2>

                <p>
                    <?php 
        echo esc_html__( 'The following settings allow you to customise where customers can customise their tax exemption details and status.', 'tax-exemption-woo' );
        ?>
                </p>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">
                            <?php 
        echo esc_html__( 'Show "Name" Field', 'tax-exemption-woo' );
        ?>
                        </th>
                        <td>
                            <?php 
        $tefw_name_show = ( isset( $settings['tefw_name_show'] ) ? $settings['tefw_name_show'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_name_show]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_name_show]" id="tefw_name_show"
                            value="1" <?php 
        checked( $tefw_name_show, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, a "Name" field will be shown on the tax exemption form.', 'tax-exemption-woo' );
        ?></i>
                            <span class="tefw-name-required-field">
                                <br/><br/>
                                <?php 
        $tefw_name_required = ( isset( $settings['tefw_name_required'] ) ? $settings['tefw_name_required'] : 1 );
        ?>
                                <input type="hidden" name="tefw_settings[tefw_name_required]" value="0" />
                                <input type="checkbox" name="tefw_settings[tefw_name_required]" id="tefw_name_required"
                                value="1" <?php 
        checked( $tefw_name_required, '1' );
        ?> />
                                <i style="font-size: 12px;"><?php 
        echo esc_html__( 'Required?', 'tax-exemption-woo' );
        ?></i>
                            </span>
                        </td>
                    </tr>
                    <script>
                    jQuery(document).ready(function($) {
                        $('#tefw_name_show').change(function() {
                            if($(this).is(':checked')) {
                                $('.tefw-name-required-field').show();
                            } else {
                                $('.tefw-name-required-field').hide();
                            }
                        });
                        if($('#tefw_name_show').is(':checked')) {
                            $('.tefw-name-required-field').show();
                        } else {
                            $('.tefw-name-required-field').hide();
                        }
                    });
                    </script>

                    <tr valign="top">
                        <th scope="row">
                            <?php 
        echo esc_html__( 'Show "Reason" Field', 'tax-exemption-woo' );
        ?>
                        </th>
                        <td>
                            <?php 
        $tefw_reason_show = ( isset( $settings['tefw_reason_show'] ) ? $settings['tefw_reason_show'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_reason_show]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_reason_show]" id="tefw_reason_show"
                            value="1" <?php 
        checked( $tefw_reason_show, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, a "Reason" field will be shown on the tax exemption form.', 'tax-exemption-woo' );
        ?></i>
                            <span class="tefw-reason-required-field">
                                <br/><br/>
                                <?php 
        $tefw_reason_required = ( isset( $settings['tefw_reason_required'] ) ? $settings['tefw_reason_required'] : 1 );
        ?>
                                <input type="hidden" name="tefw_settings[tefw_reason_required]" value="0" />
                                <input type="checkbox" name="tefw_settings[tefw_reason_required]" id="tefw_reason_required"
                                value="1" <?php 
        checked( $tefw_reason_required, '1' );
        ?> />
                                <i style="font-size: 12px;"><?php 
        echo esc_html__( 'Required?', 'tax-exemption-woo' );
        ?></i>
                            </span>
                        </td>
                    </tr>
                    <script>
                    jQuery(document).ready(function($) {
                        $('#tefw_reason_show').change(function() {
                            if($(this).is(':checked')) {
                                $('.tefw-reason-required-field').show();
                            } else {
                                $('.tefw-reason-required-field').hide();
                            }
                        });
                        if($('#tefw_reason_show').is(':checked')) {
                            $('.tefw-reason-required-field').show();
                        } else {
                            $('.tefw-reason-required-field').hide();
                        }
                    });
                    </script>

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Location on Checkout', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <select name="tefw_settings[tefw_location]">
                                <?php 
        $current_location = $settings['tefw_location'] ?? 'afterbilling';
        ?>
                                <option value="afterbilling" <?php 
        echo esc_html( selected( $current_location, 'afterbilling', false ) );
        ?>><?php 
        echo esc_html__( 'After Billing', 'tax-exemption-woo' );
        ?></option>
                                <option value="beforebilling" <?php 
        echo esc_html( selected( $current_location, 'beforebilling', false ) );
        ?>><?php 
        echo esc_html__( 'Before Billing', 'tax-exemption-woo' );
        ?></option>
                                <option value="afterorder" <?php 
        echo esc_html( selected( $current_location, 'afterorder', false ) );
        ?>><?php 
        echo esc_html__( 'After Order Notes', 'tax-exemption-woo' );
        ?></option>
                                <option value="beforeorder" <?php 
        echo esc_html( selected( $current_location, 'beforeorder', false ) );
        ?>><?php 
        echo esc_html__( 'Before Order Notes', 'tax-exemption-woo' );
        ?></option>
                            </select>
                            <br/>
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'Select where the tax exemption checkbox/form is shown on the checkout page.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Settings on My Account Page', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $account_enable = ( isset( $settings['tefw_account_enable'] ) ? $settings['tefw_account_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_account_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_account_enable]" id="tefw_account_enable"
                                value="1" <?php 
        checked( $account_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, a tab/section will be added to the "My Account" page for the user to edit their tax exemption details.', 'tax-exemption-woo' );
        ?></i>
                            <?php 
        $checkout_page_id = wc_get_page_id( 'checkout' );
        if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', $checkout_page_id ) ) {
            echo '<br/><br/><i style="font-size: 12px; color: #ff0000;">' . esc_html__( 'Note: Currently this is required when using the new "WooCommerce block checkout" layout.', 'tax-exemption-woo' ) . '</i>';
            echo '<br/><br/><i style="font-size: 12px; color: #ff0000;">' . esc_html__( 'When using the "block checkout", the fields will be hidden on checkout. A message will be shown instead, linking them to the account page where they can enable tax exemption for their account, after which they can return to checkout for tax to be removed.', 'tax-exemption-woo' ) . '</i>';
            echo '<br/><br/><i style="font-size: 12px; color: #ff0000;">' . sprintf( wp_kses_post( __( 'If you want full integration, you can switch to using a shortcode for the checkout page by editing <a href="%s" target="_blank">the checkout page</a> and replacing the block with the shortcode: [woocommerce_checkout]', 'tax-exemption-woo' ) ), esc_url( admin_url( 'post.php?post=' . $checkout_page_id . '&action=edit' ) ) ) . '</i>';
            // Set "tefw_account_enable" field to enabled and disable
            echo '<script>jQuery(document).ready(function($) { $("#tefw_account_enable").prop("checked", true).prop("disabled", true); });</script>';
            // Set the "tefw_location" field to "afterbilling" and disable
            echo '<script>jQuery(document).ready(function($) { $("select[name=\'tefw_settings[tefw_location]\']").val("afterbilling").prop("disabled", true); });</script>';
            // Update "tefw_account_enable" to enabled
            $the_settings = get_option( 'tefw_settings' );
            $the_settings['tefw_account_enable'] = 1;
            update_option( 'tefw_settings', $the_settings );
        }
        ?>
                        </td>
                    </tr>
                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Admin: Except Customer Details', 'tax-exemption-woo' );
        ?></h2>

                <p>
                    <?php 
        echo esc_html__( 'The following settings allow you to customise where tax exemption details will be displayed to admins for orders.', 'tax-exemption-woo' );
        ?>
                </p>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'New Order Email', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_admin_new_order_email = ( isset( $settings['tefw_admin_new_order_email'] ) ? $settings['tefw_admin_new_order_email'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_admin_new_order_email]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_admin_new_order_email]" value="1" <?php 
        checked( $tefw_admin_new_order_email, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, the customers tax exemption information will be included in the admin "New Order" emails.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Orders List Column', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_orders_col_enable = ( isset( $settings['tefw_orders_col_enable'] ) ? $settings['tefw_orders_col_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_orders_col_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_orders_col_enable]" value="1" <?php 
        checked( $tefw_orders_col_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, a column will be added to the admin orders list page to indicate if the order is tax exempt.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Edit Order Page', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_admin_edit_order_enable = ( isset( $settings['tefw_admin_edit_order_enable'] ) ? $settings['tefw_admin_edit_order_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_admin_edit_order_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_admin_edit_order_enable]" value="1" <?php 
        checked( $tefw_admin_edit_order_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'When enabled, a meta box will be added to the admin "Edit Order" page toview the customers tax exemption details.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

				</table>
            </div>

            <div id="custom-text" class="tab-panel">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Tax Exemption Title', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_title]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_title'] ) && $settings['tefw_text_title'] ? esc_attr( $settings['tefw_text_title'] ) : esc_html__( 'Tax Exemption', 'tax-exemption-woo' ) );
        ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Tax Exemption Description', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_description]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_description'] ) ? esc_attr( $settings['tefw_text_description'] ) : '' );
        ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Checkbox Label', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_checkbox]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_checkbox'] ) && $settings['tefw_text_checkbox'] ? esc_attr( $settings['tefw_text_checkbox'] ) : '' );
        ?>" placeholder="<?php 
        echo esc_html__( 'I want to claim tax exemption', 'tax-exemption-woo' );
        ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Checkbox Description', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_details]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_details'] ) && $settings['tefw_text_details'] ? esc_attr( $settings['tefw_text_details'] ) : '' );
        ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Name Field', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_name]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_name'] ) && $settings['tefw_text_name'] ? esc_attr( $settings['tefw_text_name'] ) : '' );
        ?>" placeholder="<?php 
        echo esc_html__( 'Name of person to which Tax Exemption applies', 'tax-exemption-woo' );
        ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Reason Field', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_reason]"
                                value="<?php 
        echo ( isset( $settings['tefw_text_reason'] ) && $settings['tefw_text_reason'] ? esc_attr( $settings['tefw_text_reason'] ) : '' );
        ?>" placeholder="<?php 
        echo esc_html__( 'Reason for Tax Exemption', 'tax-exemption-woo' );
        ?>" />
                        </td>
                    </tr>

                    <?php 
        $tefw_expiration_enable = ( isset( $settings['tefw_expiration_enable'] ) && $settings['tefw_expiration_enable'] ? $settings['tefw_expiration_enable'] : 0 );
        ?>
                    <?php 
        if ( $tefw_expiration_enable && tefw_fs()->can_use_premium_code__premium_only() ) {
            // Premium only
            ?>

                    <tr valign="top">
                        <th scope="row"><?php 
            echo esc_attr( ( isset( $settings['tefw_text_expiration_enable'] ) ? esc_html( $settings['tefw_text_expiration_enable'] ) : esc_html__( 'Expiration Date Field', 'tax-exemption-woo' ) ) );
            ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_expiration]"
                                value="<?php 
            echo ( isset( $settings['tefw_text_expiration'] ) && $settings['tefw_text_expiration'] ? esc_attr( $settings['tefw_text_expiration'] ) : '' );
            ?>" placeholder="<?php 
            echo esc_html__( 'Expiration Date', 'tax-exemption-woo' );
            ?>" />
                        </td>
                    </tr>

                    <?php 
        }
        ?>

                    <!-- AvaTax -->
                    <?php 
        if ( is_plugin_active( 'woocommerce-avatax/woocommerce-avatax.php' ) ) {
            ?>
                    <tr valign="top">
                        <th scope="row"><?php 
            echo esc_html__( 'Exemption Type', 'tax-exemption-woo' );
            ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_exemption_type]"
                                value="<?php 
            echo ( isset( $settings['tefw_text_exemption_type'] ) && $settings['tefw_text_exemption_type'] ? esc_attr( $settings['tefw_text_exemption_type'] ) : '' );
            ?>" placeholder="<?php 
            echo esc_html__( 'Exemption Type', 'tax-exemption-woo' );
            ?>" />
                        </td>
                    </tr>
                    <?php 
        }
        ?>

                    <?php 
        $tefw_certificate_enable = ( isset( $settings['tefw_certificate_enable'] ) && $settings['tefw_certificate_enable'] ? $settings['tefw_certificate_enable'] : 0 );
        ?>
                    <?php 
        if ( $tefw_certificate_enable && tefw_fs()->can_use_premium_code__premium_only() ) {
            // Premium only
            ?>

                    <tr valign="top">
                        <th scope="row"><?php 
            echo esc_html__( 'Tax Exemption Certificate', 'tax-exemption-woo' );
            ?></th>
                        <td>
                            <input type="text" name="tefw_settings[tefw_text_certificate]"
                                value="<?php 
            echo ( isset( $settings['tefw_text_certificate'] ) && $settings['tefw_text_certificate'] ? esc_attr( $settings['tefw_text_certificate'] ) : '' );
            ?>" placeholder="<?php 
            echo esc_html__( 'Tax Exemption Certificate', 'tax-exemption-woo' );
            ?>" />
                        </td>
                    </tr>

                    <?php 
        }
        ?>

                    <?php 
        ?>

                </table>
            </div>

            <?php 
        ?>

            <div id="pro" class="tab-panel">
            
                <?php 
        ?>
                    <p style="font-size: 20px;">
                        <a href="<?php 
        echo esc_html( admin_url() );
        ?>admin.php?page=tax-exemption-woo-pricing&trial=true">
                            <strong><?php 
        echo esc_html__( 'Upgrade to PRO to access these features and settings.', 'tax-exemption-woo' );
        ?></strong>
                        </a>
                    </p><br/>
                    <style>
                    #pro input[type=checkbox]:checked::before {
                        content: '';
                    }
                    </style>
                <?php 
        ?>

                <div <?php 
        ?>style="opacity: 0.5; pointer-events: none;"<?php 
        ?>>

                <h2><?php 
        echo esc_html__( 'Additional Fields', 'tax-exemption-woo' );
        ?></h2>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Enable Custom Fields', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $custom_fields_enable = ( isset( $settings['tefw_custom_fields_enable'] ) ? $settings['tefw_custom_fields_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_custom_fields_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_custom_fields_enable]"
                                value="1" <?php 
        checked( $custom_fields_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, you can add your own custom fields to the tax exemption form.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Enable Expiration Date', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_expiration_enable = ( isset( $settings['tefw_expiration_enable'] ) ? $settings['tefw_expiration_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_expiration_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_expiration_enable]"
                                value="1" <?php 
        checked( $tefw_expiration_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, it will add an "Expiration Date" field to the tax exemption form.', 'tax-exemption-woo' );
        ?></i>
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If their exemption expires, it will be disabled on their account and if "approved users only" is enabled, they will require re-approval.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tefw_certificate_enable">
                        <th scope="row"><?php 
        echo esc_html__( 'Enable Exemption Certificates', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_certificate_enable = ( isset( $settings['tefw_certificate_enable'] ) ? $settings['tefw_certificate_enable'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_certificate_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_certificate_enable]"
                                value="1" <?php 
        checked( $tefw_certificate_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, it will add a "Tax Exemption Certificate" upload section/field to the tax exemption form.', 'tax-exemption-woo' );
        ?></i>
                            <i style="font-size: 12px;" class="tax-exemption-certificate-enabled"><br/><br/><?php 
        echo esc_html__( 'Certificate will be uploaded to the wp-content/uploads/tax-exemption folder. PDFs only. Max 250KB.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    <?php 
        ?>

                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Visibility Options', 'tax-exemption-woo' );
        ?></h2>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Fields on Registration Form', 'tax-exemption-woo' );
        ?></th>
                        <?php 
        $registration_enable = ( isset( $settings['tefw_registration_enable'] ) ? $settings['tefw_registration_enable'] : 0 );
        ?>
                        <td>
                            <input type="hidden" name="tefw_settings[tefw_registration_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_registration_enable]"
                                value="1" <?php 
        checked( $registration_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, a section will be added to the WooCommerce user registration form for the user to fill out their tax exemption fields.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tefw_hide_checkout">
                        <th scope="row"><?php 
        echo esc_html__( 'Hide Fields On Checkout', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_hide_checkout = ( isset( $settings['tefw_hide_checkout'] ) ? $settings['tefw_hide_checkout'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_hide_checkout]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_hide_checkout]" id="tefw_hide_checkout"
                                value="1" <?php 
        checked( $tefw_hide_checkout, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, they will only see the the "I want to claim tax exemption" checkbox on checkout. The tax exemption fields will be hidden and they will first need to edit their details on the "My Account" page.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    
                    <!-- Only show tefw_hide_checkbox_checkout if tefw_hide_checkout is enabled -->
                    <script>
                    jQuery(document).ready(function($) {
                        $('#tefw_hide_checkout').change(function() {
                            if($(this).is(':checked')) {
                                $('.tefw_hide_checkbox_checkout').show();
                            } else {
                                $('.tefw_hide_checkbox_checkout').hide();
                            }
                        });
                        if($('#tefw_hide_checkout').is(':checked')) {
                            $('.tefw_hide_checkbox_checkout').show();
                        } else {
                            $('.tefw_hide_checkbox_checkout').hide();
                        }
                    });
                    </script>
                    <tr valign="top" class="tefw_hide_checkbox_checkout">
                        <th scope="row"><?php 
        echo esc_html__( 'Hide Checkbox On Checkout', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $tefw_hide_checkbox_checkout = ( isset( $settings['tefw_hide_checkbox_checkout'] ) ? $settings['tefw_hide_checkbox_checkout'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_hide_checkbox_checkout]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_hide_checkbox_checkout]" id="tefw_hide_checkbox_checkout"
                                value="1" <?php 
        checked( $tefw_hide_checkbox_checkout, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, the "I want to claim tax exemption" checkbox will also be hidden on checkout for all users, and will either need to manage it on their account page if enabled, or otherwise can only be managed and enabled by admins.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Exemption Status & Approvals', 'tax-exemption-woo' );
        ?></h2>

                <p><?php 
        echo esc_html__( 'You can manage and edit the status of customers tax exemption in the "Exempt Customers" tab.', 'tax-exemption-woo' );
        ?></p>
                <p><?php 
        echo esc_html__( '"Approved" users have access to tax exemption. "Declined" users will have tax exemption features completely disabled. "Pending" users are awaiting approval.', 'tax-exemption-woo' );
        ?></p>
                
                <table class="form-table">

                    <tr valign="top" class="tefw_status_show">
                        <th scope="row"><?php 
        echo esc_html__( 'Show Exemption Status', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $status_enable = ( isset( $settings['tefw_status_show'] ) ? $settings['tefw_status_show'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_status_show]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_status_show]"
                                value="1" <?php 
        checked( $status_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, the user will be able to view their exemption status on the "my account" page.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tefw_approval_enable">
                        <th scope="row"><?php 
        echo esc_html__( 'Require Manual Approval', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $approval_enable = ( isset( $settings['tefw_approval_enable'] ) ? $settings['tefw_approval_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_approval_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_approval_enable]" id="tefw_approval_enable"
                                value="1" <?php 
        checked( $approval_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, customers need to be approved manually to enable exemption. After submitting the form on the "My Account" page, an email is sent to admin for approval, and their status set to "Pending".', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tax-exemption-approval-enabled">
                        <th scope="row"></th>
                        <td style="padding-top: 0;">
                            <?php 
        $tefw_approval_message = ( isset( $settings['tefw_approval_message'] ) ? $settings['tefw_approval_message'] : 1 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_approval_message]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_approval_message]"
                                value="1" <?php 
        checked( $tefw_approval_message, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'Show tax exemption message on checkout with link to account page for non-approved users.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    
                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Tax Class: Advanced Settings', 'tax-exemption-woo' );
        ?></h2>

                <table class="form-table">

                    <!-- tefw_user_class_enable -->
                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Tax Class Per Customer', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $user_class_enable = ( isset( $settings['tefw_user_class_enable'] ) ? $settings['tefw_user_class_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_user_class_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_user_class_enable]"
                                value="1" <?php 
        checked( $user_class_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, you can set a custom "Tax Class for Tax Exemption" for specific customers. This will override the global tax class set in the general settings.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'User Limitations', 'tax-exemption-woo' );
        ?></h2>

                <table class="form-table">

                    <tr valign="top" class="tax-exemption-logged-in-enable">
                        <th scope="row"><?php 
        echo esc_html__( 'Logged in users only', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $logged_in_enable = ( isset( $settings['tefw_logged_in_enable'] ) ? $settings['tefw_logged_in_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_logged_in_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_logged_in_enable]"
                                value="1" <?php 
        checked( $logged_in_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, tax exemption will only be available to logged in users.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tax-exemption-user-roles-enable">
                        <th scope="row"><?php 
        echo esc_html__( 'Selected user roles only', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $user_roles_enable = ( isset( $settings['tefw_user_roles_enable'] ) ? $settings['tefw_user_roles_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_user_roles_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_user_roles_enable]"
                                value="1" <?php 
        checked( $user_roles_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, tax exemption will only be available to selected user roles.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tax-exemption-selected-user-roles">
                        <th scope="row"></th>
                        <td style="padding-top: 0;">
                            <?php 
        $selected_user_roles = ( isset( $settings['tefw_selected_user_roles'] ) ? $settings['tefw_selected_user_roles'] : array() );
        $all_user_roles = get_editable_roles();
        ?>
                            <fieldset>
                                <?php 
        foreach ( $all_user_roles as $role_key => $role_data ) {
            ?>
                                    <label>
                                        <input type="checkbox" name="tefw_settings[tefw_selected_user_roles][]"
                                            value="<?php 
            echo esc_attr( $role_key );
            ?>" <?php 
            checked( in_array( $role_key, $selected_user_roles ), true );
            ?> />
                                        <?php 
            echo esc_html( $role_data['name'] );
            ?>
                                    </label><br>
                                <?php 
        }
        ?>
                            </fieldset>
                        </td>
                    </tr>

                </table>

                <hr style="margin-top: 25px; display: block;" /><br/>

                <h2><?php 
        echo esc_html__( 'Other Limitations', 'tax-exemption-woo' );
        ?></h2>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row"><?php 
        echo esc_html__( 'Specific products only', 'tax-exemption-woo' );
        ?></th>
                        <td class="tefw_product_enable">
                            <?php 
        $product_enable = ( isset( $settings['tefw_product_enable'] ) ? $settings['tefw_product_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_product_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_product_enable]"
                                value="1" <?php 
        checked( $product_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, you will need to edit each product individually and enable "Tax Exempt Product" to disable VAT for that product.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>
                    <tr valign="top" class="tax-exemption-product-enabled">
                        <th scope="row"></th>
                        <td style="padding-top: 0;">
                            <?php 
        $tefw_product_required = ( isset( $settings['tefw_product_required'] ) ? $settings['tefw_product_required'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_product_required]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_product_required]"
                                value="1" <?php 
        checked( $tefw_product_required, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'An eligible product must be in cart to show tax exemption checkbox/form on checkout.', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tax-exemption-countries">
                        <th scope="row"><?php 
        echo esc_html__( 'Selected countries only', 'tax-exemption-woo' );
        ?></th>
                        <td>
                            <?php 
        $user_countries_enable = ( isset( $settings['tefw_user_countries_enable'] ) ? $settings['tefw_user_countries_enable'] : 0 );
        ?>
                            <input type="hidden" name="tefw_settings[tefw_user_countries_enable]" value="0" />
                            <input type="checkbox" name="tefw_settings[tefw_user_countries_enable]"
                                value="1" <?php 
        checked( $user_countries_enable, '1' );
        ?> />
                            <i style="font-size: 12px;"><?php 
        echo esc_html__( 'If enabled, you can limit tax exemption fields to only be available to certain countries (billing address on checkout).', 'tax-exemption-woo' );
        ?></i>
                        </td>
                    </tr>

                    <tr valign="top" class="tax-exemption-selected-countries">
                        <th scope="row"></th>
                        <td style="padding-top: 0;">
                            <!-- Select option to choose if the text exemption is hidden or shown for selected countries -->
                            <select name="tefw_settings[tefw_selected_countries_visibility]" id="tefw_selected_countries_visibility">
                                <?php 
        $visibility = ( isset( $settings['tefw_selected_countries_visibility'] ) ? $settings['tefw_selected_countries_visibility'] : 'hide' );
        ?>
                                <option value="hide" <?php 
        selected( $visibility, 'hide' );
        ?>><?php 
        esc_html_e( 'Hide tax exemption for selected countries', 'tax-exemption-woo' );
        ?></option>
                                <option value="show" <?php 
        selected( $visibility, 'show' );
        ?>><?php 
        esc_html_e( 'Show tax exemption for selected countries only', 'tax-exemption-woo' );
        ?></option>
                            </select>
                            <br/><br/>
                            <p>
                                <?php 
        echo esc_html__( 'Select the countries here', 'tax-exemption-woo' );
        ?>:
                            </p>
                            <!-- Multiselect field with all countries and pick country codes -->
                            <?php 
        $selected_countries = ( isset( $settings['tefw_selected_countries'] ) ? $settings['tefw_selected_countries'] : array() );
        $all_countries = WC()->countries->get_countries();
        ?>
                            <select name="tefw_settings[tefw_selected_countries][]" class="tefw-selected-countries" multiple="multiple" style="width: 100%;">
                                <?php 
        foreach ( $all_countries as $country_key => $country_name ) {
            ?>
                                    <option value="<?php 
            echo esc_attr( $country_key );
            ?>" <?php 
            selected( in_array( $country_key, $selected_countries ) );
            ?>>
                                        <?php 
            echo esc_html( $country_name );
            ?>
                                    </option>
                                <?php 
        }
        ?>
                            </select>
                        </td>
                    </tr>
                    <script>
                    jQuery(document).ready(function($) {
                        $('.tefw-selected-countries').select2({
                            placeholder: "Type and select countries",
                            allowClear: true
                        });
                    });
                    </script>

                </table>

                </div>

            </div>


            <div id="avatax" class="tab-panel">

                <h3><?php 
        echo esc_html__( 'AvaTax Integration', 'tax-exemption-woo' );
        ?></h3>

                <?php 
        // Update tefw_account_enable and tefw_hide_checkout to enabled if avatax is enabled
        if ( isset( $settings['tefw_avatax_enable'] ) && $settings['tefw_avatax_enable'] ) {
            $new_settings = get_option( 'tefw_settings', array() );
            $new_settings['tefw_account_enable'] = 1;
            $new_settings['tefw_hide_checkout'] = 1;
            update_option( 'tefw_settings', $new_settings );
            ?>
                    <script>
                    jQuery(document).ready(function($) {
                        $('#tefw_account_enable').prop('checked', true);
                        $('#tefw_hide_checkout').prop('checked', true);
                        $('#tefw_shipping_enable').prop('checked', true);
                        $('#tefw_account_enable').prop('disabled', true);
                        $('#tefw_hide_checkout').prop('disabled', true);
                        $('#tefw_shipping_enable').prop('disabled', true);
                    });
                    </script>
                    <?php 
        }
        ?>

                <p>
                    <?php 
        echo esc_html__( 'If the AvaTax integration is enabled it will modify the plugin to work slightly differently in some areas, and will do the following:', 'tax-exemption-woo' );
        ?>
                </p>
                <p>
                    <?php 
        echo esc_html__( '- Display a new "Exemption Type" select field on the "My Account" page, for the customer to choose from, which will show all the tax exemption reasons available in AvaTax. When using AvaTax this field is required in order for tax to be removed properly.', 'tax-exemption-woo' );
        ?>   
                </p>
                <p>
                    <?php 
        echo esc_html__( '- The following options are required, and will be enabled automatically (if not already): "Remove Tax on Shipping", "Settings on My Account Page", and "Hide Fields On Checkout".', 'tax-exemption-woo' );
        ?>
                </p>
                <p>
                    <?php 
        echo esc_html__( '- This is compatible with the "Require Manual Approval" option. The exemption type will only be set for the customer in AvaTax once they are approved.', 'tax-exemption-woo' );
        ?>
                </p>
                <p>
                    <?php 
        echo esc_html__( '- In order to claim tax exemption, the customer will first need to go to the "My Account" page to set their exemption details and type.', 'tax-exemption-woo' );
        ?>
                </p>
                <p>
                    <?php 
        echo esc_html__( '- On checkout, it will show the "Exemption Message" with a link to the "My Account" page if they wish to claim exemption.', 'tax-exemption-woo' );
        ?>
                </p>
                <p>
                    <?php 
        echo esc_html__( '- You can also view/edit each customers AvaTax exemption type on the "Exempt Customers" management page.', 'tax-exemption-woo' );
        ?>
                </p>

                <?php 
        ?>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php 
        echo esc_html__( 'Enable AvaTax Integration', 'tax-exemption-woo' );
        ?></th>
                            <td>
                                <input type="checkbox" style="opacity: 0.5; pointer-events: none;">
                            </td>
                        </tr>
                    </table>

                    <p style="font-size: 18px; margin-top: 0;">
                        <strong><?php 
        echo esc_html__( 'AvaTax integration is available in the PRO version.', 'tax-exemption-woo' );
        ?></strong>
                        <a href="<?php 
        echo esc_html( admin_url() );
        ?>admin.php?page=tax-exemption-woo-pricing&trial=true">
                            <strong><?php 
        echo esc_html__( 'Try it free for 7 days.', 'tax-exemption-woo' );
        ?></strong>
                        </a>
                    </p>

                <?php 
        ?>

            </div>

            <?php 
        submit_button();
        ?>
            
        </form>

        <div id="users" class="tab-panel">
            <h3>
                <?php 
        echo esc_html__( 'Exempt Customers', 'tax-exemption-woo' );
        ?>
                <a href="#" id="add-new-btn" class="page-title-action">
                    <?php 
        echo esc_html__( 'Add New', 'tax-exemption-woo' );
        ?>
                </a>
            </h3>
            
            <!-- Hidden "Add New" Form -->
            <div id="add-new-form" class="new-exempt-form-wrapper" style="display:none;">
            <form id="new-exempt-form" class="new-exempt-form" enctype="multipart/form-data">
                <h3 style="margin-top: 0;"><?php 
        echo esc_html__( 'Add New / Update Customer', 'tax-exemption-woo' );
        ?></h3>
                <p style="margin-top: 0;">
                    <?php 
        echo esc_html__( 'Set or update an existing customer as tax exempt by filling out the form below.', 'tax-exemption-woo' );
        ?>
                </p>
                <!-- Enable Tax Exemption -->
                <div class="tefw-form-field">
                <label for="tefw_exempt"><?php 
        echo esc_html__( 'Enabled', 'tax-exemption-woo' );
        ?>:&nbsp;</label>
                <input type="checkbox" id="tefw_exempt" name="tefw_exempt" value="1" checked>
                <i style="font-size: 12px;">&nbsp;<?php 
        echo esc_html__( 'The default value of tax exemption checkbox on checkout.', 'tax-exemption-woo' );
        ?></i>
                </div>
                <!-- User Fields -->
                <div class="tefw-form-field">
                <label for="username"><?php 
        echo esc_html__( 'Username', 'tax-exemption-woo' );
        ?>:</label>
                <input type="text" id="username" name="username">
                </div>
                <div class="tefw-form-field">
                <label for="name"><?php 
        echo esc_html__( 'Name', 'tax-exemption-woo' );
        ?>:</label>
                <input type="text" id="name" name="name">
                </div>
                <div class="tefw-form-field">
                <label for="reason"><?php 
        echo esc_html__( 'Reason', 'tax-exemption-woo' );
        ?>:</label>
                <input type="text" id="reason" name="reason">
                </div>
                <?php 
        ?>
                <?php 
        wp_nonce_field( 'tefw_add_new_exempt_user', 'tefw_add_new_exempt_user_nonce' );
        ?>

                <div class="tefw-form-field submit-field">
                <input type="submit" value="Submit" class="tefw-submit-button submit-button">
                <input type="button" value="Empty All Fields" class="tefw-clear-button clear-button" style="margin-top: 25px; float: right;">
                </div>
            </form>
            </div>

            <p style="text-align: right; margin-top: -50px;">
                Search: 
                <span style="position: relative; display: inline-block;">
                <input type="text" id="tefw_search_field" placeholder="" />
                <span id="clear-search" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); cursor: pointer;">X</span>
                </span>
            </p>
            <div id="tefw_exempt_users_table"></div>
            <div id="tefw-pagination"></div>
        </div>

        <?php 
    }
    // End if WooCommerce Installed and Active
    ?>

    </div>

	<div class="tax-exemption-settings-sidebar">

            <div class="tax-exemption-settings-sidebar-box">

                <p><a href="https://relywp.com/plugins/tax-exemption-woocommerce/?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar" target="_blank" style="font-size: 15px; font-weight: bold;">Tax Exemption for WooCommerce <?php 
    ?></a></p>

                <p style="font-size: 15px; font-weight: bold;"><?php 
    echo esc_html__( 'Developed by', 'tax-exemption-woo' );
    ?>
                    <a href="https://relywp.com/?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar" target="_blank"
                    title="www.relywp.com">RelyWP</a>
                </p>

            </div>

            <br />

            <?php 
    if ( !tefw_fs()->is_paying_or_trial() ) {
        // Premium only
        ?>
            <div class="tax-exemption-settings-sidebar-box">
                <h3 style="font-weight: bold;"><?php 
        echo esc_html__( 'Want more features? Upgrade to PRO!', 'tax-exemption-woo' );
        ?></h3>
                <p style="font-size: 15px;">
                    - <?php 
        echo esc_html__( 'Custom tax exemption form fields.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Certificate upload field.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Expiration date field.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Show fields on registration form.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Exemption status management.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Only allow exemption for approved users.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Hide the tax exemption fields on checkout.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Only remove tax from specific products.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Only allow exemption for logged in users.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Only allow exemption for selected user roles.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Only allow exemption for selected countries.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Customize the tax class for each user.', 'tax-exemption-woo' );
        ?><br />
                    - <?php 
        echo esc_html__( 'Integration with "AvaTax".', 'tax-exemption-woo' );
        ?><br />
                    <a href="https://relywp.com/plugins/tax-exemption-woocommerce/?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar"
                    target="_blank" style="font-size: 15px;"><?php 
        echo esc_html__( 'Learn more about PRO', 'tax-exemption-woo' );
        ?></a></p>
				<!-- Price -->
				<p style="font-size: 17px; font-weight: bold; color: #333;">
					<?php 
        echo esc_html__( 'Access all features for only', 'tax-exemption-woo' );
        ?> $59!
				</p>
                <p><a href="<?php 
        echo esc_html( admin_url() );
        ?>admin.php?page=tax-exemption-woo-pricing&trial=true"
                    style="font-size: 17px; font-weight: bold;"
                        class="button button-primary"><?php 
        echo esc_html__( 'Try PRO free for 7 days', 'tax-exemption-woo' );
        ?> <span class="dashicons dashicons-arrow-right-alt"
                        style="margin-top: 10px; font-size: 19px;"></span></a></p>
                <p style="font-size: 12px; font-weight: bold; color: #333;">
					<?php 
        echo esc_html__( 'Risk free! 7 day trial & 14 day money-back guarantee.', 'tax-exemption-woo' );
        ?>
				</p>
                <p style="font-size: 12px; font-weight: bold; color: red;">Limited time! 10% discount with code: DASH10</p>
            </div>

            <br />

            <?php 
    }
    ?>

            <?php 
    global $tefw_fs;
    ?>
            <div class="tax-exemption-settings-sidebar-box">
                <h3 style="font-weight: bold;"><?php 
    echo esc_html__( 'Support', 'tax-exemption-woo' );
    ?></h3>
                <p style="font-size: 15px;"><a href="https://relywp.com/plugins/tax-exemption-woocommerce/docs?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar" target="_blank"><?php 
    echo esc_html__( 'View Plugin Documentation', 'tax-exemption-woo' );
    ?></a></p>
                <p style="font-size: 15px;"><?php 
    echo esc_html__( 'Need help? Have a suggestion?', 'tax-exemption-woo' );
    ?>
                <?php 
    ?>
                    <a href="https://wordpress.org/support/plugin/tax-exemption-woo/" target="_blank">
                <?php 
    ?>
                    <?php 
    echo esc_html__( 'Get Support', 'tax-exemption-woo' );
    ?>
                </a></p>
                <?php 
    ?>
            </div>

            <br />

            <div class="tax-exemption-settings-sidebar-box">

                <p style="font-size: 12px; font-weight: bold;"><?php 
    echo esc_html__( 'Check out some of our other plugins:', 'tax-exemption-woo' );
    ?>
                    <br/>
                    - <a href="https://couponaffiliates.com/?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar" target="_blank" title="">Coupon Affiliates for WooCommerce</a>
                    <br/>
                    - <a href="https://relywp.com/plugins/advanced-customer-reports-woocommerce/?utm_campaign=tax-exemption-plugin&utm_source=plugin-settings&utm_medium=sidebar" target="_blank" title="">Advanced Customer Reports for WooCommerce</a>
                    <br/>
                    - <a href="https://wordpress.org/plugins/simple-cloudflare-turnstile/" target="_blank" title="">Simple Cloudflare Turnstile</a> (FREE)
                    <br/>
                    - <a href="https://wordpress.org/plugins/recaptcha-woo/" target="_blank" title="">reCAPTCHA for WooCommerce</a> (FREE)
                    <br/>
                </p>

            </div>


        </div>

    <?php 
}

/* Get select scripts */
function tefw_enqueue_select2_jquery() {
    if ( !isset( $_GET['page'] ) || $_GET['page'] != 'tax-exemption-woo' ) {
        return;
    }
    wp_enqueue_style( 'select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' );
    wp_enqueue_script(
        'select2-js',
        'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
        array('jquery'),
        null,
        true
    );
    wp_enqueue_script(
        'custom-select2-init',
        get_template_directory_uri() . '/js/custom-select2-init.js',
        array('select2-js'),
        null,
        true
    );
}

add_action( 'admin_enqueue_scripts', 'tefw_enqueue_select2_jquery' );