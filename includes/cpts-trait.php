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

if ( !trait_exists( 'ADPTestCPTS' ) ) :

    /**
     * Custom option and settings:
     * callback functions
     */
    trait ADPTestCPTS {

        public function custom_post_types() {
            register_post_type( $this->cpt_key,
                    array(
                        'labels' => array(
                            'name' => __('Apotheken', 'adp-test-plugin'),
                            'singular_name' => __('Apotheke', 'adp-test-plugin'),
                        ),
                        'public' => true,
                        'has_archive' => true,
                        'rewrite' => array('slug' => $this->cpt_slug), // my custom slug.
                        'show_in_rest' => true, //Whether to include the cpt in the REST API. Set this to true for use Gutemberg editor.
                        'taxonomies'=> [$this->tax_key], //When registering a post type, always register your taxonomies using the taxonomies argument. If you do not, the taxonomies and post type will not be recognized as connected.
                    )
            );

            
        }

    }


endif;
