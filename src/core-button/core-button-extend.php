<?php
/**
 * Extend core button block.
 *
 * @package wp_link_from_cf
 */

namespace UBC\CTLT\BLOCKS\EXTENSION\LINK_FROM_CF;

add_filter( 'register_block_type_args', __NAMESPACE__ . '\\render_core_button_block', 10, 3 );

/**
 * Render the Post Button block in the frontend.
 *
 * @param  array  $args Registered block args.
 * @param  string $name Block name.
 * @return array
 */
function render_core_button_block( $args, $name ) {

	if ( 'core/button' === $name ) {
		$args['uses_context']    = array( 'postId' );
		$args['render_callback'] = __NAMESPACE__ . '\\render_core_button_block_content';
	}

	return $args;
}//end render_core_button_block()


/**
 * Render the Post Button block content.
 *
 * @param  mixed $attributes Block attributes.
 * @param  mixed $content Rendered block content.
 * @param  mixed $block Block args.
 * @return HTML
 */
function render_core_button_block_content( $attributes, $content, $block ) {

	$post_id      = $block->context['postId'];
	$link_to_post = isset( $attributes['linkToPost'] ) ? boolval( $attributes['linkToPost'] ) : false;
	$enable_cf    = isset( $attributes['enableCF'] ) ? boolval( $attributes['enableCF'] ) : false;
	$cf_key       = isset( $attributes['CFKey'] ) ? sanitize_key( $attributes['CFKey'] ) : false;
	$link         = '';

	if ( ! $link_to_post && ! $enable_cf ) {
		return $content;
	}

	if ( $link_to_post && ! $enable_cf ) {
		$link = get_permalink( $post_id );
	}

	if ( $enable_cf && ! $link_to_post && ! empty( $cf_key ) ) {
		$meta = get_post_meta( $post_id, $cf_key, true );

		if ( false !== $meta && ! empty( $meta ) ) {
			$link = $meta;
		}
	}

	$found = preg_match(
		'/href="[^"]*"/',
		$content
	);

	return $found ? preg_replace(
		'/href="[^"]*"/',
		'href="' . esc_url( $link ) . '"',
		$content,
		1
	) : preg_replace(
		'/<a/',
		'<a href="' . esc_url( $link ) . '"',
		$content,
		1
	);
}
