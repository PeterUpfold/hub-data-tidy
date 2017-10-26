<?php


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

/**
 * Provides a means for running a query to select a number of WordPress
 * post objects.
 */
class Data_Tidy_WP_Post_Selector extends Post_Selector {

	/**
	 * Set up the object.
	 *
	 * @param string $post_type The WordPress post type slug this Post Selector will select objects from.
	 *
	 */
	public function __construct( $post_type, $limit ) {

		// refuse to operate on builtin post types
		$valid_post_types = get_post_types( array( '_builtin' => false ), 'names' );
		
		if ( !in_array( $post_type, $valid_post_types ) ) {
			throw new InvalidArgumentException( __( 'Cannot operate on an invalid or built-in post type.', 'hub-data-tidy' ) );
		}

		$this->post_type = $post_type;
		$this->limit = intval( $limit );
	}

	/*
	 * Add a WPDB SQL query compatible WHERE block as a mandatory condition that the
	 * posts must satisfy to be selected by this Post Selector.
	 *
	 * @param $where array WPDB-style array of properties
	 */
	public function add_condition( $where ) {
		$this->conditions[] = $where;
	}

	/**
	 * Add an additional bound parameter.
	 */
	public function add_condition_data( $where_data ) {
		$this->conditions_data[] = $where_data;
	}

	/**
	 * Get the post IDs that this Post Selector has selected.
	 *
	 * @return array
	 */
	public function get_post_ids() {
		global $wpdb;

		if ( count( $this->conditions ) < 1 || count( $this->conditions_data ) < 1 ) {
			throw new UnexpectedValueException( __( 'Number of conditions or data for the conditions is zero. This is not a supported state -- you must select posts specifically.', 'hub-data-tidy' ) );
		}

		// build the query
		$statement = "SELECT DISTINCT ID FROM {$wpdb->prefix}posts
		INNER JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID
		WHERE post_type = %s
		";

		foreach( $this->conditions as $condition ) {
			$statement .= " AND
			$condition
			";
		}
		
		$bound_params = array(
			$this->post_type
		);

		foreach( $this->conditions_data as $param ) {
			$bound_params[] = $param;
		}

		$statement .= " LIMIT %d";
		$bound_params[] = $this->limit;

		$results = $wpdb->get_col(
			$wpdb->prepare( $statement, $bound_params ),
			0
		);

		return $results;
	}

};
