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
 * Abstract class providing a means for running a query to select a number of
 * posts as candidates for deletion.
 */
abstract class Post_Selector {

	/**
	 * The WordPress post type or pseudo-type that this post selector
	 * is concerned with.
	 */
	protected $post_type;

	/**
	 * Array of WPDB SQL query compatible conditions. Will be ANDed together in
	 * the query.
	 */
	protected $conditions;

	/**
	 * Data format of the $conditions items, for the purposes of $wpdb->prepare.
	 */
	protected $conditions_format;

	/**
	 * Array of post IDs that have been selected by this selector.
	 */
	protected $post_ids;

	/**
	 * Add a WPDB SQL query compatible WHERE block as a mandatory condition that the
	 * posts must satisfy to be selected by this Post Selector.
	 *
	 * @param $where array WPDB-style array of properties
	 */
	abstract public function add_condition( $where, $where_format ); 

	/**
	 * Return an array of the post IDs that this Post Selector has selected.
	 */
	abstract public function get_post_ids();

};
