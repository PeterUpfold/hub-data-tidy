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
 * Class providing a means for deleting WP post objects with a specified ID.
 */
class Data_Tidy_WP_Post_Removal_Mechanism extends Removal_Mechanism {

	/**
	 * Remove the object with this ID.
	 */
	public function remove( $id ) {
		wp_delete_post( $id, true );
	}

};
