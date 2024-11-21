<?php
/**
 * Add support for WP-GraphQL
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

namespace Image_Base64;

/**
 * Support WP-GraphQL
 */
add_action(
	'graphql_register_types',
	function () {
		\register_graphql_fields(
			'MediaSize',
			array(
				'base64' => array(
					'description' => __( 'Base64 of data URI for a blur version', 'contasconnosco' ),
					'type'        => 'string',
					'resolve'     => function ( array $image ) {
						$blur_data = isset( $image['base64'] ) ? $image['base64'] : null;

						if ( ! $blur_data ) {
							// Legacy keys!
							$blur_data = isset( $image['blurDataURL'] ) ? $image['blurDataURL'] : null;
						}

						return $blur_data;
					},
				),
			),
		);

		\register_graphql_fields(
			'MediaItem',
			array(
				'base64' => array(
					'description' => __( 'Base64 of data URI for a blur version', 'contasconnosco' ),
					'type'        => 'string',
					'args'        => array(
						'size' => array(
							'type'        => 'MediaItemSizeEnum',
							'description' => __( 'Size of the MediaItem to calculate base64 with', 'contasconnosco' ),
						),
					),
					'resolve'     => function ( $post, $args ) {
						$size = 'medium';

						if ( ! empty( $args['size'] ) ) {
							$size = $args['size'];
						}

						if ( 'post-thumbnail' === $size ) {
							$size = 'thumbnail';
						}

						$metadata = wp_get_attachment_metadata( $post->ID );
						$blur_data = isset( $metadata['sizes'][ $size ]['base64'] ) ? $metadata['sizes'][ $size ]['base64'] : null;

						if ( ! $blur_data ) {
							// Legacy keys!
							$blur_data = isset( $metadata['sizes'][ $size ]['blurDataURL'] ) ? $metadata['sizes'][ $size ]['blurDataURL'] : null;
						}

						return $blur_data;
					},
				),
			),
		);
	}
);
