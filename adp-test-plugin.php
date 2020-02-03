<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP
 */

/**
 * Example plugin
 *
 * @package ADP_Test_Plugin
 * Plugin Name: ADP Test Plugin
 * Description: Testing Plugin Technics
 * Version: 0.1
 * Author: Andrea Di Pietro
 * Text Domain: adp-test-plugin
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Include Plugin Main Class.
require __DIR__ . '/includes/class-adptest.php';
