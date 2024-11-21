<?php
/**
 * Hook on image changes
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

/**#& hooks */
add_filter( 'wp_generate_attachment_metadata', __NAMESPACE__ . '\\process_images', 100, 2 );
add_filter( 'wp_update_attachment_metadata', __NAMESPACE__ . '\\process_images', 100, 2 );

/**
 * PHP opens and outputs images using different functions.
 * In this method, we choose correct one using mime type.
 * Remember to use function_exists() before using returned functions.
 *
 * @param string $mime - mime type (f.e. image/jpeg).
 * @return array - array where first index is image open function and second index output function.
 */
function choose_funcs_for_mime_type( string $mime ): array {
	$type = explode( '/', $mime )[1];
	return array( "imagecreatefrom$type", "image$type" );
}

/**
 * Parses image attachment's size data to more clearer and concise structure.
 * This also appends default image path to the array, which is also in the metadata.
 *
 * @param array $metadata - metadata passed to f.e. wp_generate_attachment_metadata hook.
 * @return array - array where the key is the slug of image size and the value is the path inside uploads folder to the image.
 */
function parse_sizes_from_metadata( $metadata ): array {
	$file     = $metadata['file'];
	$file_dir = dirname( $file );

	$sizes = array(
	// 'full' => $metadata['file'], something not completely ok.
	);
	foreach ( $metadata['sizes'] as $size => $size_data ) {
		if ( 'full' !== $size ) {
			$sizes[ $size ] = $file_dir . '/' . $size_data['file'];
		}
	}

	return $sizes;
}

/**
 * Generates a base64 image version and return the data
 *
 * @param Function $create - function.
 * @param Function $output - function.
 * @param string   $basedir - basedir.
 * @param string   $path - path.
 * @param string   $id - image id.
 * @param string   $size - size ex. thumbnail.
 * @param string   $mimetype - ex. jpeg.
 *
 * @return array
 */
function generate_base64( $create, $output, $basedir, $path, $id, $size, $mimetype ) {
	$image = @$create( "$basedir/$path" );

	if ( ! $image ) {
		// phpcs:ignore
		$url = wp_get_attachment_image_url( $id, $size ) ?: null;
		if ( $url ) {
			$image = @$create( $url );
		}
	}

	if ( $image ) {
		$data = array();

		$service = new Image_Manipulation();
		$image   = $service->process_image_blur( $mimetype, $image );

		ob_start();
		$output( $image );
		$contents = ob_get_clean();

		// we know the content come from internal source.
		// phpcs:ignore
		$data['base64'] = base64_encode( $contents );

		return $data;
	}
}

/**
 * Function is attached to wp_generate_attachment_metadata and
 * wp_update_attachment_metadata filter.
 * It generates downscaled and blurred version of the image to postmeta table.
 *
 * @param array $metadata - meta data information about the uploaded attachment.
 * @param int   $id - id of the attachment.
 * @return array $metadata - passed in metadata value.
 */
function process_images( $metadata, $id ) {
	if ( ! wp_attachment_is_image( $id ) ) {
		return $metadata;
	}

	$mimetype = get_post_mime_type( $id );

	if ( ! $mimetype ) {
		return $metadata;
	}

	list($create, $output) = choose_funcs_for_mime_type( $mimetype );

	if ( function_exists( $create ) && function_exists( $output ) ) {
		list('basedir' => $basedir) = wp_upload_dir();
		$sizes                      = parse_sizes_from_metadata( $metadata );

		foreach ( $sizes as $size => $path ) {
			$data = generate_base64( $create, $output, $basedir, $path, $id, $size, $mimetype );

			if ( $data ) {
				if ( 'full' === $size ) {
					$metadata['sizes']['full'] = array(
						'file'      => basename( $metadata['file'] ),
						'width'     => $metadata['width'],
						'height'    => $metadata['height'],
						'mime-type' => $mimetype,
						'base64'    => $data['base64'],
					);
				} else {
					$metadata['sizes'][ $size ] = array_merge( $metadata['sizes'][ $size ], $data );
				}
			}
		}
	}

	return $metadata;
}
