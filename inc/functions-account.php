<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
$settings = get_option( 'tefw_settings' );
if ( $settings['tefw_account_enable'] ?? 1 ) {
    /*
     * Register new endpoint to use for My Account page
     *
     * @return null
     *
     */
    function tefw_add_my_account_endpoint() {
        add_rewrite_endpoint( 'tax-exemption', EP_ROOT | EP_PAGES );
        // Resave Permalinks or it will give 404 error, make it only do once
        if ( get_option( 'tefw_flush_rewrite_rules' ) !== true ) {
            flush_rewrite_rules();
            update_option( 'tefw_flush_rewrite_rules', true );
        }
    }

    add_action( 'init', 'tefw_add_my_account_endpoint' );
    /*
     *
     * Add new query var
     *
     * @param array $vars
     *
     * @return array
     *
     */
    function tefw_query_vars(  $vars  ) {
        $vars[] = 'tax-exemption';
        return $vars;
    }

    add_filter( 'query_vars', 'tefw_query_vars', 0 );
    /*
     * Inserting new tab to account menu
     *
     * @param array $items
     *
     * @return array
     *
     */
    function tefw_add_my_account_menu_items(  $items  ) {
        $settings = get_option( 'tefw_settings' );
        $tefw_user_roles_enable = ( isset( $settings['tefw_user_roles_enable'] ) && $settings['tefw_user_roles_enable'] ? $settings['tefw_user_roles_enable'] : 0 );
        if ( !tefw_user_enabled( 'roles' ) ) {
            return $items;
            // If not exempt
        }
        $items['tax-exemption'] = ( isset( $settings['tefw_text_title'] ) && $settings['tefw_text_title'] ? esc_html( $settings['tefw_text_title'] ) : esc_html__( 'Tax Exemption', 'tax-exemption-woo' ) );
        return $items;
    }

    add_filter( 'woocommerce_account_menu_items', 'tefw_add_my_account_menu_items' );
    /*
     * Move Endpoint
     *
     * @return array
     *
     */
    add_filter( 'woocommerce_account_menu_items', 'tefw_move_my_endpoint' );
    function tefw_move_my_endpoint(  $items  ) {
        $settings = get_option( 'tefw_settings' );
        $tefw_user_roles_enable = ( isset( $settings['tefw_user_roles_enable'] ) && $settings['tefw_user_roles_enable'] ? $settings['tefw_user_roles_enable'] : 0 );
        if ( !tefw_user_enabled( 'roles' ) ) {
            return $items;
            // If not exempt
        }
        // Remove the logout menu item.
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        // Insert the tax-exemption endpoint.
        $items['tax-exemption'] = ( isset( $settings['tefw_text_title'] ) && $settings['tefw_text_title'] ? esc_html( $settings['tefw_text_title'] ) : esc_html__( 'Tax Exemption', 'tax-exemption-woo' ) );
        // Insert back the logout item.
        $items['customer-logout'] = $logout;
        return $items;
    }

    /*
     * Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
     *
     * @return mixed
     *
     */
    function tefw_endpoint_content() {
        if ( !tefw_user_enabled( 'roles' ) ) {
            return;
            // If not exempt
        }
        $user = wp_get_current_user();
        $settings = get_option( 'tefw_settings' );
        $tax_exempt = get_user_meta( $user->ID, 'tefw_exempt', true );
        $tax_exempt_name = get_user_meta( $user->ID, 'tefw_exempt_name', true );
        $tax_exempt_reason = get_user_meta( $user->ID, 'tefw_exempt_reason', true );
        ?>

        <h2 style="margin-top: 0;"><?php 
        echo esc_attr( ( isset( $settings['tefw_text_title'] ) && $settings['tefw_text_title'] ? esc_html( $settings['tefw_text_title'] ) : esc_html__( 'Tax Exemption', 'tax-exemption-woo' ) ) );
        ?></h2>

        <?php 
        if ( isset( $settings['tefw_text_description'] ) ) {
            ?>
            <p><?php 
            echo esc_attr( $settings['tefw_text_description'] );
            ?></p>
        <?php 
        }
        ?>

        <?php 
        ?>

        <?php 
        $tefw_name_show = ( isset( $settings['tefw_name_show'] ) ? $settings['tefw_name_show'] : 1 );
        $tefw_reason_show = ( isset( $settings['tefw_reason_show'] ) ? $settings['tefw_reason_show'] : 1 );
        $tefw_name_required = ( isset( $settings['tefw_name_required'] ) ? $settings['tefw_name_required'] : 1 );
        $tefw_reason_required = ( isset( $settings['tefw_reason_required'] ) ? $settings['tefw_reason_required'] : 1 );
        ?>

        <form method="post" enctype="multipart/form-data" class="tefw-account-form">
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <input type="checkbox" name="tefw_exempt" id="tefw_exempt" value="1" <?php 
        checked( 1, $tax_exempt, true );
        ?> />
                <label for="tefw_exempt" style="display: inline;"><?php 
        echo esc_attr( ( isset( $settings['tefw_text_checkbox'] ) && $settings['tefw_text_checkbox'] ? esc_html( $settings['tefw_text_checkbox'] ) : esc_html__( 'I want to claim tax exemption', 'tax-exemption-woo' ) ) );
        ?></label>
            </p>
            <?php 
        if ( $tefw_name_show ) {
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="tefw_exempt_name"><?php 
            echo esc_attr( ( isset( $settings['tefw_text_name'] ) && $settings['tefw_text_name'] ? esc_html( $settings['tefw_text_name'] ) : esc_html__( 'Name of person to which Tax Exemption applies', 'tax-exemption-woo' ) ) );
            ?>:</label>
                <input type="text" name="tefw_exempt_name" id="tefw_exempt_name" value="<?php 
            echo esc_attr( $tax_exempt_name );
            ?>" <?php 
            if ( $tefw_name_required ) {
                ?>required<?php 
            }
            ?> /><br />
            </p>
            <?php 
        }
        ?>
            <?php 
        if ( $tefw_reason_show ) {
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="tefw_exempt_reason"><?php 
            echo esc_attr( ( isset( $settings['tefw_text_reason'] ) && $settings['tefw_text_reason'] ? esc_html( $settings['tefw_text_reason'] ) : esc_html__( 'Reason for Tax Exemption', 'tax-exemption-woo' ) ) );
            ?>:</label>
                <input type="text" name="tefw_exempt_reason" id="tefw_exempt_reason" value="<?php 
            echo esc_attr( $tax_exempt_reason );
            ?>" <?php 
            if ( $tefw_reason_required ) {
                ?>required<?php 
            }
            ?> /><br />
            </p>
            <?php 
        }
        ?>

            <?php 
        ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php 
        wp_nonce_field( 'tefw_update_user_meta', 'tefw_nonce' );
        ?>
                <input type="submit" value="<?php 
        esc_html_e( 'Update', 'tax-exemption-woo' );
        ?>" />
            </p>
        </form>

        <?php 
        ?>

        <?php 
    }

    add_action( 'woocommerce_account_tax-exemption_endpoint', 'tefw_endpoint_content' );
    /**
     *
     * Save the form data if the nonce is valid and the form was submitted
     *
     */
    function tefw_save_form_submit() {
        if ( !isset( $_POST['tefw_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tefw_nonce'] ) ), 'tefw_update_user_meta' ) || !is_account_page() ) {
            return;
        }
        $fields_info = "";
        $settings = get_option( 'tefw_settings', array() );
        $user_id = get_current_user_id();
        // get username
        $user_info = get_userdata( $user_id );
        $username = $user_info->user_login;
        $edit_link = admin_url( 'admin.php?page=tax-exemption-woo&tab=users&search=' . $username );
        if ( isset( $_POST['tefw_exempt'] ) ) {
            update_user_meta( $user_id, 'tefw_exempt', sanitize_text_field( $_POST['tefw_exempt'] ) );
        } else {
            update_user_meta( $user_id, 'tefw_exempt', '' );
        }
        if ( isset( $_POST['tefw_exempt_name'] ) ) {
            update_user_meta( $user_id, 'tefw_exempt_name', sanitize_text_field( $_POST['tefw_exempt_name'] ) );
            $fields_info .= "Name: " . sanitize_text_field( $_POST['tefw_exempt_name'] ) . "<br>";
        }
        if ( isset( $_POST['tefw_exempt_reason'] ) ) {
            update_user_meta( $user_id, 'tefw_exempt_reason', sanitize_text_field( $_POST['tefw_exempt_reason'] ) );
            $fields_info .= "Reason: " . sanitize_text_field( $_POST['tefw_exempt_reason'] ) . "<br>";
        }
        if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_expiration_enable'] ) ? $settings['tefw_expiration_enable'] : 1 )) ) {
            // Premium only
            update_user_meta( $user_id, 'tefw_exempt_expiration', sanitize_text_field( $_POST['tefw_exempt_expiration'] ) );
            $fields_info .= "Expiration: " . sanitize_text_field( $_POST['tefw_exempt_expiration'] ) . "<br>";
        }
        if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_certificate_enable'] ) ? $settings['tefw_certificate_enable'] : 1 )) ) {
            // Premium only
            $tefw_certificate_account_enable = ( isset( $settings['tefw_certificate_account_enable'] ) ? $settings['tefw_certificate_account_enable'] : 1 );
            if ( $tefw_certificate_account_enable ) {
                if ( isset( $_FILES['tefw_exempt_file'] ) ) {
                    do_action( 'tefw_hook_account_upload_file', $user_id );
                }
            }
        }
        if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_custom_fields_enable'] ) ? $settings['tefw_custom_fields_enable'] : 1 )) ) {
            // Premium only
            $custom_fields = get_option( 'tefw_settings', array() );
            if ( !empty( $custom_fields['custom_fields'] ) ) {
                foreach ( $custom_fields['custom_fields'] as $field ) {
                    $field_id = ( isset( $field['id'] ) ? $field['id'] : '' );
                    if ( isset( $_POST['tefw_exempt_custom_' . $field_id] ) ) {
                        $field_value = sanitize_text_field( $_POST['tefw_exempt_custom_' . $field_id] );
                        update_user_meta( $user_id, 'tefw_exempt_custom_' . $field_id, $field_value );
                        $fields_info .= $field['label'] . ": " . $field_value . "<br>";
                    } else {
                        update_user_meta( $user_id, 'tefw_exempt_custom_' . $field_id, '' );
                    }
                }
            }
        }
        if ( isset( $settings['tefw_approval_enable'] ) && $settings['tefw_approval_enable'] === '1' ) {
            // Send email notification to the admin if the user is not approved, and set the status to "pending"
            $tefw_exempt_status = get_user_meta( $user_id, 'tefw_exempt_status', true );
            if ( isset( $_POST['tefw_exempt'] ) && (empty( $tefw_exempt_status ) || $tefw_exempt_status == 'expired' || $tefw_exempt_status == 'declined') ) {
                // Set tax exemption status to "pending"
                update_user_meta( $user_id, 'tefw_exempt_status', 'pending' );
                // Send email notification to the admin
                $admin_email = get_option( 'admin_email' );
                $subject = esc_html__( 'Tax Exemption Request', 'tax-exemption-woo' );
                // Show Fields
                $message = sprintf( esc_html__( 'The user %s has requested tax exemption.<br><br>', 'tax-exemption-woo' ), $username );
                $message .= $fields_info;
                $message .= esc_html__( '<br>Please review and update the status in the user profile.<br><br>', 'tax-exemption-woo' );
                $message .= sprintf( esc_html__( 'Click <a href="%s">here</a> to edit and manage their tax exemption status.', 'tax-exemption-woo' ), $edit_link );
                wp_mail( $admin_email, $subject, $message );
            }
        } else {
            update_user_meta( $user_id, 'tefw_exempt_status', 'approved' );
        }
        do_action( 'after_tefw_save_form_submit', $user_id );
    }

    add_action( 'template_redirect', 'tefw_save_form_submit' );
    /**
     *
     * Ajax handler for deleting the tax exemption file
     *
     * @return json
     *
     */
    function tefw_ajax_delete_tax_exemption_file() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( esc_html__( 'Access denied', 'tax-exemption-woo' ) );
        }
        if ( !check_ajax_referer( 'update_tefw_nonce', 'nonce', false ) ) {
            if ( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_tax_exemption_file' ) ) {
                wp_send_json_error( esc_html__( 'Access denied', 'tax-exemption-woo' ) );
            }
        }
        $user_id = intval( $_POST['user_id'] );
        if ( $user_id == get_current_user_id() || current_user_can( 'manage_options' ) ) {
            if ( isset( $_POST['file_name'] ) && $_POST['file_name'] ) {
                $file_name = sanitize_text_field( $_POST['file_name'] );
                $current_file = get_user_meta( $user_id, 'tefw_exempt_file', true );
                if ( $current_file == $file_name && $file_name ) {
                    // Delete the file
                    $uploads = wp_get_upload_dir();
                    $file_path = $uploads['basedir'] . '/tax-exemption/' . $file_name;
                    if ( file_exists( $file_path ) ) {
                        unlink( $file_path );
                        wp_delete_file( $file_path );
                    }
                    update_user_meta( $user_id, 'tefw_exempt_file', '' );
                    wp_send_json_success( esc_html__( 'File deleted successfully.', 'tax-exemption-woo' ) );
                } else {
                    wp_send_json_error( esc_html__( 'File not found for user.', 'tax-exemption-woo' ) );
                }
            } else {
                wp_send_json_success( esc_html__( 'File deleted successfully.', 'tax-exemption-woo' ) );
            }
        }
    }

    add_action( 'wp_ajax_delete_tax_exemption_file', 'tefw_ajax_delete_tax_exemption_file' );
    add_action( 'wp_ajax_nopriv_delete_tax_exemption_file', 'tefw_ajax_delete_tax_exemption_file' );
    /**
     * Add custom fields to the tax exemption form
     *
     * @param $user object
     *
     * @return mixed
     *
     */
    add_action( 'tefw_hook_account_custom_fields', 'tefw_account_custom_fields' );
    function tefw_account_custom_fields(  $user = ""  ) {
        $settings = get_option( 'tefw_settings', array() );
        $custom_fields = get_option( 'tefw_settings', array() );
        // Display custom fields
        if ( tefw_fs()->can_use_premium_code__premium_only() && (( isset( $settings['tefw_custom_fields_enable'] ) ? $settings['tefw_custom_fields_enable'] : 1 )) ) {
            // Premium only
            if ( !empty( $custom_fields['custom_fields'] ) ) {
                foreach ( $custom_fields['custom_fields'] as $field ) {
                    $field_id = ( isset( $field['id'] ) ? $field['id'] : '' );
                    $field_label = ( isset( $field['label'] ) ? $field['label'] : '' );
                    if ( $field_id && $field_label ) {
                        $field_type = ( isset( $field['type'] ) ? $field['type'] : '' );
                        $field_options = ( isset( $field['options'] ) ? $field['options'] : '' );
                        $field_required = ( isset( $field['required'] ) && $field['required'] == 1 ? true : false );
                        if ( isset( $user ) && isset( $user->ID ) ) {
                            $field_value = get_user_meta( $user->ID, 'tefw_exempt_custom_' . $field_id, true );
                        } else {
                            $field_value = '';
                        }
                        ?>

                        <div class="tefw-form-field">
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="tefw_exempt_custom_<?php 
                        echo esc_html( $field_id );
                        ?>"><?php 
                        echo esc_html( $field_label );
                        ?>:
                                <?php 
                        if ( $field_required ) {
                            ?>
                                    <span class="required">*</span>
                                <?php 
                        }
                        ?>
                            </label>
                            <?php 
                        if ( $field_type === 'textarea' ) {
                            ?>
                                <textarea name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>"><?php 
                            echo esc_textarea( $field_value );
                            ?></textarea>
                            <?php 
                        } elseif ( $field_type === 'select' ) {
                            ?>
                                <select name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>">
                                    <?php 
                            if ( !empty( $field_options ) ) {
                                $options = explode( "\n", $field_options );
                                foreach ( $options as $option ) {
                                    $option_value = trim( $option );
                                    $selected = selected( $field_value, $option_value, false );
                                    echo '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_value ) . '</option>';
                                }
                            }
                            ?>
                                </select>
                            <?php 
                        } elseif ( $field_type === 'radio' ) {
                            ?>
                                <?php 
                            if ( !empty( $field_options ) ) {
                                $options = explode( "\n", $field_options );
                                foreach ( $options as $option ) {
                                    $option_value = trim( $option );
                                    $checked = checked( $field_value, $option_value, false );
                                    echo '<input type="radio" name="tefw_exempt_custom_' . esc_attr( $field_id ) . '" id="tefw_exempt_custom_' . esc_attr( $field_id ) . '" value="' . esc_attr( $option_value ) . '" ' . $checked . ' /> ' . esc_html( $option_value ) . '<br />';
                                }
                            }
                            ?>
                            <?php 
                        } elseif ( $field_type === 'checkbox' ) {
                            ?>
                                &nbsp;<input type="checkbox" name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" value="1" <?php 
                            checked( $field_value, true );
                            ?> />
                            <?php 
                        } elseif ( $field_type === 'number' ) {
                            ?>
                                <input type="number" name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" value="<?php 
                            echo esc_attr( $field_value );
                            ?>" />
                            <?php 
                        } elseif ( $field_type === 'date' ) {
                            ?>
                                <input type="date" name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" value="<?php 
                            echo esc_attr( $field_value );
                            ?>" />
                            <?php 
                        } else {
                            ?>
                                <input type="text" name="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" id="tefw_exempt_custom_<?php 
                            echo esc_html( $field_id );
                            ?>" value="<?php 
                            echo esc_attr( $field_value );
                            ?>" />
                            <?php 
                        }
                        ?>

                        </p>
                        </div>

                    <?php 
                    }
                }
            }
        }
    }

}
/**
*
* Ajax function to get the current user info
*
* @return json
*
*/
add_action( 'wp_ajax_tefw_get_current_user_info', 'tefw_get_current_user_info' );
add_action( 'wp_ajax_nopriv_tefw_get_current_user_info', 'tefw_get_current_user_info' );
function tefw_get_current_user_info() {
    $response = array(
        'success' => false,
        'data'    => array(),
    );
    $current_user = wp_get_current_user();
    if ( $current_user->ID !== 0 ) {
        $response['success'] = true;
        $response['data']['username'] = $current_user->user_login;
        $response['data']['customer_id'] = $current_user->ID;
    } else {
        $response['success'] = true;
        $id = WC()->session->get_customer_id();
        $id = substr( $id, -10 );
        $response['data']['username'] = 'guest';
        $response['data']['customer_id'] = '0';
    }
    wp_send_json( $response );
}

/*
* Check if the current user has access to the tax exemption form
*
* @return bool
*/
function tefw_user_enabled(  $type = 'all'  ) {
    $is_exempt = true;
    $settings = get_option( 'tefw_settings' );
    $is_exempt = apply_filters( 'tefw_is_user_enabled', $is_exempt );
    return $is_exempt;
}
