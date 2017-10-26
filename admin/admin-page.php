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

	<h1><span class="dashicons dashicons-trash"></span>&nbsp;<?php _e( 'Data Tidy', 'hub-data-tidy' ); ?></h1>

	<p><?php _e( 'This utility allows for the permanent deletion of objects based on the criteria specified below. The Hub provides easy access to data via the WP REST API, but it is not well suited to being a data warehouse. Removing data that is old will help ensure good API performance.', 'hub-data-tidy' ); ?></p>

	<noscript>
		<div class="error"><p><?php _e( 'This page requires JavaScript to be enabled to function correctly.', 'hub-data-tidy' ); ?></p></div>
	</noscript>

	<form id="data-tidy-form" action="" method="POST">

		<table class="form-table">
			<tr>
				<th scope="row">
					<?php _e( 'Object Types', 'hub-data-tidy' ); ?>
				</th>
				<td>
					<fieldset>
					<?php foreach( get_post_types( array( '_builtin' => false ), 'objects' ) as $post_type ): ?>
					<p>
						<input class="wp-post-type" id="<?php echo esc_attr( $post_type->name ); ?>" type="checkbox" name="<?php echo esc_attr( $post_type->name ); ?>" />
						<label for="<?php echo esc_attr( $post_type->name ); ?>">
							<?php echo esc_html( $post_type->labels->name ); ?>
						</label>
					</p>
					<?php endforeach; ?>

					<?php
					/*
						Custom non-WordPress post data types, such as 
						MIS documents that are stored in their own
						table for blob storage.
					*/
					?>
					<?php $custom_types = array(
						0 => array(
							'name' => 'mis_document',
							'friendly_name' => __( 'Uploaded Documents', 'hub-data-tidy' )
						)
					); ?>
					<?php foreach( $custom_types as $custom_type ): ?>
					<p>
						<input class="custom-type" id="<?php echo esc_attr( $custom_type['name'] ); ?>" type="checkbox" name="<?php echo esc_attr( $custom_type['name'] ); ?>" />
						<label for="<?php echo esc_attr( $custom_type['name'] ); ?>">
							<?php echo esc_html( $custom_type['friendly_name'] ); ?>
						</label>
					</p>

					<?php endforeach; ?>
					</fieldset>						
				</td>
			</tr>

			<tr>	
				<th scope="row">
					<?php _e( 'Remove if matching all of:', 'hub-data-tidy' ); ?>
				</th>
				<td>
					<fieldset>
						<p>
							<input id="attached-username-toggle" name="attached-username-toggle" type="checkbox" value="true" />
							<label for="attached-username-toggle">
								<?php _e( 'Attached username begins with', 'hub-data-tidy' ); ?> 
								<input id="attached-username-prefix" name="attached-username-prefix" value="" type="text" maxlength="16" />
							</label>
						</p>
						<p>
							<input id="date-toggle" name="date-toggle" type="checkbox" value="true" />
							<label for="date-toggle">
								<?php _e( 'Date of object is older than', 'hub-data-tidy' ); ?>
								<input type="date" id="datepicker" name="datepicker" />
							</label>
						</p>
					</fieldset>

				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Mode', 'hub-data-tidy' ); ?>
				</th>
				<td>
					<fieldset>
						<p>
							<input id="simulate" name="simulate" type="checkbox" value="true" checked />
							<label for="simulate">
								<?php _e( 'Simulate only', 'hub-data-tidy' ); ?>	
							</label>
						</p>
						<p>
							<label for="batch-size">
								<?php _e( 'Batch size:', 'hub-data-tidy' ); ?>
							</label>
							<input id="batch-size" name="batch-size" type="text" value="1000" />
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
				</th>
				<td>
					<p class="submit">
						<input type="submit" id="submit" class="button button-primary" name="submit" value="<?php _e( 'Tidy', 'hub-data-tidy' ); ?>" />
					</p>
					</td>
			</tr>

			<tr id="results-area" style="display:none;">
				<th scope="row">
					<?php _e( 'Results', 'hub-data-tidy' ); ?>
				</th>
				<td>
					<div id="spinner" class="spinner" style="float:none; visibility: visible;"></div>
					<span id="loading-text"><?php _e( 'Processing&hellip;', 'hub-data-tidy' ); ?></span>
					<div id="message-area"></div>
				</td>
			</tr>

		</table>



	</form>

</div>
