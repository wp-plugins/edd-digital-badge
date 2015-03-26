<?php

if ( ! defined( 'ABSPATH' ) ) {
	return; // Silence is Golden
}

/**
 * Register saving the settings
 *
 * @since  1.0
 * @param  array $fields The array of settings to save
 * @return array         Array with our settings added
 */
function edd_db_register_metabox_input( $fields ) {
	$fields[] = '_edd_db_display_badge';

	if ( class_exists( 'EDD_Recurring' ) ) {
		$fields[] = '_edd_db_display_subscription_badge';
	}

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_db_register_metabox_input', 10, 1 );

/**
 * Display the checkboxes on the download edit screen
 *
 * @since  1.0
 * @param  int $post_id The Post ID being edited
 * @return string           Output of the string of settings
 */
function edd_db_display_metbox_input( $post_id ) {
	if ( empty( $post_id ) ) {
		return;
	}

	$post_id = absint( $post_id );
	$current_setting = get_post_meta( $post_id, '_edd_db_display_badge', true );
	$output  = '<p><strong>' . __( 'Product Badges', 'edd-db-txt' ) . '</strong></p>';
	$output .= '<p><input' . checked( '1', $current_setting, false ) . ' type="checkbox" id="_edd_db_display_badge" name="_edd_db_display_badge" value="1" />';
	$output .= '<label for="_edd_db_display_badge">' . __( 'Display an indication that the product is fulfilled via download', 'edd-db-txt' ) . '</label></p>';

	if ( class_exists( 'EDD_Recurring' ) && EDD_Recurring::is_recurring( $post_id ) ) {
		$current_setting = get_post_meta( $post_id, '_edd_db_display_subscription_badge', true );
		$output .= '<p><input' . checked( '1', $current_setting, false ) . ' type="checkbox" id="_edd_db_display_subscription_badge" name="_edd_db_display_subscription_badge" value="1" />';
		$output .= '<label for="_edd_db_display_subscription_badge">' . __( 'Display an indication that the product is a subscription', 'edd-db-txt' ) . '</label></p>';
	}

	echo $output;
}
add_action( 'edd_meta_box_settings_fields', 'edd_db_display_metbox_input', 10, 1 );

/**
 * Return the digital badge with the html wrapper
 *
 * @since  1.0
 * @return string The full string return of the digital badge
 */
function edd_db_badge_string() {
	$string = '<span class="edd-db-badge">' . edd_db_get_badge_string() . '</span>';

	return apply_filters( 'edd_db_badge_string', $string );
}

/**
 * Get the digital badge string, without the html wrapper
 *
 * @since  1.0
 * @return string The digital badge string
 */
function edd_db_get_badge_string() {
	$badge_text = edd_get_option( 'EDD_Digital_Badge_badge_text', '[' . __( 'digital', 'edd-db-txt' ) . ']' );

	return apply_filters( 'edd_db_default_badge_string', $badge_text );
}

/**
 * Return the subscription badge with the html wrapper
 *
 * @since  1.0
 * @return string The full string return of the subscription abdge
 */
function edd_db_subscription_string() {
	$string = '<span class="edd-db-badge">' . edd_db_get_subscription_string() . '</span>';

	return apply_filters( 'edd_db_badge_string', $string );
}

/**
 * Get the subscription badge string without the wrapper
 *
 * @since  1.0
 * @return string The subscription badge string
 */
function edd_db_get_subscription_string() {
	$badge_text = edd_get_option( 'EDD_Digital_Badge_subscription_text', '[' . __( 'subscription', 'edd-db-txt' ) . ']' );

	return apply_filters( 'edd_db_default_subscription_string', $badge_text );
}

/**
 * Is the download supposed to have a digial badge
 *
 * @since  1.0
 * @param  integer $download_id The Download ID
 * @return bool                 If this download gets the digital badge
 */
function edd_db_is_digital_download( $download_id = 0 ) {
	if ( empty( $download_id ) ) {
		return false;
	}

	$display_badge = get_post_meta( $download_id, '_edd_db_display_badge', true );

	$is_digital = $display_badge === '1' ? true : false;

	return apply_filters( 'edd_db_is_digital_download', $is_digital, $download_id );
}

/**
 * Is the downlaod supposed to have a subscription badge
 *
 * @since  1.0
 * @param  integer $download_id The Download ID
 * @return bool                 If this download gets the subscription adge
 */
function edd_db_is_subscription_download( $download_id = 0 ) {
	if ( empty( $download_id ) ) {
		return false;
	}

	$display_badge = get_post_meta( $download_id, '_edd_db_display_subscription_badge', true );

	$is_subscription = $display_badge === '1' ? true : false;

	return apply_filters( 'edd_db_is_subscription_download', $is_subscription, $download_id );
}

/**
 * Append the title of the download with the appropriate badges
 *
 * @since  1.0
 * @param  string $title   The current post title
 * @param  int    $post_id The post ID
 * @return string          The string with any badges appended
 */
function edd_db_append_title( $title, $post_id ) {
	if ( is_admin() || 'download' !== get_post_type( $post_id ) || edd_is_checkout() ) {
		return $title;
	}

	$show_badges = apply_filters( 'edd_db_apply_badges', true );

	if ( ! $show_badges ) { return $title; }

	if ( edd_db_is_digital_download( $post_id ) ) {
		$badge  = edd_db_badge_string();
		$title .= $badge;
	}

	if ( edd_db_is_subscription_download( $post_id ) ) {
		$badge  = edd_db_subscription_string();
		$title .= $badge;
	}

	return $title;
}
add_filter( 'the_title', 'edd_db_append_title', 10, 2 );

/**
 * Add the badges to the checkout
 *
 * @since  1.0
 * @param  array $item The Cart Item array
 * @return void
 */
function edd_db_add_badge_column_checkout( $item ) {
	$is_digital      = edd_db_is_digital_download( $item['id'] );
	$is_subscription = edd_db_is_subscription_download( $item['id'] );

	if ( ! $is_digital && ! $is_subscription ) {
		return;
	}

	$output  = '<td class="edd-db-checkout-cell">';
	if ( $is_digital ) {
		$output .= edd_db_badge_string();
	}

	if ( $is_subscription ) {
		$output .= edd_db_subscription_string();
	}
	$output .= '</td>';

	echo $output;
}
add_action( 'edd_checkout_table_body_last', 'edd_db_add_badge_column_checkout', 99, 1 );

/**
 * Add the column to the checkout
 *
 * @since  1.0
 * @return void
 */
function edd_db_add_badge_column_checkout_header() {
	echo '<th class="edd_cart_badges">' . __( 'Notes', 'edd' ) . '</th>';
}
add_action( 'edd_checkout_table_header_last', 'edd_db_add_badge_column_checkout_header', 99 );

