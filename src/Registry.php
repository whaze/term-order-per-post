<?php
/**
 * Registry class.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

namespace Whaze\TermOrderPerPost;

/**
 * Stores the list of post type / taxonomy pairs registered for custom term ordering.
 */
final class Registry {

	/**
	 * Registered combinations indexed by post type.
	 *
	 * @var array<string, string[]>
	 */
	private array $registrations = [];

	/**
	 * Register a post type / taxonomy combination for custom term ordering.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $taxonomy  The taxonomy slug.
	 */
	public function register( string $post_type, string $taxonomy ): void {
		if ( ! isset( $this->registrations[ $post_type ] ) ) {
			$this->registrations[ $post_type ] = [];
		}

		if ( ! in_array( $taxonomy, $this->registrations[ $post_type ], true ) ) {
			$this->registrations[ $post_type ][] = $taxonomy;
		}
	}

	/**
	 * Get all taxonomies registered for a given post type.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return string[]
	 */
	public function getTaxonomiesForPostType( string $post_type ): array {
		return $this->registrations[ $post_type ] ?? [];
	}

	/**
	 * Check whether a post type / taxonomy combination is registered.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $taxonomy  The taxonomy slug.
	 */
	public function isRegistered( string $post_type, string $taxonomy ): bool {
		return in_array( $taxonomy, $this->registrations[ $post_type ] ?? [], true );
	}

	/**
	 * Return all registrations.
	 *
	 * @return array<string, string[]>
	 */
	public function all(): array {
		return $this->registrations;
	}

	/**
	 * Return registrations for a specific post type, formatted for JS consumption.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array<int, array{postType: string, taxonomy: string, label: string, restBase: string}>
	 */
	public function getRegistrationsForPostType( string $post_type ): array {
		$result = [];

		foreach ( $this->getTaxonomiesForPostType( $post_type ) as $taxonomy ) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			$label           = $taxonomy_object ? $taxonomy_object->labels->name : $taxonomy;
			// rest_base may differ from the taxonomy slug (e.g. 'category' → 'categories').
			// When not explicitly set, WP leaves it empty and WP_REST_Terms_Controller falls back to the slug.
			$rest_base = ! empty( $taxonomy_object->rest_base ) ? $taxonomy_object->rest_base : $taxonomy;

			$result[] = [
				'postType' => $post_type,
				'taxonomy' => $taxonomy,
				'label'    => $label,
				'restBase' => $rest_base,
			];
		}

		return $result;
	}
}
