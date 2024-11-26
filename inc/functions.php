<?php
/**
 * Pluggable functions
 *
 * @package         Image_Base64
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'wp_get_attachment_image_base64' ) ) :
	/**
	 * Retrieve image size base64 string
	 *
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
	 * @return string|false
	 */
	function wp_get_attachment_image_base64( int $attachment_id, string|array $size = 'thumbnail' ) {
		$is_image = wp_attachment_is_image( $attachment_id );

		if ( ! $is_image ) {
			return false;
		}

		foreach ( array( $size, 'full' ) as $_size ) {
			$image = image_get_intermediate_size( $attachment_id, $_size );

			if ( isset( $image['base64'] ) ) {
				$base64 = $image['base64'];
				break;
			} else {
				$base64 = false;
			}
		}

		return apply_filters( 'wp_get_attachment_image_base64', $base64, $attachment_id, $size );
	}
endif;
