<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines which indexes should appear in the sitemap root index.
 *
 * @since 4.0.0
 */
class Root {
	/**
	 * Returns the indexes for the sitemap root index.
	 *
	 * @since 4.0.0
	 *
	 * @return array The indexes.
	 */
	public function indexes() {
		$indexes = [];
		if ( 'general' !== aioseo()->sitemap->type ) {
			foreach ( aioseo()->addons->getLoadedAddons() as $loadedAddon ) {
				if ( ! empty( $loadedAddon->root ) && method_exists( $loadedAddon->root, 'indexes' ) ) {
					$indexes = $loadedAddon->root->indexes();
					if ( $indexes ) {
						return $indexes;
					}
				}
			}

			return $indexes;
		}

		$filename   = aioseo()->sitemap->filename;
		$postTypes  = aioseo()->sitemap->helpers->includedPostTypes();
		$taxonomies = aioseo()->sitemap->helpers->includedTaxonomies();

		$indexes = array_merge( $indexes, $this->getAdditionalIndexes() );

		if ( $postTypes ) {
			$postArchives = [];

			foreach ( $postTypes as $postType ) {
				$postIndexes = $this->buildIndexesPostType( $postType );
				$indexes     = array_merge( $indexes, $postIndexes );

				if (
					get_post_type_archive_link( $postType ) &&
					aioseo()->dynamicOptions->noConflict()->searchAppearance->archives->has( $postType ) &&
					(
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->default ||
						! aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->noindex
					)
				) {
					$postArchives[ $postType ] = aioseo()->sitemap->helpers->lastModifiedPostTime( $postType );
				}
			}

			if ( ! empty( $postArchives ) ) {
				usort( $postArchives, function( $date1, $date2 ) {
					return $date1 < $date2 ? 1 : 0;
				} );

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/post-archive-$filename.xml" ),
					'lastmod' => $postArchives[0],
					'count'   => count( $postArchives )
				];
			}
		}

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$indexes = array_merge( $indexes, $this->buildIndexesTaxonomy( $taxonomy ) );
			}
		}

		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->author &&
			aioseo()->options->searchAppearance->archives->author->show &&
			(
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			$authors = get_users( [
				'has_published_posts' => [ 'post' ]
			] );

			$indexes[] = $this->buildIndex( 'author', count( $authors ) );
		}

		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->date &&
			aioseo()->options->searchAppearance->archives->date->show &&
			(
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			global $wpdb;
			$result = $wpdb->get_results( $wpdb->prepare(
				"SELECT count(*) as amountOfUrls FROM (
					SELECT post_date
					FROM {$wpdb->posts}
					WHERE post_type = %s AND post_status = 'publish'
					GROUP BY
						YEAR(post_date),
						MONTH(post_date)
					LIMIT %d
				) as dates",
				'post',
				50000
			) );

			$indexes[] = $this->buildIndex( 'date', $result[0]->amountOfUrls );
		}

		return apply_filters( 'aioseo_sitemap_indexes', $indexes );
	}

	/**
	 * Returns the additional page indexes.
	 *
	 * @since 4.2.1
	 *
	 * @return array
	 */
	private function getAdditionalIndexes() {
		$additionalPages = [];
		if ( aioseo()->options->sitemap->general->additionalPages->enable ) {
			foreach ( aioseo()->options->sitemap->general->additionalPages->pages as $additionalPage ) {
				$additionalPage = json_decode( $additionalPage );
				if ( empty( $additionalPage->url ) ) {
					continue;
				}

				$additionalPages[] = $additionalPage;
			}
		}

		$indexes         = [];
		$postTypes       = aioseo()->sitemap->helpers->includedPostTypes();
		$additionalPages = apply_filters( 'aioseo_sitemap_additional_pages', $additionalPages );
		if (
			'posts' === get_option( 'show_on_front' ) ||
			count( $additionalPages ) ||
			! in_array( 'page', $postTypes, true )
		) {
			$indexes = $this->buildAdditionalIndexes( $additionalPages );
		}

		return $indexes;
	}

	/**
	 * Builds a given index.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $indexName    The index name.
	 * @param  integer $amountOfUrls The amount of URLs in the index.
	 * @return array                 The index.
	 */
	public function buildIndex( $indexName, $amountOfUrls ) {
		$filename = aioseo()->sitemap->filename;

		return [
			'loc'     => aioseo()->helpers->localizedUrl( "/$indexName-$filename.xml" ),
			'lastmod' => aioseo()->sitemap->helpers->lastModifiedPostTime(),
			'count'   => $amountOfUrls
		];
	}

	/**
	 * Builds the additional pages index.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $amountOfurls The amount of additional pages.
	 * @return array                 The index.
	 */
	public function buildAdditionalIndexes( $entries ) {
		$postTypes             = aioseo()->sitemap->helpers->includedPostTypes();
		$shouldIncludeHomepage = 'posts' === get_option( 'show_on_front' ) || ! in_array( 'page', $postTypes, true );

		if ( $shouldIncludeHomepage ) {
			$homePageEntry               = new \stdClass;
			$homePageEntry->lastModified = aioseo()->sitemap->helpers->lastModifiedPostTime();
			array_unshift( $entries, $homePageEntry );
		}

		if ( ! $entries ) {
			return [];
		}

		$filename       = aioseo()->sitemap->filename;
		$chunks         = aioseo()->sitemap->helpers->chunkEntries( $entries );

		$indexes = [];
		for ( $i = 0; $i < count( $chunks ); $i++ ) {
			$chunk       = array_values( $chunks[ $i ] );
			$indexNumber = 1 < count( $chunks ) ? $i + 1 : '';

			$index = [
				'loc'     => aioseo()->helpers->localizedUrl( "/addl-$filename$indexNumber.xml" ),
				'lastmod' => $chunk[0]->lastModified ? aioseo()->helpers->dateTimeToIso8601( $chunk[0]->lastModified ) : '',
				'count'   => count( $chunks[ $i ] )
			];

			$indexes[] = $index;
			continue;
		}

		return $indexes;
	}

	/**
	 * Builds indexes for all eligible posts of a given post type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $postType The post type.
	 * @return array            The indexes.
	 */
	public function buildIndexesPostType( $postType ) {
		$posts = aioseo()->sitemap->content->posts( $postType, [ 'root' => true ] );

		if ( ! $posts ) {
			foreach ( aioseo()->addons->getLoadedAddons() as $instance ) {
				if ( ! empty( $instance->root ) && method_exists( $instance->root, 'buildIndexesPostType' ) ) {
					$posts = $instance->root->buildIndexesPostType( $postType );
					if ( $posts ) {
						return $this->buildIndexes( $postType, $posts );
					}
				}
			}
		}

		if ( ! $posts ) {
			return [];
		}

		return $this->buildIndexes( $postType, $posts );
	}

	/**
	 *Builds indexes for all eligible terms of a given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy The taxonomy.
	 * @return array            The indexes.
	 */
	public function buildIndexesTaxonomy( $taxonomy ) {
		$terms = aioseo()->sitemap->content->terms( $taxonomy, [ 'root' => true ] );

		if ( ! $terms ) {
			foreach ( aioseo()->addons->getLoadedAddons() as $instance ) {
				if ( ! empty( $instance->root ) && method_exists( $instance->root, 'buildIndexesTaxonomy' ) ) {
					$terms = $instance->root->buildIndexesTaxonomy( $taxonomy );
					if ( $terms ) {
						return $this->buildIndexes( $taxonomy, $terms );
					}
				}
			}
		}

		if ( ! $terms ) {
			return [];
		}

		return $this->buildIndexes( $taxonomy, $terms );
	}

	/**
	 * Builds indexes for a given type.
	 *
	 * Acts as a helper function for buildIndexesPostTypes() and buildIndexesTaxonomies().
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name    The name of the object parent.
	 * @param  array  $entries The sitemap entries.
	 * @return array  $indexes The indexes.
	 */
	public function buildIndexes( $name, $entries ) {
		$filename = aioseo()->sitemap->filename;
		$chunks   = aioseo()->sitemap->helpers->chunkEntries( $entries );
		$indexes  = [];
		for ( $i = 0; $i < count( $chunks ); $i++ ) {
			$chunk       = array_values( $chunks[ $i ] );
			$indexNumber = 0 !== $i && 1 < count( $chunks ) ? $i + 1 : '';

			$index = [
				'loc'   => aioseo()->helpers->localizedUrl( "/$name-$filename$indexNumber.xml" ),
				'count' => count( $chunks[ $i ] )
			];

			if ( isset( $entries[0]->ID ) ) {
				$ids = array_map( function( $post ) {
					return $post->ID;
				}, $chunk );
				$ids = implode( "', '", $ids );

				$lastModified = null;
				if ( ! apply_filters( 'aioseo_sitemap_lastmod_disable', false ) ) {
					$lastModified = aioseo()->core->db
						->start( aioseo()->core->db->db->posts . ' as p', true )
						->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
						->whereRaw( "( `p`.`ID` IN ( '$ids' ) )" )
						->run()
						->result();
				}

				if ( ! empty( $lastModified[0]->last_modified ) ) {
					$index['lastmod'] = aioseo()->helpers->dateTimeToIso8601( $lastModified[0]->last_modified );
				}
				$indexes[] = $index;
				continue;
			}

			$termIds = [];
			foreach ( $chunk as $term ) {
				$termIds[] = $term->term_id;
			}
			$termIds = implode( "', '", $termIds );

			$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';

			$lastModified = null;
			if ( ! apply_filters( 'aioseo_sitemap_lastmod_disable', false ) ) {
				$lastModified = aioseo()->core->db
					->start( aioseo()->core->db->db->posts . ' as p', true )
					->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
					->whereRaw( "
					( `p`.`ID` IN
						(
							SELECT `tr`.`object_id`
							FROM `$termRelationshipsTable` as tr
							WHERE `tr`.`term_taxonomy_id` IN ( '$termIds' )
						)
					)" )
					->run()
					->result();
			}

			if ( ! empty( $lastModified[0]->last_modified ) ) {
				$index['lastmod'] = aioseo()->helpers->dateTimeToIso8601( $lastModified[0]->last_modified );
			}
			$indexes[] = $index;
		}

		return $indexes;
	}
}