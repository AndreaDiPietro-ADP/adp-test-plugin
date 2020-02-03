<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugi/includes
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

if ( ! trait_exists( 'ADPTestTaxonomy' ) ) :

	/**
	 * Custom option and settings:
	 * callback functions
	 */
	trait ADPTestTaxonomy {

		public function register_taxonomy_apotheke_types() {
			$labels = array(
				'name'          => _x( 'Types', 'taxonomy general name', 'adp-test-plugin' ),
				'singular_name' => _x( 'Type', 'taxonomy singular name', 'adp-test-plugin' ),
				'search_items'  => __( 'Search Types', 'adp-test-plugin' ),
				'all_items'     => __( 'All Types', 'adp-test-plugin' ),
				// 'parent_item' => __('Parent Type','adp-test-plugin'),
				// 'parent_item_colon' => __('Parent Type:','adp-test-plugin'),
				'edit_item'     => __( 'Edit Type', 'adp-test-plugin' ),
				'update_item'   => __( 'Update Type', 'adp-test-plugin' ),
				'add_new_item'  => __( 'Add New Type', 'adp-test-plugin' ),
				'new_item_name' => __( 'New Type Name', 'adp-test-plugin' ),
				'menu_name'     => _x( 'Type', 'taxonomy menu name', 'adp-test-plugin' ),
			);
			$args   = array(
				'hierarchical'      => false, // make it hierarchical (like categories), or plain like Tag.
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true, // Whether to include the taxonomy in the REST API. Set this to true for the taxonomy to be available in the block editor (Gutemberg).
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $this->tax_key ),
			);
			register_taxonomy( $this->tax_key, array( $this->cpt_key ), $args );

			// register_taxonomy_for_object_type( $this->tax_key, $this->cpt_key );
		}

	}


endif;
