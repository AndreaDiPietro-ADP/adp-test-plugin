<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugi/includes/admin
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

if ( ! trait_exists( 'ADPTestMetaBox' ) ) :

	/**
	 * Custom option and settings:
	 * callback functions
	 */
	trait ADPTestMetaBox {
		/**
		 * Add the meta box
		 */
		public static function add_meta_box() {
			$screen = $this->cpt_key;
			add_meta_box(
				"{$this->classname_lc}_box_id", // Unique ID.
				__( 'Apotheke info', 'adp-test-plugin' ), // Box title.
				array( $this, 'html_meta_box' ), // Content callback, must be of type callable.
				$screen, // Post type.
				'side'                    // context.
			);
		}

		/**
		 *
		 * Save meta box value
		 *
		 * @param int $post_id the post id.
		 */
		public static function save_meta_box( $post_id ) {
			$label_for  = "{$this->classname_lc }_field_list_drugs";
			$meta_key   = "_{$this->classname_lc}_avaible_drugs";
			$nonce_name = "{$this->classname_lc}_box_nonce";
			if ( isset( $_POST[ $nonce_name ] ) && wp_verify_nonce( sanitize_text_field( $_POST[ $nonce_name ] ), $nonce_name ) ) :
				$values = array_key_exists( $label_for, $_POST ) ? ( (array) $_POST[ $label_for ] ) : null;
				delete_post_meta( $post_id, $meta_key );
				if ( ! empty( $values ) ) :
					foreach ( $values as &$val ) :
						$val = sanitize_text_field( $val );
						add_post_meta( $post_id, $meta_key, $val );
					endforeach;
				endif;
			elseif ( true === WP_DEBUG && isset( $_POST[ $nonce_name ] ) ) :
					error_log(
						sprintf(
							// translators: 1: Slug Name of plugin 2: Function Name.
							__( '%1$s, %2$s: meta box data not saved, wrong nonce.', 'adp-test-plugin' ),
							$this->slug,
							'save_meta_box'
						)
					);
			endif;
		}

		/**
		 *
		 * Print html
		 *
		 * @param WP_Post $post object post.
		 */
		public static function html_meta_box( $post ) {
			$meta_key   = "_{$this->classname_lc}_avaible_drugs";
			$values     = get_post_meta( $post->ID, $meta_key, false );
			$nonce_name = "{$this->classname_lc}_box_nonce";

			// get the value of the setting 'drugs list'.
			$option_name    = "{$this->classname_lc}_options";
			$label_for      = "{$this->classname_lc}_field_list_drugs";
			$options        = get_option( $option_name );
			$setting_values = isset( $options[ $label_for ] ) ? $options[ $label_for ] : null;
			// output the fields.
			?>
			<input type="hidden" name="<?php echo $nonce_name; ?>" value="<?php echo wp_create_nonce( $nonce_name ); ?>"/>
<label for="<?php esc_attr_e( $label_for ); ?>"><?php esc_html_e( 'List of apotheke\'s drugs', 'adp-test-plugin' ); ?></label>
			<?php
			if ( ! empty( $setting_values ) ) :
				$count = 0;
				foreach ( $setting_values as $setting_val ) :
					if ( ! empty( $setting_val ) ) :
						$count++;
						$id_input = esc_attr( $label_for ) . '_' . $count;
						?>
						<div>
							<input name="<?php echo esc_attr( $label_for ); ?>[]" id="<?php echo $id_input; ?>" value="<?php esc_attr_e( $setting_val ); ?>" <?php echo checked( true, ( ( ! empty( $values ) && is_array( $values ) ) ? in_array( $setting_val, $values ) : false ), false ); ?> type="checkbox"/> <?php esc_html_e( $setting_val ); ?>
						</div>
						<?php
					endif;
				endforeach;
			endif;
		}

	}


endif;
