<?php

namespace dfwood\WordPress;

/**
 * Class PostMeta
 * @author David Wood <david@davidwood.ninja>
 * @link https://davidwood.ninja/
 * @license GPLv3+
 * @package dfwood\WordPress
 */
class PostMeta {

	/**
	 * @var array WP_LOCK_KEYS A list of WordPress' post edit lock meta keys.
	 * These should typically be excluded when deleting or copying all post meta.
	 */
	const WP_LOCK_KEYS = [
		'_edit_lock',
		'_edit_last',
	];

	/**
	 * Copies all non-excluded meta values, by key, from one post to another.
	 *
	 * @param int $originalId ID of the post to copy meta values from.
	 * @param int $destinationId ID of the post to copy meta values to.
	 * @param array $exclude Array of meta keys to exclude from copying.
	 */
	public static function copyAll( $originalId, $destinationId, array $exclude = [] ) {
		$meta = get_post_meta( $originalId );
		foreach ( $meta as $key => $values ) {
			if ( ! in_array( $key, $exclude, true ) ) {
				// Check and see if there is only 1 value for this key.
				if ( 1 === count( $values ) ) {
					// If only 1 value, allow an existing value (if any) to be overwritten.
					// Use `reset()` to ensure the correct value is retrieved.
					update_post_meta( $destinationId, $key, maybe_unserialize( reset( $values ) ) );
				} else {
					// Otherwise, use add_post_meta as there are multiple values with the same key.
					foreach ( $values as $value ) {
						add_post_meta( $destinationId, $key, maybe_unserialize( $value ) );
					}
				}
			}
		}
	}

	/**
	 * Deletes all post meta values on the provided post. Does NOT delete
	 * any meta values that have a matching key in `$excludedKeys`.
	 *
	 * @param int $postId ID of the post to delete all post meta for.
	 * @param array $excludeKeys Array of meta keys to exclude from deletion.
	 */
	public static function deleteAll( $postId, array $excludeKeys = [] ) {
		$metaKeys = get_post_custom_keys( $postId );
		foreach ( $metaKeys as $key ) {
			if ( ! in_array( $key, $excludeKeys, true ) ) {
				delete_post_meta( $postId, $key );
			}
		}
	}

}
