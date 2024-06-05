<?php
/**
 *
 * Plugin Name:       WP Block Extension - Link from CF
 * Description:       Allow blocks to link to URL exists in custom field.
 * Version:           1.0
 * Author:            Kelvin Xu
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-link-from-cf
 *
 * @package wp_link_from_cf
 */

namespace UBC\CTLT\BLOCKS\EXTENSION\LINK_FROM_CF;

define( 'LINK_FROM_CF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LINK_FROM_CF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once LINK_FROM_CF_PLUGIN_DIR . 'src/core-button/core-button-extend.php';
require_once LINK_FROM_CF_PLUGIN_DIR . 'src/core-post-title/core-post-title-extend.php';

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_assets' );

/**
 * Enqueue block assets.
 *
 * @return void
 */
function enqueue_assets() {

	wp_enqueue_script(
		'wp-block-extension-link-from-cf-js',
		plugin_dir_url( __FILE__ ) . 'build/script.js',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/script.js' ),
		true
	);

	wp_localize_script(
		'wp-block-extension-link-from-cf-js',
		'wp_link_from_cf',
		array(
			'nonce' => wp_create_nonce( 'wp_link_from_cf' ),
		)
	);
}//end enqueue_assets()

add_action( 'wp_ajax_wp_link_from_cf_get_meta_keys', __NAMESPACE__ . '\\get_meta_keys' );

/**
 * Ajax request handler to return the list of meta keys from the post meta table.
 *
 * @return void
 */
function get_meta_keys() {
	global $wpdb;

	// phpcs:ignore
	wp_verify_nonce( $_POST['nonce'], 'wp_link_from_cf' );

	$keys = get_transient( 'wp_link_from_cf' );
	if ( false !== $keys ) {
		wp_send_json_success( $keys );
	}

	$keys = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT meta_key
			FROM $wpdb->postmeta
			WHERE meta_key NOT BETWEEN '_' AND '_z'
			HAVING meta_key NOT LIKE %s
			ORDER BY meta_key",
			$wpdb->esc_like( '_' ) . '%'
		)
	);

	set_transient( 'wp_link_from_cf', $keys, HOUR_IN_SECONDS );

	wp_send_json_success( $keys );
}//end get_meta_keys()

add_action( 'updated_post_meta', __NAMESPACE__ . '\\reset_metakeys_transient' );

/**
 * Delete `wp_metadata_filter_get_keys` transient when any of the post metas is updated.
 */
function reset_metakeys_transient() {
	if ( false !== get_transient( 'wp_link_from_cf' ) ) {
		delete_transient( 'wp_link_from_cf' );
	}
}//end reset_metakeys_transient()
