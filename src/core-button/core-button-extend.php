<?php
/**
 * Extend core button block.
 *
 * @package wp_link_from_cf
 */

namespace UBC\CTLT\BLOCKS\EXTENSION\LINK_FROM_CF;

add_filter( 'render_block', __NAMESPACE__ . '\\render_core_button_block_content', 10, 3 );


	/**
	 * Render the Post Button block content.
	 *
	 * @param mixed $content Rendered block content.
	 * @param mixed $block Block args.
	 * @param mixed $instance Block instance.
	 * @return string HTML
	 */
function render_core_button_block_content( $content, $block, $instance ) {

	if ( 'core/button' !== $block['blockName'] ) {
		return $content;
	}

	$post_id         = $instance->context['postId'];
	$link_to_post    = isset( $block['attrs']['linkToPost'] ) ? boolval( $block['attrs']['linkToPost'] ) : false;
	$enable_cf       = isset( $block['attrs']['enableCF'] ) ? boolval( $block['attrs']['enableCF'] ) : false;
	$cf_key          = isset( $block['attrs']['CFKey'] ) ? sanitize_key( $block['attrs']['CFKey'] ) : false;
	$open_in_new_tab = isset( $block['attrs']['openInNewTab'] ) ? boolval( $block['attrs']['openInNewTab'] ) : false;
	$link            = '';

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

	$content = $found ? preg_replace(
		'/href="[^"]*"/',
		'href="' . esc_url( $link ) . '"',
		$content,
		1
	) : preg_replace(
		'/<a/',
		'<a href="' . esc_url( $link ) . '" ',
		$content,
		1
	);

	// Open in new tab.
	if ( $open_in_new_tab ) {
		$content = preg_replace(
			'/<a/',
			'<a target="_blank" ',
			$content,
			1
		);
	}

	return $content;
}
