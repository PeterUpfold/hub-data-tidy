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
 * The HTML structure for the admin page.
 */

if ( ! function_exists( 'add_action' ) ) {
	header( 'HTTP/1.1 403 Forbidden');
	echo '<h1>Forbidden</h1>';
	die();
}

?>
<div class="wrap">

	<h1><?php _e( 'Data Tidy', 'hub-data-tidy' ); ?></h1>

	<form id="data-tidy-form" action="" method="POST">

		<table class="form-table">
			<tr>
				<th scope="row">
					<?php _e( 'Object Types', 'hub-data-tidy' ); ?>
				</th>
				<td>
					<fieldset>
					<?php foreach( get_post_types( array( '_builtin' => false ), 'objects' ) as $post_type ): ?>
						<input id="<?php echo esc_attr( $post_type->name ); ?>" type="checkbox" name="<?php echo esc_attr( $post_type->name ); ?>" />
						<label for="<?php echo esc_attr( $post_type->name ); ?>">
							<?php echo $post_type->labels->name; ?>
						</label>

					<?php endforeach; ?>
					</fieldset>						
				</td>
			</tr>
		</table>

	</form>

</div>
