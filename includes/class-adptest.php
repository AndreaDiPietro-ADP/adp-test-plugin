<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugin/includes
 */

// Exit if accessed directly.
defined( 'WPINC' ) || die;


require __DIR__ . '/admin/options-page-trait.php';
require __DIR__ . '/admin/options-callbacks-trait.php';
require __DIR__ . '/admin/meta-boxes-trait.php';
require __DIR__ . '/public/screens-callables.php';
require __DIR__ . '/cpts-trait.php';
require __DIR__ . '/taxonomy-trait.php';

if ( ! class_exists( 'ADPTest' ) ) :

	/**
         * Singleton class.
         */
	final class ADPTest {

		use ADPTestOptionsSettings;
		use ADPTestOptionsCB;
		use ADPTestCPTS;
		use ADPTestMetaBox;
		use ADPTestPublicSceensCallables;
		use ADPTestTaxonomy;

		/**
		 * uses many variables, several of which can be filtered to
		 * customize the way it operates. Most of these variables are stored in a
		 * private array that gets updated with the help of PHP magic methods.
		 *
		 * This is a precautionary measure, to avoid potential errors produced by
		 * unanticipated direct manipulation of run-time data.
		 *
		 * @var array
		 */
		private $data;

		/** Singleton ************************************************************ */

		/**
		 * Main Instance
		 *
		 * Please load it only one time
		 *
		 * Insures that only one instance of this classs exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @staticvar object $instance
		 * @see adptest()
		 * @return ADPTest The one true ADPTest
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication.
			static $instance = null;

			// Only run these methods if they haven't been ran previously.
			if ( null === $instance ) :
				$instance = new ADPTest();
				$instance->setup_environment();
				$instance->setup_actions();
			endif;

			// Always return the instance.
			return $instance;
		}

		/** Magic Methods ******************************************************** */

		/**
		 * A dummy constructor to prevent from being loaded more than once.
		 *
		 * @see ADPTest::instance()
		 * @see adptest();
		 */
		private function __construct() {
			/* Do nothing here */
		}

		/**
		 * A dummy magic method to prevent from being cloned
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'adp-test-plugin' ), null );
		}

		/**
		 * A dummy magic method to prevent from being unserialized
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'adp-test-plugin' ), null );
		}

		/**
		 * Magic method for checking the existence of a certain custom field
		 */
		public function __isset( $key ) {
			return isset( $this->data[ $key ] );
		}

		/**
		 * Magic method for getting variables
		 */
		public function __get( $key ) {
			return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
		}

		/**
		 * Magic method for setting variables
		 */
		public function __set( $key, $value ) {
			$this->data[ $key ] = $value;
		}

		/**
		 * Magic method for unsetting variables
		 */
		public function __unset( $key ) {
			if ( isset( $this->data[ $key ] ) ) :
				unset( $this->data[ $key ] );
			endif;
		}

		/**
		 * Magic method to prevent notices and errors from invalid method calls
		 */
		public function __call( $name = '', $args = array() ) {
			unset( $name, $args );
			return null;
		}

		/** Private Methods ****************************************************** */
		private function trailingseparatorit( $path ) {
			if ( ! empty( $path ) ) :
				$path = preg_replace( '/[\\/]+$/', '', $path ) . DIRECTORY_SEPARATOR;
			endif;
			return $path;
		}

		/**
		 * Setup the environment variables to allow the rest of plugin to function
		 * more easily.
		 *
		 * @access private
		 */
		private function setup_environment() {

			/** Paths ************************************************************ */
			// File & base & path and URL.
			$this->classname    = get_class( $this );
			$this->classname_lc = sanitize_key( $this->classname ); // $wp_taxonomies uses it for key
			$this->file         = __FILE__;
			$this->plugin_dir   = $this->trailingseparatorit( dirname( $this->file, 2 ) );
			$this->slug         = plugin_basename( $this->plugin_dir );
			$this->hookprefix   = str_replace( '-', '_', $this->slug );
			$this->basefile     = $this->plugin_dir . $this->slug . '.php';
			$this->basename     = plugin_basename( $this->basefile );

			$this->plugin_url = trailingslashit( plugin_dir_url( $this->basefile ) );

			// Includes.
			$this->includes_dir = $this->trailingseparatorit( $this->plugin_dir . 'includes' );
			$this->includes_url = trailingslashit( $this->trailingseparatorit( $this->plugin_url . 'includes' ) );

			// Languages.
			$this->lang_dir = $this->trailingseparatorit( dirname( $this->basename ) . DIRECTORY_SEPARATOR . 'languages' );

			// cpt.
			$this->cpt_key  = "{$this->classname_lc}_apotheken";
			$this->cpt_slug = apply_filters( $this->hookprefix . '_cpt_slug', 'apotheken' ); // duplicate slug problem can be solved slug with the filter

			// taxonomies.
			$this->tax_key = "{$this->classname_lc}_apotheken_types";
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @access private
		 */
		private function setup_actions() {

			$hookprefix = $this->hookprefix;

			// Add actions to plugin activation and deactivation hooks.
			add_action(
				'activate_' . $this->basename,
				function()use ( $hookprefix ) {
					do_action( $hookprefix . '_activation' );
				}
			);
			add_action(
				'deactivate_' . $this->basename,
				function()use ( $hookprefix ) {
					do_action( $hookprefix . '_deactivation' );
				}
			);

			// simple to try event.
			add_action( $this->hookprefix . '_activation', array( $this, 'on_activation' ) );

			// simple to try event.
			add_action( $this->hookprefix . '_deactivation', array( $this, 'on_deactivation' ) );

			// multilang.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// If Plugin is being deactivated, do not add any actions.
			if ( $this->plugin_is_deactivation() ) {
				return;
			}

			// hooks for administrative pages only.
			if ( is_admin() ) :
				// register our option page to the admin_menu action hook.
				add_action( 'admin_menu', array( $this, 'options_page' ) );

				// register settings function to the admin_init action hook.
				add_action( 'admin_init', array( $this, 'settings_init' ) );

				// register meta box.
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

				// register save of meta box.
				add_action( 'save_post', array( $this, 'save_meta_box' ) );
			endif;

			// register cpt.
			add_action( 'init', array( $this, 'custom_post_types' ) );

			// Register Taxonomy.
			add_action( 'init', array( $this, 'register_taxonomy_apotheke_types' ) );

			// Register Shortcode.
			add_action( 'init', array( $this, 'shortcode_init' ) );

			// check whether or not to use the_content hook to render apotheke drug list.
			$option_name                 = "{$this->classname_lc}_options";
			$label_for_activation_hook   = "{$this->classname_lc}_field_content_hook";
			$options                     = get_option( $option_name );
			$activation_hook_the_content = isset( $options[ $label_for_activation_hook ] ) ? ( 'yes' === $options[ $label_for_activation_hook ] ) : false;
			if ( $activation_hook_the_content ) :
				add_filter( 'the_content', array( $this, 'prepend_apotheke_types' ) );
				add_filter( 'the_content', array( $this, 'append_drug_list' ) );
			endif;
		}

		function load_plugin_textdomain() {
			load_plugin_textdomain( 'adp-test-plugin', false, $this->lang_dir );
		}

		private function plugin_is_deactivation() {
			global $pagenow;

			$basename = $this->basename;
			$action   = false;

			// Bail if not in admin/plugins.
			if ( ! ( is_admin() && ( 'plugins.php' === $pagenow ) ) ) :
				return false;
			endif;

			if ( ! empty( $_REQUEST['action'] ) && ( '-1' !== $_REQUEST['action'] ) ) :
				$action = $_REQUEST['action'];
			elseif ( ! empty( $_REQUEST[''] ) && ( '-1' !== $_REQUEST[''] ) ) :
				$action = $_REQUEST[''];
			endif;

			// Bail if not deactivating.
			if ( empty( $action ) || ! in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) ) :
				return false;
			endif;

			// The plugin(s) being deactivated.
			if ( $action === 'deactivate' ) :
				$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
			else :
				$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
			endif;

			// Bail if no basename.
			if ( empty( $basename ) ) :
				return false;
			endif;

			// Is plugin being deactivated?
			return in_array( $basename, $plugins );
		}

		// ****************** Test function*************************
		function on_activation() {
			if ( true === WP_DEBUG ) :
				if ( class_exists( 'ADPTest', false ) ) :
					$plugin_mc = self::instance();
				endif;

				error_log( ( isset( $plugin_mc ) ? ( $plugin_mc->slug . ': ' ) : '' ) . __( 'activated', 'adp-test-plugin' ) );
			endif;

			// trigger our function that registers the custom post type.
			$this->custom_post_types();

			// clear the permalinks after the post type has been registered.
			flush_rewrite_rules();
		}

		function on_deactivation() {
			if ( true === WP_DEBUG ) :
				if ( class_exists( 'ADPTest', false ) ) :
					$plugin_mc = self::instance();
				endif;

				error_log( ( isset( $plugin_mc ) ? ( $plugin_mc->slug . ': ' ) : '' ) . __( 'deactivated', 'adp-test-plugin' ) );
			endif;

			// unregister the post type, so the rules are no longer in memory.
			unregister_post_type( $this->cpt_key );
			// clear the permalinks to remove our post type's rules from the database.
			flush_rewrite_rules();
		}

	}

	/**
	 * The main function responsible for returning the one true ADPTest Instance
	 * to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php adpt = adptest(); ?>
	 *
	 * @return ADPTest The one true ADPTest Instance
	 */
	function adptest() {
		return ADPTest::instance();
	}

	/**
	 * Begins execution of the plugin
	 *
	 * Hook plugin early onto the 'plugins_loaded' action.
	 *
	 * This gives all other plugins the chance to load before, to get their
	 * actions, filters, and overrides setup without this plugin being in the way.
	 */
	if ( defined( 'ADPTEST_LATE_LOAD' ) ) :
		add_action( 'plugins_loaded', 'adptest', (int) ADPTEST_LATE_LOAD );

		// "And now here's something we hope you'll really like!".
	else :
		adptest();
	endif;


endif;
