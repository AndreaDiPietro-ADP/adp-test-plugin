<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugin/includes/admin
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

if ( ! trait_exists( 'ADPTestOptionsSettings' ) ) :

	/**
	 * Custom option and settings:
	 */
	trait ADPTestOptionsSettings {

		/**
		 * Top level menu
		 */
		public function options_page() {
			// Add top level menu page.
			$hook_suffix = add_menu_page(
				sprintf(
							/* translators: %s: Name of Plugin's Menu page Title */
					__( '%s options', 'adp-test-plugin' ),
					$this->slug
				),
				sprintf(
							/* translators: %s: Name of Plugin's Menu Title */
					__( '%s Options', 'adp-test-plugin' ),
					$this->slug
				),
				'manage_options',
				$this->slug,
				array( $this, 'options_page_html' )
			);

			add_action( "admin_head-{$hook_suffix}", array( $this, 'include_options_javascript_cb' ) );
		}

		/**
		 * Top level menu:
		 * callback functions
		 */
		public function options_page_html() {
			// Check user capabilities.
			if ( ! current_user_can( 'manage_options' ) ) :
				return;
			endif;

			require $this->includes_dir . 'admin' . DIRECTORY_SEPARATOR . 'options-page-html.php';
		}


		/**
		 * Check options before saving
		 */
		public function option_validation( $value, $old_value, $option ) {
			// Remove empty element from drugs' list.
			$label_for = "{$this->classname_lc}_field_list_drugs";
			$drug_list = isset( $value[ $label_for ] ) ? $value[ $label_for ] : null;
			if ( ! empty( $drug_list ) ) :
				// Remove empty elements.
				$drug_list           = array_filter(
					$drug_list,
					function( $val ) {
						return ! empty( $val );
					},
					0
				);
				$value[ $label_for ] = $drug_list;
			endif;

			// Sanitize contact mail.
			$label_for    = "{$this->classname_lc}_field_contact_mail";
			$contact_mail = isset( $value[ $label_for ] ) ? $value[ $label_for ] : null;
			if ( ! empty( $contact_mail ) ) :
				$contact_mail = sanitize_email( $contact_mail );
			endif;
			return $value;
		}

		/**
		 *  Remove deleted drugs from posts' meta
		 */
		public function remove_from_post_meta_deleted_drugs( $old_value, $value, $option ) {

			$label_for     = "{$this->classname_lc}_field_list_drugs";
			$old_drug_list = isset( $old_value[ $label_for ] ) ? $old_value[ $label_for ] : null;
			$drug_list     = isset( $value[ $label_for ] ) ? $value[ $label_for ] : null;

			$to_del = null;
			if ( ! empty( $old_drug_list ) && is_array( $old_drug_list ) ) :
				if ( empty( $drug_list ) ) :
					$to_del = $old_drug_list;
				elseif ( is_array( $drug_list ) ) :
					$to_del = array_diff( $old_drug_list, $drug_list );
				endif;
			endif;

			// Sync post meta avaible drugs.
			if ( ! empty( $to_del ) ) :
				// Remove empty elements.
				$to_del = array_filter(
					$to_del,
					function( $val ) {
						return ! empty( $val );
					},
					0
				);
				if ( ! empty( $to_del ) ) :
					// error_log(print_r($to_del,true));
					global $wpdb;

					$ps_placeholders = '';
					$size_of         = count( $to_del );
					for ( $i = 0; $i < $size_of; $i++ ) :
						$ps_placeholders .= ',%s';
					endfor;
					$ps_placeholders = substr( $ps_placeholders, 1 );
					$query           = $wpdb->prepare( "delete from {$wpdb->postmeta} where meta_key = '_{$this->classname_lc}_avaible_drugs' and meta_value in (" . $ps_placeholders . ') ', $to_del );
					$result          = $wpdb->query( $query );
					if ( true === WP_DEBUG && false === $result && ! empty( $wpdb->last_error ) ) :
						error_log( $wpdb->last_error );
					endif;
				endif;
			endif;
		}

		/**
		 * Custom option and settings
		 */
		public function settings_init() {

			$option_group = $this->slug;
			$option_name  = $this->classname_lc . '_options';

			add_filter( "pre_update_option_{$option_name}", array( $this, 'option_validation' ), 10, 3 );
			add_action( "update_option_{$option_name}", array( $this, 'remove_from_post_meta_deleted_drugs' ), 10, 3 );

			// Register a new setting for Options' page ('slug').
			register_setting( $option_group, $option_name );

			// Register a new section in Options' page ('slug').
			add_settings_section(
				$this->classname_lc . '_section_developers',
				__( 'Configure drug stores.', 'adp-test-plugin' ),
				array( $this, 'section_developers_cb' ),
				$this->slug
			);

			// Register a new field in the section, inside the Options' page ('slug').
			$setid = "{$this->classname_lc}_field_content_hook";
			add_settings_field(
				$setid, // As of WP 4.6 this value is used only internally.
				// Use $args' label_for to populate the id inside the callback.
					__( 'Content hook active?', 'adp-test-plugin' ),
				array( $this, 'field_content_hook_cb' ),
				$this->slug,
				$this->classname_lc . '_section_developers',
				array(
					'label_for'                          => $setid,
					'class'                              => $this->classname_lc . '_row',
					$this->classname_lc . '_custom_data' => 'custom',
					'option_name'                        => $option_name,
				)
			);

			// Register a new field in the section, inside the Options' page ('slug').
			$setid = "{$this->classname_lc}_field_contact_mail";
			add_settings_field(
				$setid, // As of WP 4.6 this value is used only internally.
				// Use $args' label_for to populate the id inside the callback.
					__( 'General contact mail', 'adp-test-plugin' ),
				array( $this, 'field_contact_mail_cb' ),
				$this->slug,
				$this->classname_lc . '_section_developers',
				array(
					'label_for'                          => $setid,
					'class'                              => $this->classname_lc . '_row',
					$this->classname_lc . '_custom_data' => 'custom',
					'option_name'                        => $option_name,
				)
			);

			// Register a new field in the section, inside the Options' page ('slug').
			$setid = "{$this->classname_lc}_field_list_drugs";
			add_settings_field(
				$setid, // As of WP 4.6 this value is used only internally.
				// Use $args' label_for to populate the id inside the callback.
					__( 'Drugs', 'adp-test-plugin' ),
				array( $this, 'field_list_drugs_cb' ),
				$this->slug,
				$this->classname_lc . '_section_developers',
				array(
					'label_for'                          => $setid,
					'class'                              => $this->classname_lc . '_row',
					$this->classname_lc . '_custom_data' => 'custom',
					'option_name'                        => $option_name,
				)
			);
		}

	}


endif;
