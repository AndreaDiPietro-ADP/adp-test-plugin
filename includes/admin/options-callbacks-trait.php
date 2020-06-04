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

if ( ! trait_exists( 'ADPTestOptionsCB' ) ) :

	/**
	 * Custom option and settings:
	 * callback functions
	 */
	trait ADPTestOptionsCB {

		/**
		 * Developers section cb
		 * section callbacks can accept an $args parameter, which is an array.
		 * $args have the following keys defined: title, id, callback.
		 * the values are defined at the add_settings_section() function.
		 *
		 * @param array $args arguments.
		 */
		public function section_developers_cb( $args ) {
			?>
			<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Generic drugs and stores settings.', 'adp-test-plugin' ); ?></p>
			<?php
		}

		/**
		 * Field callbacks can accept an $args parameter, which is an array.
		 * $args is defined at the add_settings_field() function.
		 * WordPress has magic interaction with the following keys: label_for, class.
		 * the "label_for" key value is used for the "for" attribute of the <label>.
		 * the "class" key value is used for the "class" attribute of the <tr> containing the field.
		 * you can add custom key value pairs to be used inside your callbacks.
		 *
		 * @param array $args arguments.
		 */
		public function field_content_hook_cb( $args ) {
			// get the value of the setting we've registered with register_setting().
			$options = get_option( $args['option_name'] );
			// output the field.
			?>
			<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
					data-custom="<?php echo esc_attr( $args[ $this->slug_lc . '_custom_data' ] ); ?>"
										name="<?php esc_attr_e( $this->slug_lc ); ?>_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
					>
				<option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
					<?php esc_html_e( 'no', 'adp-test-plugin' ); ?>
				</option>
				<option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
					<?php esc_html_e( 'yes', 'adp-test-plugin' ); ?>
				</option>
			</select>
			<p class="description">
				<?php
				printf(
                                        // translators: 1: Affermative choice 2: Plugin Slug.
                                        __( '<b>%2$s</b> will add to %1$s cpt drugs\' list at the end of the content.', 'adp-test-plugin' ),
                                        ( '<b>' . $this->cpt_slug . '</b>' ),
                                        esc_html__( 'yes', 'adp-test-plugin' )
				)
				?>
			</p>
			<?php
		}

		/**
		 *
		 * @param array $args arguments.
		 */
		public function field_contact_mail_cb( $args ) {
			// get the value of the setting we've registered with register_setting().
			$options = get_option( $args['option_name'] );
			// output the field
			?>
				<input id="<?php echo esc_attr( $args['label_for'] ); ?>"
                                        type="email"
                                        data-custom="<?php echo esc_attr( $args[ $this->slug_lc . '_custom_data' ] ); ?>"
                                        name="<?php echo $this->slug_lc; ?>_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
                                        value="<?php echo isset( $options[ $args['label_for'] ] ) ? sanitize_email( $options[ $args['label_for'] ] ) : ( '' ); ?>"
				/>
			<?php
		}

                /**
                 *
                 * @param array $args arguments.
                 */
		public function field_list_drugs_cb( $args ) {
			// get the value of the setting we've registered with register_setting().
			$options = get_option( $args['option_name'] );
			$values  = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : null;
			// output the field.
			if ( ! empty( $values ) ) {
				$count = 0;
				foreach ( $values as $val ) {
					if ( ! empty( $val ) ) {
						$count++;
						$id_input     = esc_attr( $args['label_for'] ) . '_' . $count;
						$id_container = 'container_' . $id_input;
						$data_cutom   = esc_attr( $args[ $this->slug_lc . '_custom_data' ] );
						$name         = $this->slug_lc . '_options[' . esc_attr( $args['label_for'] ) . '][]';
						printf(
                                                        $this->drug_field(),
                                                        // id input.
                                                        $id_input,
                                                        // data-custom.
                                                        $data_cutom,
                                                        // name.
                                                        $name,
                                                        // value.
                                                        sanitize_text_field( $val ),
                                                        // id container (div).
                                                        $id_container,
                                                        // javascript class prefix name, see options_page_javascript.php.
                                                        $this->slug_lc
						);
					}
				}
			}
			?>
				<a href="#" onclick="<?php echo $this->slug_lc; ?>DrugsOptions().addElement(this); return false;">[+]</a>
			<?php
		}

		/**
		 *
		 * @return string html block.
		 */
		private function drug_field() {
			return '<div id="%5$s"><input id="%1$s" data-custom="%2$s" name="%3$s" value="%4$s"/><a href="#" onclick="%6$sDrugsOptions().deleteElement(document.getElementById(\'%5$s\')); return false;">[-]</a></div>';
		}

                /**
                 * Include javascript for list element.
                 */
		public function include_options_javascript_cb() {
			require $this->includes_dir . 'admin' . DIRECTORY_SEPARATOR . 'options-page-javascript.php';
		}

	}


endif;
