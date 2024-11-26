<?php
/**
 * Plugin Name:     Image Base64
 * Plugin URI:      https://gist.github.com/lightningspirit/bb51e110d92821b724ab53bf2e07cb87
 * Description:     Generate base64 encode versions of images
 * Version:         0.2.1
 * Requires:        PHP: 7.4
 * Author:          Move Your Digital, Inc.
 * Author URI:      https://moveyourdigital.com
 * License:         GPLv2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:      https://github.com/moveyourdigital/wp-image-base64/raw/main/wp-info.json
 * Text Domain:     image-base64
 * Domain Path:     /languages
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use any URL path relative to this plugin
 *
 * @param string $path the path.
 * @return string
 */
function plugin_uri( $path ) {
	return plugins_url( $path, __FILE__ );
}

/**
 * Use any directory relative to this plugin
 *
 * @since 0.1.0
 * @param string $path the path.
 * @return string
 */
function plugin_dir( $path ) {
	return plugin_dir_path( __FILE__ ) . $path;
}

/**
 * Gets the plugin unique identifier
 * based on 'plugin_basename' call.
 *
 * @since 0.1.0
 * @return string
 */
function plugin_file() {
	return plugin_basename( __FILE__ );
}

/**
 * Gets the plugin basedir
 *
 * @since 0.1.0
 * @return string
 */
function plugin_slug() {
	return dirname( plugin_file() );
}

/**
 * Gets the plugin version.
 *
 * @since 0.1.0
 * @param bool $revalidate force plugin revalidation.
 * @return string
 */
function plugin_data( bool $revalidate = false ) {
	if ( true === $revalidate ) {
		delete_transient( 'plugin_data_' . plugin_file() );
	}

	$plugin_data = get_transient( 'plugin_data_' . plugin_file() );

	if ( ! $plugin_data ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_data = array_intersect_key(
			$plugin_data,
			array_flip(
				array( 'Version', 'UpdateURI' )
			)
		);

		set_transient( 'plugin_data' . plugin_file(), $plugin_data );
	}

	return $plugin_data;
}

/**
 * Get plugin version
 *
 * @return string|null
 */
function plugin_version() {
	$data = plugin_data();

	if ( isset( $data['Version'] ) ) {
		return $data['Version'];
	}
}

/**
 * Get plugin update URL
 *
 * @return string|null
 */
function plugin_update_uri() {
	$data = plugin_data();

	if ( isset( $data['UpdateURI'] ) ) {
		return $data['UpdateURI'];
	}
}

/**
 * Gets the action hook for first process
 *
 * @return string
 */
function hook_process_old_media() {
	return 'image-base64_hook_process_media';
}

/**
 * Load plugin translations and post type
 *
 * @since 0.1.0
 */
add_action(
	'init',
	function () {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'wp-graphql/wp-graphql.php' ) ) {
			include __DIR__ . '/inc/wp-graphql.php';
		}

		include __DIR__ . '/inc/class-image-manipulation.php';
		include __DIR__ . '/inc/functions.php';
		include __DIR__ . '/inc/hooks.php';
		include __DIR__ . '/inc/updater.php';
	}
);

/**
 * Schedule a cron to build base64 of all existing images
 *
 * @since 0.1.0
 */
register_activation_hook(
	__FILE__,
	function () {
		if ( ! wp_next_scheduled( hook_process_old_media() ) ) {
			wp_schedule_single_event( time(), hook_process_old_media() );
		}
	}
);

/**
 * Remove cron hook on deactivation
 *
 * @since 0.1.0
 */
register_deactivation_hook(
	__FILE__,
	function () {
		$timestamp = wp_next_scheduled( hook_process_old_media() );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, hook_process_old_media() );
		}
	}
);
