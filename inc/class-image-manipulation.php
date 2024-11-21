<?php
/**
 * Image Manipulation class
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
 * Class for manipulating images.
 */
class Image_Manipulation {
	/**
	 * Processing function for images. We need mime type so we can process pngs with it's own method.
	 *
	 * @param string   $mime - mime type of the image object.
	 * @param resource $image - Image object.
	 * @return resource - modified Image object.
	 */
	public function process_image_blur( string $mime, $image ) {
		switch ( $mime ) {
			case 'image/png':
				return $this->process_png_blur( $image );
			default:
				return $this->process_generic_blur( $image );
		}
	}

	/**
	 * Downscales passed in image while keeping aspect ratio to defined width and returns new downscaled image.
	 *
	 * @param resource | \GdImage $image - Image object.
	 * @return resource | \GdImage - Downscaled image object.
	 */
	public function downscale( $image ) {
		$width = apply_filters( 'image_base64_width', 8 );
		return imagescale( $image, $width );
	}

	/**
	 * Applies gaussian blur to passed in image.
	 * Blur's strength is applied using same function over and over again to the image object.
	 *
	 * @param resource | \GdImage $image - Image object.
	 */
	public function gaussian_blur( $image ): void {
		$strength = apply_filters( 'image_base64_gaussian_blur_strength', 1 );
		for ( $i = 1; $i <= $strength; $i++ ) {
			imagefilter( $image, IMG_FILTER_GAUSSIAN_BLUR );
		}
	}

	/**
	 * A generic process function for images.
	 *
	 * @param resource $image - image object that needs processing.
	 * @return resource $downscaled - downscaled and blurred image.
	 */
	public function process_generic_blur( $image ) {
		$downscaled = $this->downscale( $image );
		$this->gaussian_blur( $downscaled );
		return $downscaled;
	}

	/**
	 * To keep transparency in png images, we need to process them using this function.
	 *
	 * @param resource | \GdImage $image - Image object.
	 * @return resource - modified Image object.
	 */
	public function process_png_blur( $image ) {
		$width  = imagesx( $image );
		$height = imagesy( $image );

		// create empty copy of passed in image using true color.
		$new_image = imagecreatetruecolor( $width, $height );

		// downscale and apply needed alpha and blending.
		$new_image = $this->downscale( $new_image );
		imagealphablending( $new_image, false );
		imagesavealpha( $new_image, true );

		$ds_width  = imagesx( $new_image );
		$ds_height = imagesy( $new_image );

		// fill copy with transparent rectangle.
		$transparency = imagecolorallocatealpha( $new_image, 255, 255, 255, 127 );
		imagefilledrectangle( $new_image, 0, 0, $ds_width, $ds_height, $transparency );

		// paste image inside the copy.
		imagecopyresampled( $new_image, $image, 0, 0, 0, 0, $ds_width, $ds_height, $width, $height );

		$this->gaussian_blur( $new_image );

		return $new_image;
	}
}
