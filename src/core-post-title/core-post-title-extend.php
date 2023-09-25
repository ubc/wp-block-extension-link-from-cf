<?php
/**
 * Extend core button block.
 *
 * @package wp_link_from_cf
 */

namespace UBC\CTLT\BLOCKS\EXTENSION\LINK_FROM_CF;

add_filter( 'render_block', __NAMESPACE__ . '\\render_core_post_title_block_content', 10, 3 );

/**
 * Render the Post Button block content.
 *
 * @param  HTML  $content Rendered block content.
 * @param  array $block Block args.
 * @param  array $instance Block instance.
 * @return HTML
 */
function render_core_post_title_block_content( $content, $block, $instance ) {

	if ( 'core/post-title' !== $block['blockName'] ) {
		return $content;
	}

	$post_id   = $instance->context['postId'];
	$is_link   = isset( $block['attrs']['isLink'] ) ? boolval( $block['attrs']['isLink'] ) : false;
	$enable_cf = isset( $block['attrs']['enableCF'] ) ? boolval( $block['attrs']['enableCF'] ) : false;
	$cf_key    = isset( $block['attrs']['CFKey'] ) ? sanitize_key( $block['attrs']['CFKey'] ) : false;
	$link      = '';

	if ( ! $is_link || ! $enable_cf ) {
		return $content;
	}

	if ( $enable_cf && ! empty( $cf_key ) ) {
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
