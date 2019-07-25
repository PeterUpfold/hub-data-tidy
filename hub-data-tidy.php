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

/* Copyright (C) 2017-19 Test Valley School.


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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_hub_data_tidy', array( $this, 'process_form' ) );
		add_action( 'wp_ajax_hub_data_tidy_progress', array( $this, 'get_progress' ) );


		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::add_command( 'hub-data-tidy', array( $this, 'cli_data_tidy' ) );
		}
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
			50
		);
	}
	
	/**
	 * Add required JavaScript files to the queue for later loading.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_hub-data-tidy' == $hook_suffix ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_register_script(
				'hub-data-tidy-admin-page',
				plugins_url( 'js/admin-page.js', __FILE__ ),
				array(
					'jquery',
					'jquery-ui-core',
					'jquery-ui-datepicker'
				),
				@filemtime( dirname( __FILE__ ) . '/js/admin-page.js' ),
				true
			);

			wp_enqueue_script( 'hub-data-tidy-admin-page' );
			wp_localize_script( 'hub-data-tidy-admin-page', 'hub_data_tidy_l10n', $this->js_l10n() );

		}
	}


	/**
	 * Render the admin page for the plugin.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( HUB_DATA_TIDY_REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'You do not have the needed permissions to use this utility.', 'hub-data-tidy' ) );
		}
		require( dirname( __FILE__ ) . '/admin/admin-page.php' );
	}

	/**
	 * Return all localised strings for the purposes of the JavaScript. The output of this 
	 * method will be used as an input to wp_localize_script().
	 *
	 * @return array
	 */
	public function js_l10n() {
		return array(
			'submit_button_processing'                         => __( 'Processing&hellip;', 'hub-data-tidy' ),
			'submit_button_normal'                             => __( 'Tidy', 'hub-data-tidy' ),
			'before_unload'                                    => __( 'Closing now may leave the data tidy operation in an unknown state.', 'hub-data-tidy' )
		);
	}

	/**
	 * Process a submission of the data tidy form.
	 */
	public function process_form() {
		global $wpdb;

		require_once( dirname( __FILE__ ) . '/includes/class-post-selector.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-removal-mechanism.php' );

		require_once( dirname( __FILE__ ) . '/includes/class-data-tidy-wp-post-selector.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-data-tidy-wp-post-removal-mechanism.php' );

		if ( ! current_user_can( HUB_DATA_TIDY_REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'You do not have the needed permissions to use this utility.', 'hub-data-tidy' ) );
		}	

		if ( ! array_key_exists( '_wpnonce', $_POST ) ) {
			wp_die( __( 'Unable to verify the form was submitted by a valid user. Please try logging in again and reloading the page.', 'hub-data-tidy' ) );
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'hub-data-tidy' ) ) {
			wp_die( __( 'Unable to verify the form was submitted by a valid user. Please try logging in again and reloading the page.', 'hub-data-tidy' ) );
		}

		// first, get the selected conditions and whether to simulate only
		if ( array_key_exists( 'simulate', $_POST ) && 'false' == $_POST['simulate'] ) {
			$simulate_only = false;
		}
		else {
			$simulate_only = true;
		}

		$conditions_to_add = array();
		$conditions_data_to_add = array();

		if ( array_key_exists( 'attached_username_toggle', $_POST ) && 'true' == $_POST['attached_username_toggle'] ) {
			if ( ! array_key_exists( 'attached_username_prefix', $_POST ) ) {
				wp_send_json_error( array(
					'message' => __( 'If you specify to use a username prefix, you must specify the username.', 'hub-data-tidy' )
				) );
				wp_die();
			}

			if ( empty( $_POST['attached_username_prefix'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'The username prefix cannot be empty.', 'hub-data-tidy' )
				) );
				wp_die();
			}

			$conditions_to_add[] = "(meta_key = %s AND meta_value LIKE %s)";
			$conditions_data_to_add[] = 'username';
			$conditions_data_to_add[] = $wpdb->esc_like( $_POST['attached_username_prefix'] ) . '%';

		}

		if ( array_key_exists( 'date_toggle', $_POST ) && 'true' == $_POST['date_toggle'] ) {
			if ( ! array_key_exists( 'date', $_POST ) ) {
				wp_send_json_error( array(
					'message' => __( 'If you specify to use a date limit, you must specify the date.', 'hub-data-tidy' )
				) );
				wp_die();
			}

			if ( empty( $_POST['date'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'The date cannot be empty if this box is ticked.', 'hub-data-tidy' )
				) );
				wp_die();
			}

			if ( ! checkdate( 
				substr( $_POST['date'], 5, 2 ), /* month */
				substr( $_POST['date'], 8, 2 ), /* day */
				substr( $_POST['date'], 0, 4 )  /* year */
			) ) {
				wp_send_json_error( array(
					'message' => __( 'The date could not be validated (YYYY-MM-DD).', 'hub-data-tidy' )
				) );
				wp_die();
			}

			$conditions_to_add[] = "post_date < %s";
			$conditions_data_to_add[] = $_POST['date'];

		}

		if ( count( $conditions_to_add ) < 1 ) {
			wp_send_json_error( array(
				'message' => __( 'There were no valid conditions to add.', 'hub-data-tidy' )
			) );
			wp_die();
		}

		if ( ! array_key_exists( 'batch_size', $_POST ) ) {
			wp_send_json_error( array(
				'message' => __( 'Batch size was not specified.', 'hub-data-tidy' )
			) );
			wp_die();
		}

		if ( intval( $_POST['batch_size'] ) < 1 ||  intval( $_POST['batch_size'] ) > 100000) {
			wp_send_json_error( array(
				'message' => __( 'Batch size was out of range (1-100000).', 'hub-data-tidy' )
			) );
			wp_die();
		}

		$batch_size = intval( $_POST['batch_size'] );

		$output_progress = array(); // we will report output via the JSON success or error after this point

		// loop through WP post types and select posts
		if ( array_key_exists( 'wp_post_types', $_POST ) && is_array( $_POST['wp_post_types'] ) && count ( $_POST['wp_post_types'] ) > 0 ) {
			foreach( $_POST['wp_post_types'] as $post_type ) {
				$post_selector = new Data_Tidy_WP_Post_Selector( $post_type, $batch_size );

				$output_progress[] = sprintf( __( 'Created a WP post selector for post type %s with batch size %d', 'hub-data-tidy' ), $post_type, $batch_size );

				foreach( $conditions_to_add as $condition ) {
					$post_selector->add_condition( $condition );
					$output_progress[] = sprintf( __( 'Adding condition \'%s\' to post selector for %s', 'hub-data-tidy' ), $condition, $post_type );
				}
				foreach( $conditions_data_to_add as $data ) {
					$post_selector->add_condition_data( $data );
				}

				$post_ids = $post_selector->get_post_ids();
				$output_progress[] = sprintf( __( 'Selected %d posts that match the criteria for the post selector for %s', 'hub-data-tidy' ), count( $post_ids ), $post_type ); 
				
				// push update to transient for live progress
				set_transient( 'hub-data-tidy' . get_current_user_id() . $_POST['_wpnonce'], $output_progress, (60 * 60) );



				if ( ! $simulate_only && count( $post_ids ) > 0 ) {
					// remove
					$remover = new Data_Tidy_WP_Post_Removal_Mechanism();
					foreach( $post_ids as $post_id ) {
						$post_details = get_post( $post_id );
						$output_progress[] = sprintf( __( 'Calling remove on %s ID %d from %s: %s (%s)', 'hub-data-tidy' ), $post_details->post_type, intval( $post_id ), date('Y-m-d H:i:s', strtotime( $post_details->post_date ) ), $post_details->post_title, $post_type );
						$remover->remove( intval( $post_id ) );

						// push update to transient for live progress
						set_transient( 'hub-data-tidy' . get_current_user_id() . $_POST['_wpnonce'], $output_progress, (60 * 60) );
					}
				}
				else if ( count( $post_ids  ) > 0 ) {
					// show what would be removed
					foreach( $post_ids as $post_id ) {
						$post_details = get_post( $post_id );
						$output_progress[] = sprintf( __( 'Simulated: would remove %s ID %d from %s: %s (%s)', 'hub-data-tidy' ), $post_details->post_type, intval( $post_id ), date('Y-m-d H:i:s', strtotime( $post_details->post_date ) ), $post_details->post_title, $post_type );					
						// push update to transient for live progress
						set_transient( 'hub-data-tidy' . get_current_user_id() . $_POST['_wpnonce'], $output_progress, (60 * 60) );

					}
				}

			}
		}

		wp_send_json_success( array( 'messages' => $output_progress ) );

	}

	/**
	 * Return the current progress of the job.
	 */
	public function get_progress() {

		if ( ! current_user_can( HUB_DATA_TIDY_REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'You do not have the needed permissions to use this utility.', 'hub-data-tidy' ) );
		}

		if ( ! array_key_exists( '_wpnonce', $_POST ) ) {
			wp_die( __( 'Unable to verify the form was submitted by a valid user. Please try logging in again and reloading the page.', 'hub-data-tidy' ) );
		}
		
		wp_send_json_success( get_transient( 'hub-data-tidy' . get_current_user_id() . $_POST['_wpnonce']) );
		wp_die();
	}


	/**
	 * Process the tidying up of Hub data through the WP_CLI interface.
	 *
	 * --wp-post-types=<post-types>
	 *  : A comma-separated set of the WordPress post type slugs to tidy (e.g. 'achievements,attendance-marks')
	 *
	 * --batch-size=<batch-size>
	 *  : A maximum number of posts to process in this execution run of the command.
	 *
	 * --simulate=<simulate>
	 *  : Simulate mode is on by default. Pass 'off' or 'false' to actually remove content from WordPress.
	 *
	 * --attached-username-prefix=<username-prefix>
	 *  : Only remove WP posts that have a meta value such that they are associated with usernames beginning with this string.
	 *
	 * --date=<date>
	 *  : A YYYY-MM-DD string, where any content that was created on or after this date will not be selected for removal.
	 */
	public function cli_data_tidy( $args, $assoc_args ) {
		global $wpdb;

		require_once( dirname( __FILE__ ) . '/includes/class-post-selector.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-removal-mechanism.php' );

		require_once( dirname( __FILE__ ) . '/includes/class-data-tidy-wp-post-selector.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-data-tidy-wp-post-removal-mechanism.php' );

		if ( ! class_exists( 'WP_CLI' ) ) {
			wp_die('WP_CLI is not installed or not available.');
		}
		
		$simulate = true;

		if ( array_key_exists( 'simulate', $assoc_args ) ) {
			if ( 'false' == $assoc_args['simulate'] || 'off' == $assoc_args['simulate'] ) {
				WP_CLI::log( __( 'Simulate is disabled.', 'hub-data-tidy' ) );
				$simulate = false;
			}
		}

		$batch_size = 1000;
		if ( array_key_exists( 'batch_size', $assoc_args ) && is_numeric( $assoc_args['batch_size'] ) && (int)$assoc_args['batch_size'] > 0 ) {
			$batch_size = (int)$assoc_args['batch_size'];
			WP_CLI::log( sprintf( __('Setting batch size to %d.', 'hub-data-tidy' ), $batch_size ) );
		}

		$conditions_to_add = [];
		$conditions_data_to_add = [];

		if ( array_key_exists( 'attached-username-prefix', $assoc_args ) ) {
			$conditions_to_add[] = "(meta_key = %s AND meta_value LIKE %s)";
			$conditions_data_to_add[] = 'username';
			$conditions_data_to_add[] = $wpdb->esc_like( $assoc_args['attached-username-prefix'] );
			WP_CLI::log( sprintf( __( 'Adding username prefix condition for "%s".', 'hub-data-tidy' ), $assoc_args['attached-username-prefix'] ) );
		}

		if ( array_key_exists( 'date', $assoc_args ) ) {
			if ( !checkdate(
				substr( $assoc_args['date'], 5, 2 ), /* month */
				substr( $assoc_args['date'], 8, 2 ), /* day   */
				substr( $assoc_args['date'], 0, 4 )  /* year  */
			) ) {
				WP_CLI::error( __( 'The date could not be validated (YYYY-MM-DD).', 'hub-data-tidy' ) );
			}
			$conditions_to_add[] = "post_date < %s";
			$conditions_data_to_add[] = $assoc_args['date'];
			WP_CLI::log( sprintf( __( 'Adding date before condition of "%s".', 'hub-data-tidy' ), $assoc_args['date'] ) );
		}
				
		$remover = new Data_Tidy_WP_Post_Removal_Mechanism();

		// loop through post types and select posts
		if ( array_key_exists( 'wp-post-types', $assoc_args ) ) {
			$post_types = explode(',', $assoc_args['wp-post-types'] );
			foreach( $post_types as $post_type ) {
				WP_CLI::log( sprintf( __( 'Processing post type "%s".', 'hub-data-tidy' ), $post_type ) );
				$post_selector = NULL;
				try {
					$post_selector = new Data_Tidy_WP_Post_Selector( $post_type, $batch_size );
				}
				catch ( InvalidArgumentException $exception ) {
					WP_CLI::line(
						sprintf(
							__( 'Unable to create a post type selector for "%s". Maybe this post type slug is invalid? Exception: "%s"', 'hub-data-tidy' ),
							$post_type,
							$exception->getMessage()
						)
					);
					continue;
				}

				WP_CLI::log( sprintf( __( 'Created a WP post selector for post type %s with batch size %d', 'hub-data-tidy' ), $post_type, $batch_size ) );

				
				foreach( $conditions_to_add as $condition ) {
					$post_selector->add_condition( $condition );
					WP_CLI::log( sprintf( __( 'Adding condition \'%s\' to post selector for %s', 'hub-data-tidy' ), $condition, $post_type ) );
				}
				foreach( $conditions_data_to_add as $data ) {
					$post_selector->add_condition_data( $data );
				}

				$post_ids = $post_selector->get_post_ids();
				WP_CLI::log( sprintf( __( 'Selected %d posts that match the criteria for the post selector for %s', 'hub-data-tidy' ), count( $post_ids ), $post_type ) );

				$removed_count = 0;

				if ( $simulate && count( $post_ids ) > 0 ) {
					/* simulate removal */
					foreach( $post_ids as $post_id ) {
						$post_details = get_post( $post_id );
						WP_CLI::line(
							sprintf(
								__( 'Simulated: would remove %s ID %d from %s: %s (%s)', 'hub-data-tidy' ),
								$post_details->post_type, intval( $post_id ),
								date('Y-m-d H:i:s', strtotime( $post_details->post_date ) ),
								$post_details->post_title, $post_type 
							)
						);
						$removed_count++;
					}
				}
				else if ( count( $post_ids ) > 0 ) {
					/* actually remove */
					foreach( $post_ids as $post_id ) {
						$post_details = get_post( $post_id );
						WP_CLI::log(
							sprintf(
								__( 'Calling remove on %s ID %d from %s: %s (%s)', 'hub-data-tidy' ),
								$post_details->post_type, intval( $post_id ),
								date('Y-m-d H:i:s', strtotime( $post_details->post_date ) ),
								$post_details->post_title, $post_type 
							)
					       	);
						$remover->remove( intval( $post_id ) );	
						$removed_count++;
					}
				}
				else {
					/* no posts */
					WP_CLI::error( __( 'No content was found that matched the conditions to tidy.', 'hub-data-tidy' ) );
				}

			}
		}
		else {
			WP_CLI::error( __( 'No wp-post-types were specified to tidy.', 'hub-data-tidy' ) );
		}


		WP_CLI::log(
			( $simulate ) ? sprintf( __( 'Simulated removal of %d items.', 'hub-data-tidy' ), $removed_count ) :
					sprintf( __( 'Removed %d items.', 'hub-data-tidy' ), $removed_count )
		);

	}	

};

if ( function_exists( 'add_action' ) ) {
	$hub_data_tidy = new Hub_Data_Tidy();
}
