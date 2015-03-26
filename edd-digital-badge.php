<?php
/*
Plugin Name: Easy Digital Downloads - Digital Badge
Plugin URI: https://easydigitaldownloads.com
Description: Identify products as digital and subscription
Version: 1.0
Author: Chris Klosowski / Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Text Domain: edd-db-txt
*/

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Digital_Badge' ) ) {

	/**
	 * Main EDD_Digital_Badge class
	 *
	 * @since       1.0
	 */
	class EDD_Digital_Badge {

		/**
		 * @var         EDD_Digital_Badge $instance The one true EDD_Digital_Badge
		 * @since       1.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0
		 * @return      object self::$instance The one true EDD_Digital_Badge
		 */
		public static function instance() {
			if( !self::$instance ) {
				self::$instance = new EDD_Digital_Badge();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_Digital_Badge_VER', '1.0' );

			// Plugin path
			define( 'EDD_Digital_Badge_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_Digital_Badge_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function includes() {
			// Include scripts
			require_once EDD_Digital_Badge_DIR . 'includes/scripts.php';
			require_once EDD_Digital_Badge_DIR . 'includes/functions.php';
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 *
		 */
		private function hooks() {
			// Register settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDD_Digital_Badge_DIR . '/languages/';
			$lang_dir = apply_filters( 'EDD_Digital_Badge_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-db-txt' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-db-txt', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/edd-db-txt/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-db-txt/ folder
				load_textdomain( 'edd-db-txt', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-db-txt/languages/ folder
				load_textdomain( 'edd-db-txt', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-db-txt', false, $lang_dir );
			}
		}


		/**
		 * Add settings
		 *
		 * @access      public
		 * @since       1.0
		 * @param       array $settings The existing EDD settings array
		 * @return      array The modified EDD settings array
		 */
		public function settings( $settings ) {
			$new_settings = array(
				array(
					'id'    => 'EDD_Digital_Badge_settings',
					'name'  => '<strong>' . __( 'Digital Badge Settings', 'edd-db-txt' ) . '</strong>',
					'desc'  => __( 'Configure Plugin Name Settings', 'edd-db-txt' ),
					'type'  => 'header',
				),
				array(
					'id'    => 'EDD_Digital_Badge_badge_text',
					'name'  => __( 'Download Badge Text', 'edd-db-txt' ),
					'desc'  => __( 'The text string you want to show by products marked as digital', 'edd-db-txt' ),
					'type'  => 'text',
					'std'   => edd_db_get_badge_string()
				)
			);

			if ( class_exists( 'EDD_Recurring' ) ) {
				$new_settings[] = array(
					'id'    => 'EDD_Digital_Badge_subscription_text',
					'name'  => __( 'Subscription Badge Text', 'edd-db-txt' ),
					'desc'  => __( 'The text string you want to show by products marked as subscriptions', 'edd-db-txt' ),
					'type'  => 'text',
					'std'   => edd_db_get_subscription_string()
				);
			}

			return array_merge( $settings, $new_settings );
		}

	}


	/**
	 * The main function responsible for returning the one true EDD_Digital_Badge
	 * instance to functions everywhere
	 *
	 * @since       1.0
	 * @return      \EDD_Digital_Badge The one true EDD_Digital_Badge
	 */
	function EDD_Digital_Badge_load() {
		if( ! class_exists( 'Easy_Digital_Downloads' ) ) {

			$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$activation = $activation->run();
			return EDD_Digital_Badge::instance();
		} else {
			return EDD_Digital_Badge::instance();
		}
	}

	add_action( 'plugins_loaded', 'EDD_Digital_Badge_load' );

} // End if class_exists check
