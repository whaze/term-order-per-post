<?php
/**
 * Main plugin class.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

namespace Whaze\TermOrderPerPost;

use Whaze\TermOrderPerPost\BlockEditor\EditorAssets;

/**
 * Orchestrates all plugin hooks.
 */
final class Plugin {

	/**
	 * Injects dependencies.
	 *
	 * @param Registry     $registry      Stores post type / taxonomy registrations.
	 * @param OrderStorage $storage       Reads and writes term order data.
	 * @param OrderCleaner $cleaner       Keeps order in sync on term changes.
	 * @param RestField    $rest_field    Registers the REST API field.
	 * @param EditorAssets $editor_assets Enqueues the block editor script.
	 */
	public function __construct(
		private readonly Registry $registry,
		private readonly OrderStorage $storage,
		private readonly OrderCleaner $cleaner,
		private readonly RestField $rest_field,
		private readonly EditorAssets $editor_assets,
	) {}

	/**
	 * Register all plugin hooks.
	 */
	public function register(): void {
		// Priority 99: must run after theme/plugin term_order_per_post_register() calls (priority 10).
		add_action( 'init', [ $this, 'registerPostMeta' ], 99 );
		add_action( 'rest_api_init', [ $this->rest_field, 'register' ] );
		add_action( 'set_object_terms', [ $this->cleaner, 'onSetObjectTerms' ], 10, 6 );
		add_action( 'enqueue_block_editor_assets', [ $this->editor_assets, 'enqueue' ] );
	}

	/**
	 * Register the post meta so it is accessible via the REST API and Gutenberg.
	 *
	 * Called on `init` (after `term_order_per_post_register()` calls have run)
	 * so we can target only the relevant post types.
	 */
	public function registerPostMeta(): void {
		foreach ( array_keys( $this->registry->all() ) as $post_type ) {
			register_post_meta(
				$post_type,
				OrderStorage::META_KEY,
				[
					'type'          => 'string',
					'description'   => 'Serialised JSON map of taxonomy => ordered term IDs.',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
				]
			);
		}
	}
}
