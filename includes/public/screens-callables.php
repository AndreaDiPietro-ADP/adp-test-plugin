<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugi/includes/public
 */

defined( 'WPINC' ) || die;

if ( ! trait_exists( 'ADPTestPublicSceensCallables' ) ) :

	trait ADPTestPublicSceensCallables {

		/**
		 *
		 * @param int $apotheke_id drugstore id.
		 * @return array
		 */
		private function get_drug_list( $apotheke_id ) {
			$meta_key  = "_{$this->classname_lc}_avaible_drugs";
			$drug_list = get_post_meta( $apotheke_id, $meta_key, false );

			return $drug_list;
		}

		/**
		 *
		 * @param array $drug_list drugs list.
		 * @return string
		 */
		private function get_drug_list_html( $drug_list ) {
			$content = '';
			if ( ! empty( $drug_list ) && is_array( $drug_list ) ) :
				$class    = sanitize_html_class( $this->classname_lc . '_title_list_drugs' );
				$content .= "<label class=\"{$class}\">" . __( 'List of avaible drugs.', 'adp-test-plugin' ) . '</label>';
				$class    = sanitize_html_class( $this->classname_lc . '_list_drugs' );
				$content .= "<ul class=\"{$class}\">";
				foreach ( $drug_list as $drug ) :
					$drug     = esc_html( $drug );
					$content .= "<li>{$drug}</li>";
				endforeach;
				$content .= '</ul>';
			endif;
			return $content;
		}

		/**
		 *
		 * @param string $content .
		 * @return string
		 */
		public function append_drug_list( $content ) {
			// Check if we're inside the main loop in a single post page.
			if ( is_single() && in_the_loop() && is_main_query() ) :
				$drug_list = $this->get_drug_list( get_the_ID() );

				if ( ! empty( $drug_list ) ) :
					$content .= $this->get_drug_list_html( $drug_list );

					$contact_mail = $this->contact_info();
					if ( ! empty( $contact_mail ) ) :
						$contact_mail = sanitize_email( $contact_mail );
						$content     .= "<div class=\"{$this->classname_lc}\"><a href=\"mailto:{$contact_mail}\">" . esc_html__( 'Drugstores\'s administrator info contact.', 'adp-test-plugin' ) . '</a></div>';
					endif;
				endif;
			endif;
			return $content;
		}

		/**
		 *
		 * @return string .
		 */
		private function contact_info() {
			$option_name  = "{$this->classname_lc}_options";
			$label_for    = "{$this->classname_lc}_field_contact_mail";
			$options      = get_option( $option_name );
			$contact_mail = ( false !== $options && isset( $options[ $label_for ] ) ) ? sanitize_email( $options[ $label_for ] ) : null;
			return $contact_mail;
		}

		/**
		 *
		 * @param string $content .
		 * @return string
		 */
		public function prepend_apotheke_types( $content ) {
			// Check if we're inside the main loop in a single post page.
			if ( is_single() && in_the_loop() && is_main_query() ) :
				$term_list = wp_get_post_terms( get_the_ID(), $this->tax_key, array( 'fields' => 'names' ) );

				if ( ! is_wp_error( $term_list ) && ! empty( $term_list ) ) :
					$t_list = '';
					foreach ( $term_list as $wp_term ) :
						$t_list .= ',' . esc_html( $wp_term );
					endforeach;
					if ( ! empty( $t_list ) ) :
						$t_list  = substr( $t_list, 1 );
						$class   = sanitize_html_class( $this->classname_lc . '_apotheke_types' );
						$content = "<div class=\"{$class}\">{$t_list}</div>" . $content;
					endif;
				endif;
			endif;
			return $content;
		}

		/**
		 *
		 * @param array  $atts .
		 * @param string $content .
		 * @param string $tag .
		 * @return string
		 */
		public function shortcode( $atts = array(), $content = null, $tag = '' ) {
			// normalize attribute keys, lowercase.
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );

			// start output.
			$o = '';

			$wp_post = null;
			// check exiting and published apotheke.
			if ( isset( $atts['id'] ) && ! empty( $atts['id'] ) && is_numeric( $atts['id'] ) && ! empty( $wp_post = get_post( (int) $atts['id'], OBJECT ) ) && 'publish' === $wp_post->post_status && $wp_post->post_type === $this->cpt_key ) {

				// avoid self loop.
				if ( in_the_loop() && get_the_ID() === $wp_post->ID ) :
					return $o;
				endif;

				// override default attributes with user attributes.
				$shcd_atts = shortcode_atts(
					array(
						'title' => __( 'Apotheke info', 'adp-test-plugin' ),
					),
					$atts,
					$tag
				);

				// start box.
				$o .= "<div class=\"{$this->classname_lc}-apotheke-info-box\">";

				// title.
				$o .= '<h2>' . esc_html( $shcd_atts['title'] ) . '</h2>';

				$o .= '<h3><a href="' . get_permalink( $wp_post ) . '">' . esc_html( $wp_post->post_title ) . '</a></h3>';
				// enclosing tags.
				if ( ! is_null( $content ) ) {
					$modified_content = $content;

					// page builder of siteorigin create infinite loop using filter the content.
					// so remove it.
					$filter_exist = false;
					if ( class_exists( 'SiteOrigin_Panels' ) ) :
						$filter_exist = remove_filter( 'the_content', array( SiteOrigin_Panels::single(), 'generate_post_content' ) );
					endif;
					// secure output by executing the_content filter hook on $content and then run shortcode parser recursively.
					$modified_content = apply_filters( 'the_content', $modified_content );
					// and re-add it if it was removed.
					if ( class_exists( 'SiteOrigin_Panels' ) && $filter_exist ) :
						add_filter( 'the_content', array( SiteOrigin_Panels::single(), 'generate_post_content' ) );
					endif;

					$modified_content = do_shortcode( $modified_content );

					$o .= "<div class\"{$this->classname_lc}-shortcode-content\">{$modified_content}</div>";
				}

				// apotheke drug list.
				$drug_list = $this->get_drug_list( $wp_post->ID );
				$o .= $this->get_drug_list_html( $drug_list );

				// end box.
				$o .= '</div>';
			}
			// return output.
			return $o;
		}

		/**
		 * .
		 */
		public function shortcode_init() {
			add_shortcode( "{$this->classname_lc}-apotheke-info", array( $this, 'shortcode' ) );
		}

	}

endif;
