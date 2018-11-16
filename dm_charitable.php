<?php
/**
 * @package DM Charitable Export
 * @version 0.0.1
 */
/*
Plugin Name: DM Charitable Export
Plugin URI: https://www.dailymaverick.co.za
Description: Exports Charitable data
Author: Jason Norwood-Young
Version: 0.0.1
Author Email: jason@10layer.com
*/

// Register Navigation Menus
add_action( 'admin_menu', 'charitable_export_menu' );

/** Step 1. */
function charitable_export_menu() {
	add_options_page( 'Charitable Export', 'Charitable Export', 'manage_options', 'charitable-export', 'do_charitable_export' );
}

/** Step 3. */
function do_charitable_export() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    include("dm_charitable_export.php");
}
?>
