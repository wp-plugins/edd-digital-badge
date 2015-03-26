<?php
/**
 * Load frontend scripts
 *
 * @since       1.0
 * @return      void
 */
function edd_db_load_scripts( $hook ) {
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_style( 'edd_db_css', EDD_Digital_Badge_URL . 'assets/styles' . $suffix . '.css' );
}
add_action( 'wp_enqueue_scripts', 'edd_db_load_scripts' );
