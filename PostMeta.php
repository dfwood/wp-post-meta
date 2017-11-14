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
		self::_copyAll( $originalId, $destinationId, $exclude );
	}

	/**
	 * Copies all non-excluded meta values, by key, from one post/revision to a revision.
	 *
	 * This differs from `copyAll()` in that it will allow copying to a WordPress revision. Copying from a WordPress
	 * revision is always possible. This is still considered safe to use for copying to a post IF you are certain your
	 * destination id is correct (i.e. not a revision).
	 *
	 * @param int $originalId ID of the post to copy meta values from.
	 * @param int $destinationId ID of the post to copy meta values to.
	 * @param array $exclude Array of meta keys to exclude from copying.
	 */
	public static function copyAllToRevision( $originalId, $destinationId, array $exclude = [] ) {
		self::_copyAll( $originalId, $destinationId, $exclude, true );
	}

	/**
	 * Internal helper method to consolidate copy all logic.
	 *
	 * @internal
	 *
	 * @param int $originalId ID of the post to copy meta values from.
	 * @param int $destinationId ID of the post to copy meta values to.
	 * @param array $exclude Array of meta keys to exclude from copying.
	 * @param bool $isRevision If destination is a revision, this needs to be true for it to work.
	 */
	protected static function _copyAll( $originalId, $destinationId, array $exclude = [], $isRevision = false ) {
		$meta = get_post_meta( $originalId );
		foreach ( $meta as $key => $values ) {
			if ( ! in_array( $key, $exclude, true ) ) {
				// Check and see if there is only 1 value for this key.
				if ( 1 === count( $values ) ) {
					// If only 1 value, allow an existing value (if any) to be overwritten. Use `reset()` to ensure the correct value is retrieved.
					if ( $isRevision ) {
						update_metadata( 'post', $destinationId, $key, self::_preparePostMetaData( reset( $values ) ) );
					} else {
						update_post_meta( $destinationId, $key, self::_preparePostMetaData( reset( $values ) ) );
					}
				} else {
					// Otherwise, use add_post_meta as there are multiple values with the same key.
					foreach ( $values as $value ) {
						if ( $isRevision ) {
							add_metadata( 'post', $destinationId, $key, self::_preparePostMetaData( $value ) );
						} else {
							add_post_meta( $destinationId, $key, self::_preparePostMetaData( $value ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Deletes all post meta values on the provided post. Does NOT delete any meta values that have a matching key in
	 * `$excludedKeys`.
	 *
	 * @param int $postId ID of the post to delete all post meta for.
	 * @param array $exclude Array of meta keys to exclude from deletion.
	 */
	public static function deleteAll( $postId, array $exclude = [] ) {
		self::_deleteAll( $postId, $exclude );
	}

	/**
	 * Deletes all post meta values on the provided post/revision. Does NOT delete any meta values that have a matching
	 * key in `$excludedKeys`.
	 *
	 * This differs from `deleteAll()` in that it will allow deleting from a WordPress revision. This is still
	 * considered safe to use for deleting from a post IF you are certain your post id is correct (i.e. not a
	 * revision).
	 *
	 * @param int $postId ID of the post to delete all post meta for.
	 * @param array $exclude Array of meta keys to exclude from deletion.
	 */
	public static function deleteAllFromRevision( $postId, array $exclude = [] ) {
		self::_deleteAll( $postId, $exclude, true );
	}

	/**
	 * Internal helper method to consolidate delete all logic.
	 *
	 * @internal
	 *
	 * @param int $postId ID of the post to delete all post meta for.
	 * @param array $exclude Array of meta keys to exclude from deletion.
	 * @param bool $isRevision If post is a revision, this needs to be true for it to work.
	 */
	protected static function _deleteAll( $postId, array $exclude = [], $isRevision = false ) {
		$metaKeys = get_post_custom_keys( $postId );
		foreach ( $metaKeys as $key ) {
			if ( ! in_array( $key, $exclude, true ) ) {
				if ( $isRevision ) {
					delete_metadata( 'post', $postId, $key );
				} else {
					delete_post_meta( $postId, $key );
				}
			}
		}
	}

	/**
	 * Prepares post meta values to be passed to `add/update_metadata()`. Ensures data has proper slashing.
	 *
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	protected static function _preparePostMetaData( $data ) {
		$data = maybe_unserialize( $data );
		if ( is_string( $data ) || is_array( $data ) ) {
			$data = wp_slash( $data );
		}

		return $data;
	}

}
