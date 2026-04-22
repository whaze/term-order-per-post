<?php
/**
 * OrderStorage class.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

namespace Whaze\TermOrderPerPost;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes term ordering data stored as post meta.
 */
final class OrderStorage {

	public const META_KEY = '_term_order_per_post';

	/**
	 * Get the ordered term IDs for a given post and taxonomy.
	 *
	 * Invalid IDs (deleted terms) are silently filtered out.
	 * Returns an empty array if no order is defined or the stored data is corrupt.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy slug.
	 *
	 * @return int[]
	 */
	public function getOrder( int $post_id, string $taxonomy ): array {
		$all = $this->getAllOrders( $post_id );

		if ( ! isset( $all[ $taxonomy ] ) ) {
			return [];
		}

		$stored = $all[ $taxonomy ];

		// Filter to valid, currently assigned term IDs.
		$assigned_result = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );

		if ( is_wp_error( $assigned_result ) ) {
			return $stored;
		}

		$assigned_ids = array_map( 'absint', $assigned_result );

		return array_values(
			array_filter(
				$stored,
				static fn( int $id ) => in_array( $id, $assigned_ids, true )
			)
		);
	}

	/**
	 * Persist the ordered term IDs for a given post and taxonomy.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy slug.
	 * @param int[]  $term_ids Ordered array of term IDs.
	 */
	public function setOrder( int $post_id, string $taxonomy, array $term_ids ): void {
		$all              = $this->getAllOrders( $post_id );
		$all[ $taxonomy ] = array_values( array_map( 'absint', $term_ids ) );

		update_post_meta( $post_id, self::META_KEY, wp_json_encode( $all ) );
	}

	/**
	 * Remove the stored order for a given post and taxonomy.
	 *
	 * Deletes the post meta entirely when no taxonomy orders remain.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy slug.
	 */
	public function deleteOrder( int $post_id, string $taxonomy ): void {
		$all = $this->getAllOrders( $post_id );

		unset( $all[ $taxonomy ] );

		if ( empty( $all ) ) {
			delete_post_meta( $post_id, self::META_KEY );
		} else {
			update_post_meta( $post_id, self::META_KEY, wp_json_encode( $all ) );
		}
	}

	/**
	 * Get all stored term orders for a post, keyed by taxonomy slug.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array<string, int[]>
	 */
	public function getAllOrders( int $post_id ): array {
		$raw = get_post_meta( $post_id, self::META_KEY, true );

		if ( ! $raw ) {
			return [];
		}

		$decoded = json_decode( $raw, true );

		if ( ! is_array( $decoded ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error(
				sprintf(
					'term-order-per-post: corrupted meta for post %d — expected JSON array, got: %s',
					absint( $post_id ),
					esc_html( $raw )
				),
				E_USER_WARNING
			);

			return [];
		}

		return array_map(
			static fn( mixed $ids ) => is_array( $ids )
				? array_values( array_map( 'absint', $ids ) )
				: [],
			$decoded
		);
	}
}
