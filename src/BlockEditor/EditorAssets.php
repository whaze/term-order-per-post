<?php
/**
 * EditorAssets class.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

namespace Whaze\TermOrderPerPost\BlockEditor;

use Whaze\TermOrderPerPost\OrderStorage;
use Whaze\TermOrderPerPost\Registry;

/**
 * Enqueues the block editor JS bundle and passes configuration data to the script.
 */
final class EditorAssets {

	/**
	 * Sets up the asset enqueuer.
	 *
	 * @param Registry $registry        Determines which post types / taxonomies are managed.
	 * @param string   $plugin_dir_path Absolute path to the plugin root (with trailing slash).
	 * @param string   $plugin_dir_url  URL to the plugin root (with trailing slash).
	 */
	public function __construct(
		private readonly Registry $registry,
		private readonly string $plugin_dir_path,
		private readonly string $plugin_dir_url,
	) {}

	/**
	 * Enqueue the editor script for the current post type if applicable.
	 */
	public function enqueue(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		$post_type = $screen->post_type;

		if ( empty( $this->registry->getTaxonomiesForPostType( $post_type ) ) ) {
			return;
		}

		$asset_file = $this->plugin_dir_path . 'assets/build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'term-order-per-post-editor',
			$this->plugin_dir_url . 'assets/build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'term-order-per-post-editor',
			'termOrderPerPost',
			[
				'registrations' => $this->registry->getRegistrationsForPostType( $post_type ),
				'metaKey'       => OrderStorage::META_KEY,
				'nonce'         => wp_create_nonce( 'wp_rest' ),
			]
		);

		$this->loadScriptTranslations();
	}

	/**
	 * Inject JS translations via wp.i18n.setLocaleData().
	 *
	 * Uses glob() instead of wp_set_script_translations() because the latter resolves JSON files by hashing the script's full URL path,
	 * which differs per installation. Instead, we load all per-source JSON files produced by
	 * `wp i18n make-json` directly — setLocaleData() merges them additively.
	 */
	private function loadScriptTranslations(): void {
		$locale = determine_locale();
		$files  = glob( $this->plugin_dir_path . 'languages/term-order-per-post-' . $locale . '-*.json' );

		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			$json = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local filesystem path, not a remote URL
			if ( false === $json ) {
				continue;
			}

			$data = json_decode( $json, true );
			if ( ! isset( $data['locale_data']['messages'] ) ) {
				continue;
			}

			wp_add_inline_script(
				'term-order-per-post-editor',
				'wp.i18n.setLocaleData( ' . wp_json_encode( $data['locale_data']['messages'] ) . ', "term-order-per-post" );',
				'before'
			);
		}
	}
}
