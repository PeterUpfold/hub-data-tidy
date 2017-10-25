<?php
/*
Plugin Name: "The Hub" Data Tidy
Plugin URI: https://www.testvalley.hants.sch.uk/
Description: Provides a user interface for tidying old MIS data to keep the WordPress database running smoothly.
Version: 1.0
Author: Mr P Upfold
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/* Copyright (C) 2017 Test Valley School.


    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

define( 'HUB_DATA_TIDY_REQUIRED_CAPABILITY', 'delete_others_pages' );

// dashicons-trash

class Hub_Data_Tidy {

	/**
	 * Add actions to WP hooks for this plugin.
	 */
 	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	/**
	 * Register our admin menus.
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Data Tidy', 'hub-data-tidy' ),
			__( 'Data Tidy', 'hub-data-tidy' ),
			HUB_DATA_TIDY_REQUIRED_CAPABILITY,
			'hub-data-tidy',
			array( $this, 'render_admin_page' ),
			'dashicons-trash',
			5
		);
	}


	/**
	 * Render the admin page for the plugin.
	 */
	public function render_admin_page() {
		require( dirname( __FILE__ ) . '/admin/admin-page.php' );
	}
};

if ( function_exists( 'add_action' ) ) {
	$hub_data_tidy = new Hub_Data_Tidy();
}
