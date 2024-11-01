<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
// Function to get users with the meta 'tefw_exempt' set to 1
function get_tefw_exempt_users(  $page = 1, $limit = 20, $search = ''  ) {
    // Check if admin
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }
    // where meta key tefw_exempt is true or tefw_exempt_name not empty
    $args = array(
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'tefw_exempt',
                'value'   => 1,
                'compare' => '==',
            ),
            array(
                'key'     => 'tefw_exempt_name',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'number'     => $limit,
        'paged'      => $page,
    );
    if ( !empty( $search ) ) {
        $args['search'] = '*' . $search . '*';
    }
    $settings = get_option( 'tefw_settings', array() );
    $tefw_certificate_enable = ( isset( $settings['tefw_certificate_enable'] ) ? $settings['tefw_certificate_enable'] : 1 );
    $approval_enable = ( isset( $settings['tefw_approval_enable'] ) ? $settings['tefw_approval_enable'] : 0 );
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $total_users = $user_query->get_total();
    $output = '<table style="margin: 0;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th title="' . esc_html__( 'Is tax exemption currently enabled for this customer?', 'tax-exemption-woo' ) . '">' . esc_html__( 'Enabled', 'tax-exemption-woo' ) . '</th>';
    $output .= '<th>' . esc_html__( 'Username', 'tax-exemption-woo' ) . '</th>';
    $output .= '<th>' . esc_html__( 'Email', 'tax-exemption-woo' ) . '</th>';
    $output .= '<th>' . esc_html__( 'Exempt Name', 'tax-exemption-woo' ) . '</th>';
    $output .= '<th>' . esc_html__( 'Exempt Reason', 'tax-exemption-woo' ) . '</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    foreach ( $users as $user ) {
        $user_id = $user->ID;
        $username = $user->user_login;
        $email = $user->user_email;
        $tefw_exempt_status = get_user_meta( $user_id, 'tefw_exempt_status', true );
        $tefw_exempt_name = get_user_meta( $user_id, 'tefw_exempt_name', true );
        $tefw_exempt_reason = get_user_meta( $user_id, 'tefw_exempt_reason', true );
        $exemption_file = get_user_meta( $user_id, 'tefw_exempt_file', true );
        $file_url = '';
        if ( $exemption_file ) {
            $uploads_dir = wp_upload_dir();
            // does file exist?
            if ( file_exists( $uploads_dir['basedir'] . '/tax-exemption/' . $exemption_file ) ) {
                $file_url = esc_url( $uploads_dir['baseurl'] . '/tax-exemption/' . $exemption_file );
            }
        }
        $output .= '<tr id="user-' . esc_html( $user_id ) . '">';
        $output .= "<td class='tefw_exempt'><input type='checkbox' name='tefw_exempt'\r\n        value='" . esc_html( $user_id ) . "' " . checked( get_user_meta( $user_id, 'tefw_exempt', true ), 1, false ) . " disabled></td>";
        $output .= "<td class='tefw_exempt_username'><a href='" . esc_url( admin_url( 'user-edit.php?user_id=' . esc_html( $user_id ) ) ) . "#tefw_fields' target='_blank'>" . esc_html( $username ) . "</a></td>";
        $output .= "<td>" . esc_html( $email ) . "</td>";
        $output .= "<td class='tefw_exempt_name'>" . esc_html( $tefw_exempt_name ) . "</td>";
        $output .= "<td class='tefw_exempt_reason'>" . esc_html( $tefw_exempt_reason ) . "</td>";
        // Edit link that opens the add new form and pre-populates the fields
        $output .= "<td class='tefw-col-actions'><a href='#' class='button tefw-edit-user' data-user-id='" . esc_html( $user_id ) . "'>" . esc_html__( 'Edit Customer', 'tax-exemption-woo' ) . "</a></td>";
        $output .= '</tr>';
    }
    $output .= '</tbody>';
    $output .= '</table>';
    return [
        'html'  => $output,
        'total' => $total_users,
    ];
}

// Create an AJAX endpoint to fetch users
add_action( 'wp_ajax_get_tefw_exempt_users', function () {
    $page = ( isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1 );
    $search = ( isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '' );
    $limit = 20;
    $result = get_tefw_exempt_users( $page, $limit, $search );
    wp_send_json( $result );
} );
// Create an AJAX endpoint to add a new user
add_action( 'wp_ajax_add_new_exempt_user', function () {
    // Settings
    $settings = get_option( 'tefw_settings', array() );
    // Admin check
    if ( !current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'You do not have permission to do this.' );
        return;
    }
    // Check Nonce
    check_ajax_referer( 'tefw_add_new_exempt_user', 'tefw_add_new_exempt_user_nonce' );
    // Validate nonce, user permissions, etc. if necessary
    $exempt = ( isset( $_POST['exempt'] ) && $_POST['exempt'] ? 1 : 0 );
    $status = ( isset( $_POST['tefw_exempt_status'] ) ? sanitize_text_field( $_POST['tefw_exempt_status'] ) : '' );
    $username = ( isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '' );
    $name = ( isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '' );
    $reason = ( isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '' );
    $expiration = ( isset( $_POST['expiration'] ) ? sanitize_text_field( $_POST['expiration'] ) : '' );
    $tax_class = ( isset( $_POST['tax_class'] ) ? sanitize_text_field( $_POST['tax_class'] ) : '' );
    $tefw_exempt = ( isset( $_POST['tefw_exempt'] ) ? sanitize_text_field( $_POST['tefw_exempt'] ) : '' );
    $wc_avatax_tax_exemption = ( isset( $_POST['wc_avatax_tax_exemption'] ) ? sanitize_text_field( $_POST['wc_avatax_tax_exemption'] ) : '' );
    $user = get_user_by( 'login', $username );
    if ( $user ) {
        // Update user meta fields
        update_user_meta( $user->ID, 'tefw_exempt', $tefw_exempt );
        update_user_meta( $user->ID, 'tefw_exempt_status', $status );
        update_user_meta( $user->ID, 'tefw_exempt_name', $name );
        update_user_meta( $user->ID, 'tefw_exempt_reason', $reason );
        // Custom Fields
        $settings = get_option( 'tefw_settings' );
        $custom_fields = $settings['custom_fields'] ?? array();
        foreach ( $custom_fields as $field ) {
            $label = $field['label'] ?? '';
            $id = $field['id'] ?? '';
            if ( $label ) {
                $field_name = 'tefw_exempt_custom_' . sanitize_title( str_replace( ' ', '_', $id ) );
                $field_value = ( isset( $_POST[$field_name] ) ? sanitize_text_field( $_POST[$field_name] ) : '' );
                update_user_meta( $user->ID, 'tefw_exempt_custom_' . $id, $field_value );
            }
        }
        // Update Status
        $tefw_exempt_status = ( isset( $_POST['tefw_exempt_status'] ) ? sanitize_text_field( $_POST['tefw_exempt_status'] ) : '' );
        if ( $tefw_exempt_status ) {
            update_user_meta( $user->ID, 'tefw_exempt_status', $tefw_exempt_status );
        }
        // return the username escaped
        wp_send_json_success( esc_html( $username ) );
    } else {
        wp_send_json_error( 'User not found.' );
    }
} );