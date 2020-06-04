<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugin/includes
 */

// If this file is called directly, abort.
( defined( 'WPINC' ) && current_user_can( 'manage_options' ) ) || die;



// add error/update messages.
// check if the user have submitted the settings.
// WordPress will add the "settings-updated" $_GET parameter to the url.
if ( isset( $_GET['settings-updated'] ) ) {
	// add settings saved message with the class of "updated".
	add_settings_error( $this->slug_lc . '_messages', $this->slug_lc . '_message', __( 'Settings Saved', 'adp-test-plugin' ), 'updated' );
}

// show error/update messages.
settings_errors( $this->slug_lc . '_messages' );
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
<?php
// output security fields for the registered setting.
settings_fields( $this->slug );
// output setting sections and their fields.
// (sections are registered for 'class name', each field is registered to a specific section).
do_settings_sections( $this->slug );
// output save settings button.
submit_button( 'Save Settings' );
?>
	</form>
</div>
