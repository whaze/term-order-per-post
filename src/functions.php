<?php
/**
 * Public API functions and plugin bootstrap.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Whaze\TermOrderPerPost\BlockEditor\EditorAssets;
use Whaze\TermOrderPerPost\OrderCleaner;
use Whaze\TermOrderPerPost\OrderStorage;
use Whaze\TermOrderPerPost\Plugin;
use Whaze\TermOrderPerPost\Registry;
use Whaze\TermOrderPerPost\RestField;

// Bootstrap: instantiate and register the plugin on `plugins_loaded`.
add_action(
	'plugins_loaded',
	static function (): void {
		$registry = new Registry();
		$storage  = new OrderStorage();

		$plugin = new Plugin(
			$registry,
			$storage,
			new OrderCleaner( $storage, $registry ),
			new RestField( $registry, $storage ),
			new EditorAssets(
				$registry,
				WHAZE_TERM_ORDER_FOR_POSTS_DIR,
				WHAZE_TERM_ORDER_FOR_POSTS_URL
			),
		);

		$plugin->register();

		// Store the registry globally so public functions can access it.
		$GLOBALS['whaze_term_order_for_posts_registry'] = $registry;
		$GLOBALS['whaze_term_order_for_posts_storage']  = $storage;
	}
);

/**
 * Register term ordering for a specific post type and taxonomy combination.
 *
 * Must be called on or after `plugins_loaded` (e.g. inside an `init` callback).
 *
 * @param string $post_type The post type slug.
 * @param string $taxonomy  The taxonomy slug.
 */
function whaze_term_order_for_posts_register( string $post_type, string $taxonomy ): void {
	if ( ! isset( $GLOBALS['whaze_term_order_for_posts_registry'] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'whaze_term_order_for_posts_register() must be called after the plugins_loaded hook.', 'whaze-term-order-for-posts' ),
			'1.0.0'
		);

		return;
	}

	$GLOBALS['whaze_term_order_for_posts_registry']->register( $post_type, $taxonomy );
}

/**
 * Get the terms assigned to a post, sorted by the custom order defined in the editor.
 *
 * Falls back to wp_get_object_terms() if no custom order is defined for the post.
 *
 * @param int    $post_id  The post ID.
 * @param string $taxonomy The taxonomy slug.
 * @param array  $args     Optional. Arguments passed to wp_get_object_terms() as fallback.
 *
 * @return \WP_Term[]|\WP_Error Array of term objects, or WP_Error on failure.
 */
function whaze_term_order_for_posts_get_terms( int $post_id, string $taxonomy, array $args = [] ): array|\WP_Error {
	if ( ! isset( $GLOBALS['whaze_term_order_for_posts_storage'] ) ) {
		return wp_get_object_terms( $post_id, $taxonomy, $args );
	}

	/** @var OrderStorage $storage OrderStorage instance. */ // phpcs:ignore Generic.Commenting.DocComment.MissingShort
	$storage = $GLOBALS['whaze_term_order_for_posts_storage'];
	$order   = $storage->getOrder( $post_id, $taxonomy );

	if ( empty( $order ) ) {
		return wp_get_object_terms( $post_id, $taxonomy, $args );
	}

	$args['include'] = $order;
	$args['orderby'] = 'include';

	return wp_get_object_terms( $post_id, $taxonomy, $args );
}
