<?php
/**
 * OrderCleaner class.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

namespace Whaze\TermOrderPerPost;

/**
 * Keeps stored term order in sync when terms are removed from a post.
 */
final class OrderCleaner {

	/**
	 * Injects dependencies.
	 *
	 * @param OrderStorage $storage  Reads and writes order data.
	 * @param Registry     $registry Determines which post types / taxonomies are managed.
	 */
	public function __construct(
		private readonly OrderStorage $storage,
		private readonly Registry $registry,
	) {}

	/**
	 * Fires after terms are set on an object.
	 *
	 * Removes from the stored order any term IDs that are no longer assigned.
	 * Cleans up the stored entry entirely when no terms remain ordered.
	 *
	 * @param int    $object_id  The object ID (post ID).
	 * @param int[]  $terms      Array of term IDs that were just set.
	 * @param int[]  $tt_ids     Array of term taxonomy IDs.
	 * @param string $taxonomy   The taxonomy slug.
	 * @param bool   $append     Whether terms were appended or replaced.
	 * @param int[]  $old_tt_ids Array of old term taxonomy IDs.
	 */
	public function onSetObjectTerms( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- $append and $old_tt_ids required by set_object_terms hook signature
		int $object_id,
		array $terms,
		array $tt_ids,
		string $taxonomy,
		bool $append,
		array $old_tt_ids
	): void {
		$post_type = get_post_type( $object_id );

		if ( ! $post_type || ! $this->registry->isRegistered( $post_type, $taxonomy ) ) {
			return;
		}

		$stored_order = $this->storage->getOrder( $object_id, $taxonomy );

		if ( empty( $stored_order ) ) {
			return;
		}

		$new_term_ids = array_map( 'absint', $terms );
		$filtered     = array_values(
			array_filter(
				$stored_order,
				static fn( int $id ) => in_array( $id, $new_term_ids, true )
			)
		);

		if ( empty( $filtered ) ) {
			$this->storage->deleteOrder( $object_id, $taxonomy );
		} elseif ( count( $filtered ) !== count( $stored_order ) ) {
			$this->storage->setOrder( $object_id, $taxonomy, $filtered );
		}
	}
}
