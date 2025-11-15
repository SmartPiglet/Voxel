<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

function get_current_post(): ?\Voxel\Post {
	global $post;
	if ( $post instanceof \WP_Post ) {
		return \Voxel\Post::get( $post );
	} else {
		$queried_object = get_queried_object();
		if ( $queried_object instanceof \WP_Post ) {
			return \Voxel\Post::get( $queried_object );
		}
	}

	return null;
}

function set_current_post( \Voxel\Post $the_post ) {
	global $post;
	$post = $the_post->get_wp_post_object();
	setup_postdata( $post );
}

function get_current_post_type() {
	$post = \Voxel\get_current_post();
	return $post ? $post->post_type : null;
}

function get_current_author() {
	$post = \Voxel\get_current_post();
	return $post ? $post->get_author() : null;
}

function get_current_term(): ?\Voxel\Term {
	global $vx_current_term;
	if ( $vx_current_term instanceof \Voxel\Term ) {
		return $vx_current_term;
	} else {
		$queried_object = get_queried_object();
		if ( $queried_object instanceof \WP_Term ) {
			return \Voxel\Term::get( $queried_object );
		}
	}

	return null;
}

function set_current_term( ?\Voxel\Term $term ) {
	$GLOBALS['vx_current_term'] = $term;
}

function get_search_results( $request, $options = [] ) {
	$options = array_merge( [
		'limit' => 10,
		'render' => true,
		'render_cards_with_markers' => false,
		'ids' => null,
		'template_id' => null,
		'get_total_count' => false,
		'exclude' => [],
		'offset' => 0,
		'priority_min' => null,
		'priority_max' => null,
		'preload_additional_ids' => 1,
		'pg' => null,
		'apply_conditional_logic' => false,
	], $options );

	if ( is_int( $options['preload_additional_ids'] ) && $options['preload_additional_ids'] > 0 ) {
		$preload_additional_ids = \Voxel\clamp( $options['preload_additional_ids'], 0, apply_filters( 'voxel/load_additional_markers/max_limit', 1000 ) );
	} else {
		$preload_additional_ids = 1;
	}

	$max_limit = apply_filters( 'voxel/get_search_results/max_limit', 500 );
	$limit = min( $options['limit'], $max_limit );

	$results = [
		'ids' => [],
		'render' => null,
		'has_next' => false,
		'has_prev' => false,
		'templates' => null,
		'scripts' => '',
		'additional_ids' => [],
		'template_id' => null,
	];

	$post_type = \Voxel\Post_Type::get( sanitize_text_field( $request['type'] ?? '' ) );
	if ( ! $post_type ) {
		return $results;
	}

	$template_id = $post_type->get_templates()['card'];
	if ( is_numeric( $options['template_id'] ) ) {
		$custom_card_templates = array_column( $post_type->templates->get_custom_templates()['card'], 'id' );
		if ( in_array( $options['template_id'], $custom_card_templates ) ) {
			$template_id = $options['template_id'];
		}
	}

	if ( ! \Voxel\template_exists( $template_id ) ) {
		return $results;
	}

	$results['template_id'] = $template_id;

	if ( $options['render'] && ( $GLOBALS['vx_preview_card_level'] ?? 0 ) > 1 ) {
		$results['ids'] = [];
	} elseif ( is_array( $options['ids'] ) ) {
		$results['ids'] = $options['ids'];
	} else {
		$args = [];
		foreach ( $post_type->get_filters() as $filter ) {
			if ( isset( $request[ $filter->get_key() ] ) ) {
				$args[ $filter->get_key() ] = $request[ $filter->get_key() ];
			}
		}

		if ( $options['apply_conditional_logic'] ) {
			$parsed_values = [];
			foreach ( $post_type->get_filters() as $filter ) {
				if ( array_key_exists( $filter->get_key(), $request ) ) {
					$parsed_values[ $filter->get_key() ] = $filter->parse_value( $request[ $filter->get_key() ] );
				}
			}

			foreach ( $post_type->get_filters() as $filter ) {
				if ( ! $filter->passes_conditional_logic( $parsed_values ) ) {
					unset( $args[ $filter->get_key() ] );
				}
			}
		}

		$args['limit'] = absint( $limit );
		$page = absint( $options['pg'] ?? $request['pg'] ?? 1 );

		if ( $page > 1 ) {
			$args['offset'] = ( $args['limit'] * ( $page - 1 ) );
		}

		if ( $options['offset'] >= 1 ) {
			if ( ! isset( $args['offset'] ) ) {
				$args['offset'] = absint( $options['offset'] );
			} else {
				$args['offset'] += absint( $options['offset'] );
			}
		}

		$args['limit'] += $preload_additional_ids;

		$cb = function( $query ) use ( $options ) {
			if ( ! empty( $options['exclude'] ) && is_array( $options['exclude'] ) ) {
				$exclude_ids = array_values( array_filter( array_map( 'absint', $options['exclude'] ) ) );
				if ( ! empty( $exclude_ids ) ) {
					if ( count( $exclude_ids ) === 1 ) {
						$query->where( sprintf(
							'`%s`.post_id <> %d',
							$query->table->get_escaped_name(),
							$exclude_ids[0]
						) );
					} else {
						$query->where( sprintf(
							'`%s`.post_id NOT IN (%s)',
							$query->table->get_escaped_name(),
							join( ',', $exclude_ids )
						) );
					}
				}
			}

			$min_priority = is_numeric( $options['priority_min'] ) ? intval( $options['priority_min'] ) : null;
			$max_priority = is_numeric( $options['priority_max'] ) ? intval( $options['priority_max'] ) : null;

			if ( $min_priority !== null && $max_priority !== null ) {
				if ( $min_priority === $max_priority ) {
					$query->where( sprintf(
						'priority = %d',
						$min_priority
					) );
				} elseif ( $min_priority <= $max_priority ) {
					$query->where( sprintf(
						'priority >= %d AND priority <= %d',
						$min_priority,
						$max_priority
					) );
				}
			} elseif ( $min_priority !== null ) {
				$query->where( sprintf(
					'priority >= %d',
					$min_priority
				) );
			} elseif ( $max_priority !== null ) {
				$query->where( sprintf(
					'priority <= %d',
					$max_priority
				) );
			}
		};

		$_start = microtime( true );
		$post_ids = $post_type->query( $args, $cb );

		if ( $options['get_total_count'] ) {
			$results['total_count'] = $post_type->get_index_query()->get_post_count( $args, $cb );
		}

		$_query_time = microtime( true ) - $_start;

		$results['has_prev'] = $page > 1;
		$original_limit = ( $args['limit'] - $preload_additional_ids );
		if ( count( $post_ids ) > $original_limit ) {
			$results['has_next'] = true;

			$additional_ids = array_splice( $post_ids, $original_limit );
			$results['additional_ids'] = $additional_ids;
		}

		$results['ids'] = $post_ids;

		do_action( 'qm/info', sprintf( 'Query time: %sms', round( $_query_time * 1000, 1 ) ) );
		do_action( 'qm/info', trim( $post_type->get_index_query()->get_sql( $args ) ) );
	}

	if ( $options['render'] ) {
		if ( $options['render'] === 'markers' ) {
			do_action( 'qm/start', 'render_additional_markers' );

			_prime_post_caches( array_map( 'absint', $results['ids'] ) );

			ob_start();
			$current_request_post = \Voxel\get_current_post();

			$has_results = false;

			foreach ( $results['ids'] as $i => $post_id ) {
				$post = \Voxel\Post::get( $post_id );
				if ( ! $post ) {
					continue;
				}

				$has_results = true;

				\Voxel\set_current_post( $post );

				echo '<div class="ts-marker-wrapper hidden">';
				echo _post_get_marker( $post );
				echo '</div>';

				do_action( 'qm/lap', 'render_additional_markers' );
			}

			// reset current post
			if ( $current_request_post ) {
				\Voxel\set_current_post( $current_request_post );
			}

			if ( \Voxel\is_dev_mode() ) { ?>
				<script type="text/javascript">
					<?php if ( ! is_array( $options['ids'] ) ): ?>
						console.log('Additional markers query time: %c' + <?= round( ( $_query_time ?? 0 ) * 1000, 1 ) ?> + 'ms', 'color: #81c784;');
					<?php endif ?>
				</script>
			<?php }

			$results['render'] = ob_get_clean();

			do_action( 'qm/stop', 'render_markers' );
		} else {
			if ( ! isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
				$GLOBALS['vx_preview_card_current_ids'] = $results['ids'];
			}

			if ( ! isset( $GLOBALS['vx_preview_card_level'] ) ) {
				$GLOBALS['vx_preview_card_level'] = 0;
			}

			if ( $GLOBALS['vx_preview_card_level'] > 1 ) {
				$results['render'] = '';
			} else {
				$previous_ids = $GLOBALS['vx_preview_card_current_ids'];
				$GLOBALS['vx_preview_card_current_ids'] = $results['ids'];
				$GLOBALS['vx_preview_card_level']++;

				do_action( 'qm/start', 'render_search_results' );
				do_action( 'voxel/before_render_search_results' );

				_prime_post_caches( array_map( 'absint', $results['ids'] ) );

				ob_start();
				$current_request_post = \Voxel\get_current_post();

				$has_results = false;

				add_filter( 'elementor/frontend/builder_content/before_print_css', '__return_false' );

				foreach ( $results['ids'] as $i => $post_id ) {
					$post = \Voxel\Post::get( $post_id );
					if ( ! $post ) {
						continue;
					}

					if ( is_admin() ) {
						\Voxel\print_template_css( $template_id );
					}

					$has_results = true;
					\Voxel\set_current_post( $post );

					echo '<div class="ts-preview" data-post-id="'.$post_id.'">';
					\Voxel\print_template( $template_id );

					if ( $options['render_cards_with_markers'] ) {
						if ( $GLOBALS['vx_preview_card_level'] === 1 ) {
							echo '<div class="ts-marker-wrapper hidden">';
							echo _post_get_marker( $post );
							echo '</div>';
						}
					}

					echo '</div>';

					do_action( 'qm/lap', 'render_search_results' );
				}

				// reset current post
				if ( $current_request_post ) {
					\Voxel\set_current_post( $current_request_post );
				}

				if ( \Voxel\is_dev_mode() ) { ?>
					<script type="text/javascript">
						<?php if ( ! is_array( $options['ids'] ) ): ?>
							console.log('Query time: %c' + <?= round( ( $_query_time ?? 0 ) * 1000, 1 ) ?> + 'ms', 'color: #81c784;');
						<?php endif ?>
					</script>
				<?php }

				$results['render'] = ob_get_clean();

				if ( ! \Voxel\is_edit_mode() ) {
					\Elementor\Plugin::$instance->frontend->register_styles();
				}

				wp_enqueue_style( 'vx:post-feed.css' );
				ob_start();
				foreach ( wp_styles()->queue as $handle ) {
					wp_styles()->do_item( $handle );
				}
				$results['styles'] = ob_get_clean();

				ob_start();
				foreach ( wp_scripts()->queue as $handle ) {
					wp_scripts()->do_item( $handle );
				}
				$results['scripts'] = ob_get_clean();

				do_action( 'qm/stop', 'render_search_results' );
				$GLOBALS['vx_preview_card_level']--;
				$GLOBALS['vx_preview_card_current_ids'] = $previous_ids;
			}
		}
	}

	return $results;
}

function get_narrowed_filter_values( array $request, $term_taxonomy_ids ): array {
	global $wpdb;

	$response = [
		'terms' => [],
		'ranges' => [],
	];

	$post_type = \Voxel\Post_Type::get( sanitize_text_field( $request['type'] ?? '' ) );
	if ( ! $post_type ) {
		$response['terms'] = (object) $response['terms'];
		$response['ranges'] = (object) $response['ranges'];
		return $response;
	}

	$args = [];
	foreach ( $post_type->get_filters() as $filter ) {
		if ( isset( $request[ $filter->get_key() ] ) ) {
			$args[ $filter->get_key() ] = $request[ $filter->get_key() ];
		}
	}

	// handle conditional logic
	$parsed_values = [];
	foreach ( $post_type->get_filters() as $filter ) {
		if ( array_key_exists( $filter->get_key(), $request ) ) {
			$parsed_values[ $filter->get_key() ] = $filter->parse_value( $request[ $filter->get_key() ] );
		}
	}

	foreach ( $post_type->get_filters() as $filter ) {
		if ( ! $filter->passes_conditional_logic( $parsed_values ) ) {
			unset( $args[ $filter->get_key() ] );
		}
	}

	$_total_start = microtime( true );
	foreach ( $post_type->get_filters() as $filter ) {
		if ( $filter->get_type() === 'terms' && $filter->is_adaptive() && array_key_exists( $filter->get_key(), $request ) ) {
			if ( $taxonomy = $filter->get_taxonomy() ) {
				if ( ! ( $filter->get_prop('multiple') && $filter->get_prop('operator') === 'and' ) ) {
					if ( ( $_REQUEST['_last_modified'] ?? null ) === $filter->get_key() ) {
						continue;
					}
				}

				$posts_query = $post_type->index_query->get_adaptive_counts_for_terms_filter( $filter, $args );

				$all_ids = $term_taxonomy_ids[ $filter->get_key() ] ?? [];
				if ( ! is_array( $all_ids ) ) {
					continue;
				}

				$all_ids = array_filter( array_map( 'absint', $all_ids ) );
				if ( empty( $all_ids ) ) {
					continue;
				}

				$all_ids = array_slice( $all_ids, 0, 2500 );

				$count_posts = apply_filters( '_voxel/adaptive-terms/count-posts', true, $filter, $post_type );

				if ( $count_posts ) {
					$term_taxonomy_id__in = join( ',', $all_ids );

					$sql = sprintf( "
						WITH filtered_posts AS ( {$posts_query} ),
						hit_counter AS (
							SELECT '%s' AS taxonomy, tr.term_taxonomy_id AS term_id, COUNT(*) AS hits
								FROM {$wpdb->term_relationships} tr
							JOIN filtered_posts fp ON fp.post_id = tr.object_id
							WHERE tr.term_taxonomy_id IN ({$term_taxonomy_id__in})
							GROUP BY tr.term_taxonomy_id
						)
						SELECT taxonomy, JSON_OBJECTAGG(term_id, hits) AS terms
						FROM hit_counter
					", esc_sql( $taxonomy->get_key() ), esc_sql( $taxonomy->get_key() ) );

					// dd_sql($sql);

					$_query_start = microtime( true );
					$results = $wpdb->get_results( $sql, ARRAY_A );

					do_action( 'qm/info', sprintf( '(%s) Adaptive terms query time: %sms', $taxonomy->get_key(), round( ( microtime( true ) - $_query_start ) * 1000, 1 ) ) );
					// do_action( 'qm/info', trim( $sql ) );

					if ( is_array( $results ) ) {
						foreach ( $results as $result ) {
							$terms = json_decode( $result['terms'] ?? '', true );
							if ( is_array( $terms ) && ! empty( $terms ) ) {
								$response['terms'][ $result['taxonomy'] ] = $terms;
							}
						}
					}

					if ( ! isset( $response['terms'][ $taxonomy->get_key() ] ) ) {
						$response['terms'][ $taxonomy->get_key() ] = [];
					}
				} else {
					$id__in = 'SELECT '.join( ' UNION ALL SELECT ', $all_ids );

					$sql = "
						WITH ids(id) AS ( {$id__in} ),
						posts AS ( {$posts_query} )
						SELECT ids.id AS id, EXISTS (
							SELECT 1
							FROM {$wpdb->term_relationships} tr
							JOIN posts p ON p.post_id = tr.object_id
							WHERE tr.term_taxonomy_id = ids.id
							LIMIT 1
						) AS has_post
						FROM ids
						HAVING has_post = 1
					";

					$_query_start = microtime( true );
					$results = $wpdb->get_results( $sql, ARRAY_A );

					if ( is_array( $results ) ) {
						$response['terms'][ $taxonomy->get_key() ] = [];
						foreach ( $results as $result ) {
							$response['terms'][ $taxonomy->get_key() ][ $result['id'] ] = 1;
						}
					}

					if ( ! isset( $response['terms'][ $taxonomy->get_key() ] ) ) {
						$response['terms'][ $taxonomy->get_key() ] = [];
					}

					do_action( 'qm/info', sprintf( '(%s) ALT Adaptive terms query time: %sms', $taxonomy->get_key(), round( ( microtime( true ) - $_query_start ) * 1000, 1 ) ) );
					// do_action( 'qm/info', trim( $sql ) );
				}
			}
		}
	}
	do_action( 'qm/info', sprintf( 'Adaptive terms total: %sms', round( ( microtime( true ) - $_total_start ) * 1000, 1 ) ) );

	$_total_start = microtime( true );
	foreach ( $post_type->get_filters() as $filter ) {
		if ( $filter->get_type() === 'range' && $filter->is_adaptive() && array_key_exists( $filter->get_key(), $request ) ) {
			if ( ( $_REQUEST['_last_modified'] ?? null ) === $filter->get_key() ) {
				continue;
			}

			$sql = $post_type->index_query->get_adaptive_query_for_range_filter( $filter, $args );

			$_query_start = microtime( true );
			$results = $wpdb->get_row( $sql, ARRAY_A );

			$min = is_numeric( $results['min_value'] ?? null ) ? floatval( $results['min_value'] ) : null;
			$max = is_numeric( $results['max_value'] ?? null ) ? floatval( $results['max_value'] ) : null;

			$response['ranges'][ $filter->get_key() ] = [
				'min' => $min,
				'max' => $max,
			];

			do_action( 'qm/info', sprintf( '(%s) Adaptive range query time: %sms', $filter->get_key(), round( ( microtime( true ) - $_query_start ) * 1000, 1 ) ) );
		}
	}
	do_action( 'qm/info', sprintf( 'Adaptive ranges total: %sms', round( ( microtime( true ) - $_total_start ) * 1000, 1 ) ) );

	$response['terms'] = (object) $response['terms'];
	$response['ranges'] = (object) $response['ranges'];

	return $response;
}

function _post_get_marker_icon( $icon_string ) {
	static $svg_cache = [];

	if ( str_starts_with( (string) $icon_string, 'svg:' ) ) {
		if ( ! isset( $svg_cache[ $icon_string ] ) ) {
			$svg_markup = \Voxel\get_icon_markup( $icon_string );
			$svg_markup = str_replace( ' id=', ' _id=', $svg_markup );
			$svg_markup = str_replace( '<svg', sprintf( '<svg id="ts-svg-%s" ', $icon_string ), $svg_markup );
			$svg_cache[ $icon_string ] = $svg_markup;
		} else {
			$svg_markup = sprintf( '<svg viewBox="0 0 32 32"><use width="32" height="32" href="#ts-svg-%s"></use></svg>', $icon_string );
		}

		return $svg_markup;
	} else {
		return \Voxel\get_icon_markup( $icon_string );
	}
}

function _post_get_marker( $post ) {
	$custom_marker = apply_filters( '_voxel/post_utils/post_get_marker/custom', null, $post );
	if ( $custom_marker !== null ) {
		return $custom_marker;
	}

	$location_field = $post->get_field('location');
	$location = $location_field ? $location_field->get_value() : [];
	if ( ! ( is_numeric( $location['latitude'] ?? null ) && is_numeric( $location['longitude'] ?? null ) ) ) {
		return;
	}

	$marker_type = $post->post_type->config( 'settings.map.markers.type' );

	$common_attributes = [
		sprintf( 'data-post-id="%d"', $post->get_id() ),
		sprintf( 'data-post-link="%s"', esc_attr( $post->get_link() ) ),
		sprintf( 'data-position="%s,%s"', $location['latitude'], $location['longitude'] ),
	];

	$common_attributes = join( ' ', $common_attributes );

	$default_marker = sprintf(
		'<div class="map-marker marker-type-icon mi-static" %s><svg viewBox="0 0 32 32"><use width="32" height="32" href="#ts-symbol-marker"></use></svg></div>',
		$common_attributes
	);

	if ( $marker_type === 'text' ) {
		$text = esc_html( \Voxel\render( $post->post_type->config( 'settings.map.markers.type_text.text', '' ) ) );

		return <<<HTML
			<div class="map-marker marker-type-text" {$common_attributes}>{$text}</div>
		HTML;
	} elseif ( $marker_type === 'image' ) {
		$config = $post->post_type->config( 'settings.map.markers.type_image' );

		$term_icon = '';
		$taxonomy_field = $post->get_field( $config['icon_source'] );
		if ( $taxonomy_field && $taxonomy_field->get_type() === 'taxonomy' ) {
			$value = $taxonomy_field->get_value();
			if ( isset( $value[0] ) ) {
				$term_icon = $value[0]->get_icon();
				$term_color = $value[0]->get_color();

				if ( ! empty( $term_icon ) ) {
					$icon_markup = _post_get_marker_icon( $term_icon );

					if ( ! empty( $icon_markup ) ) {
						$marker_style = ! empty( $term_color ) ? sprintf( 'style="--ts-accent-1:%s"', esc_attr( $term_color ) ) : '';
						$term_icon = sprintf(
							'<div class="marker-cat" %s %s>%s</div>',
							$common_attributes,
							$marker_style,
							$icon_markup
						);
					}
				}
			}
		}

		$image_field = $post->get_field( $config['image_source'] );
		if ( $image_field && $image_field->get_type() === 'image' ) {
			$image_ids = $image_field->get_value();
			if ( empty( $image_ids ) ) {
				$image_ids = [ $image_field->get_default() ];
			}

			$image_id = array_shift( $image_ids );
			$image_url = esc_url( wp_get_attachment_image_url( $image_id, 'thumbnail' ) );
			if ( ! empty( $image_url ) ) {
				return sprintf(
					'<div class="map-marker marker-type-image" %s><img src="%s"></div>%s',
					$common_attributes,
					$image_url,
					$term_icon
				);
			}
		}

		if ( $config['default_image'] ) {
			$image_url = esc_url( wp_get_attachment_image_url( $config['default_image'], 'thumbnail' ) );
			if ( ! empty( $image_url ) ) {
				return sprintf(
					'<div class="map-marker marker-type-image" %s><img src="%s"></div>%s',
					$common_attributes,
					$image_url,
					$term_icon
				);
			}
		}

		return sprintf(
			'<div class="map-marker marker-type-icon mi-static" %s><svg viewBox="0 0 32 32"><use width="32" height="32" href="#ts-symbol-marker"></use></svg></div>%s',
			$common_attributes,
			$term_icon
		);
	} elseif ( $marker_type === 'icon' ) {
		$config = $post->post_type->config( 'settings.map.markers.type_icon' );
		$field = $post->get_field( $config['source'] );
		if ( $field && $field->get_type() === 'taxonomy' ) {
			$value = $field->get_value();
			if ( isset( $value[0] ) ) {
				$term_icon = $value[0]->get_icon();
				$term_color = $value[0]->get_color();

				if ( ! empty( $term_icon ) ) {
					$icon_markup = _post_get_marker_icon( $term_icon );

					if ( ! empty( $icon_markup ) ) {
						$marker_style = ! empty( $term_color ) ? sprintf( 'style="--ts-icon-bg:%s"', esc_attr( $term_color ) ) : '';
						return sprintf(
							'<div class="map-marker marker-type-icon mi-dynamic" %s %s>%s</div>',
							$common_attributes,
							$marker_style,
							$icon_markup
						);
					}
				}
			}
		}

		if ( ! empty( $config['default'] ) ) {
			$icon_markup = _post_get_marker_icon( $config['default'] );

			if ( ! empty( $icon_markup ) ) {
				return sprintf(
					'<div class="map-marker marker-type-icon mi-static" %s>%s</div>',
					$common_attributes,
					$icon_markup
				);
			}
		}

		return $default_marker;
	} else {
		return $default_marker;
	}
}

function cache_post_review_stats( $post_id ) {
	global $wpdb;

	$post = \Voxel\Post::get( $post_id );

	$stats = [
		'total' => 0,
		'average' => null,
		'by_score' => [],
		'by_category' => [],
		'latest' => null,
	];

	$results = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT AVG(review_score) AS average, COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'post_reviews' AND post_id = %d AND moderation = 1
	SQL, $post_id ) );

	if ( is_numeric( $results->average ) && is_numeric( $results->total ) && $results->total > 0 ) {
		$stats['total'] = absint( $results->total );
		$stats['average'] = \Voxel\clamp( $results->average, -2, 2 );

		$by_score = $wpdb->get_results( $wpdb->prepare( <<<SQL
			SELECT ROUND(review_score) AS score, COUNT(*) AS total
			FROM {$wpdb->prefix}voxel_timeline
			WHERE feed = 'post_reviews' AND post_id = %d AND moderation = 1
			GROUP BY ROUND(review_score)
		SQL, $post_id ) );

		foreach ( $by_score as $score ) {
			if ( is_numeric( $score->score ) && is_numeric( $score->total ) && $score->total > 0 ) {
				$stats['by_score'][ (int) $score->score ] = absint( $score->total );
			}
		}

		// get latest item
		$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
			SELECT id, created_at, user_id, published_as
			FROM {$wpdb->prefix}voxel_timeline
			WHERE feed = 'post_reviews' AND post_id = %d AND moderation = 1
			ORDER BY created_at DESC LIMIT 1
		SQL, $post_id ) );

		if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
			$stats['latest'] = [
				'id' => absint( $latest->id ),
				'user_id' => is_numeric( $latest->user_id ) ? absint( $latest->user_id ) : null,
				'published_as' => is_numeric( $latest->published_as ) ? absint( $latest->published_as ) : null,
				'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
			];
		}
	}

	if ( $post && $post->post_type ) {
		$averages_sql = [];
		foreach ( $post->post_type->reviews->get_categories() as $category ) {
			$averages_sql[] = sprintf(
				"AVG(JSON_EXTRACT(details, '$.rating.\"%s\"')) AS `%s`",
				esc_sql( $category['key'] ),
				esc_sql( $category['key'] )
			);
		}

		if ( ! empty( $averages_sql ) ) {
			$select = join( ', ', $averages_sql );
			$sql = $wpdb->prepare( <<<SQL
				SELECT {$select} FROM {$wpdb->prefix}voxel_timeline
				WHERE feed = 'post_reviews' AND `post_id` = %d AND moderation = 1
			SQL, $post->get_id() );
			$results = $wpdb->get_row( $sql, ARRAY_A );
			foreach ( $results as $category_key => $category_average ) {
				if ( is_numeric( $category_average ) ) {
					$stats['by_category'][ $category_key ] = round( $category_average, 3 );
				}
			}
		}
	}

	update_post_meta( $post_id, 'voxel:review_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/review-stats-updated', $post_id, $stats );
	return $stats;
}

function cache_post_timeline_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'total' => 0,
		'latest' => null,
	];

	// calculate total count
	$total = $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'post_timeline' AND post_id = %d AND moderation = 1
	SQL, $post_id ) );

	$stats['total'] = is_numeric( $total ) ? absint( $total ) : 0;

	// get latest item
	$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT id, created_at
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'post_timeline' AND post_id = %d AND moderation = 1
		ORDER BY created_at DESC LIMIT 1
	SQL, $post_id ) );

	if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
		$stats['latest'] = [
			'id' => absint( $latest->id ),
			'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
		];
	}

	update_post_meta( $post_id, 'voxel:timeline_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/timeline-stats-updated', $post_id, $stats );
	return $stats;
}

function cache_post_wall_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'total' => 0,
		'latest' => null,
	];

	// calculate total count
	$total = $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'post_wall' AND post_id = %d AND moderation = 1
	SQL, $post_id ) );

	$stats['total'] = is_numeric( $total ) ? absint( $total ) : 0;

	// get latest item
	$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT id, created_at, user_id, published_as
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'post_wall' AND post_id = %d AND moderation = 1
		ORDER BY created_at DESC LIMIT 1
	SQL, $post_id ) );

	if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
		$stats['latest'] = [
			'id' => absint( $latest->id ),
			'user_id' => is_numeric( $latest->user_id ) ? absint( $latest->user_id ) : null,
			'published_as' => is_numeric( $latest->published_as ) ? absint( $latest->published_as ) : null,
			'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
		];
	}

	update_post_meta( $post_id, 'voxel:wall_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/wall-stats-updated', $post_id, $stats );
	return $stats;
}

function cache_post_review_reply_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'total' => 0,
		'latest' => null,
	];

	$results = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline_replies r
		LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
		WHERE t.feed = 'post_reviews' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
	SQL, $post_id ) );

	if ( is_numeric( $results->total ) && $results->total > 0 ) {
		$stats['total'] = absint( $results->total );

		// get latest item
		$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
			SELECT r.id AS id, r.created_at AS created_at, r.user_id AS user_id, r.published_as AS published_as
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
			WHERE t.feed = 'post_reviews' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
			ORDER BY r.created_at DESC LIMIT 1
		SQL, $post_id ) );

		if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
			$stats['latest'] = [
				'id' => absint( $latest->id ),
				'user_id' => is_numeric( $latest->user_id ) ? absint( $latest->user_id ) : null,
				'published_as' => is_numeric( $latest->published_as ) ? absint( $latest->published_as ) : null,
				'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
			];
		}
	}

	update_post_meta( $post_id, 'voxel:review_reply_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/review-reply-stats-updated', $post_id, $stats );
	return $stats;
}

function cache_post_timeline_reply_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'total' => 0,
		'latest' => null,
	];

	$results = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline_replies r
		LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
		WHERE t.feed = 'post_timeline' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
	SQL, $post_id ) );

	if ( is_numeric( $results->total ) && $results->total > 0 ) {
		$stats['total'] = absint( $results->total );

		// get latest item
		$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
			SELECT r.id AS id, r.created_at AS created_at, r.user_id AS user_id, r.published_as AS published_as
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
			WHERE t.feed = 'post_timeline' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
			ORDER BY r.created_at DESC LIMIT 1
		SQL, $post_id ) );

		if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
			$stats['latest'] = [
				'id' => absint( $latest->id ),
				'user_id' => is_numeric( $latest->user_id ) ? absint( $latest->user_id ) : null,
				'published_as' => is_numeric( $latest->published_as ) ? absint( $latest->published_as ) : null,
				'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
			];
		}
	}

	update_post_meta( $post_id, 'voxel:timeline_reply_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/timeline-reply-stats-updated', $post_id, $stats );
	return $stats;
}


function cache_post_wall_reply_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'total' => 0,
		'latest' => null,
	];

	$results = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline_replies r
		LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
		WHERE t.feed = 'post_wall' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
	SQL, $post_id ) );

	if ( is_numeric( $results->total ) && $results->total > 0 ) {
		$stats['total'] = absint( $results->total );

		// get latest item
		$latest = $wpdb->get_row( $wpdb->prepare( <<<SQL
			SELECT r.id AS id, r.created_at AS created_at, r.user_id AS user_id, r.published_as AS published_as
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->prefix}voxel_timeline t ON r.status_id = t.id
			WHERE t.feed = 'post_wall' AND t.post_id = %d AND t.moderation = 1 AND r.moderation = 1
			ORDER BY r.created_at DESC LIMIT 1
		SQL, $post_id ) );

		if ( is_numeric( $latest->id ?? null ) && strtotime( $latest->created_at ) ) {
			$stats['latest'] = [
				'id' => absint( $latest->id ),
				'user_id' => is_numeric( $latest->user_id ) ? absint( $latest->user_id ) : null,
				'published_as' => is_numeric( $latest->published_as ) ? absint( $latest->published_as ) : null,
				'created_at' => date( 'Y-m-d H:i:s', strtotime( $latest->created_at ) ),
			];
		}
	}

	update_post_meta( $post_id, 'voxel:wall_reply_stats', wp_slash( wp_json_encode( $stats ) ) );
	do_action( 'voxel/post/wall-reply-stats-updated', $post_id, $stats );
	return $stats;
}

function get_single_post_template_id( \Voxel\Post_Type $post_type ) {
	$templates = $post_type->templates->get_custom_templates()['single_post'] ?? null;
	if ( empty( $templates ) ) {
		return $post_type->get_templates()['single'] ?? null;
	}

	foreach ( $templates as $template ) {
		if ( empty( $template['visibility_rules'] ) ) {
			continue;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $template['visibility_rules'] );
		if ( $rules_passed ) {
			return $template['id'];
		}
	}

	return $post_type->get_templates()['single'] ?? null;
}

function get_single_term_template_id() {
	$templates = \Voxel\get_custom_templates()['term_single'] ?? null;
	if ( empty( $templates ) ) {
		return null;
	}

	foreach ( $templates as $template ) {
		if ( empty( $template['visibility_rules'] ) ) {
			continue;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $template['visibility_rules'] );
		if ( $rules_passed ) {
			return $template['id'];
		}
	}

	return null;
}

/**
 * Determine possible post expiration dates based on expiration
 * rules configured for that post type.
 *
 * @since 1.2.6
 */
function resolve_expiration_rules( \Voxel\Post $post ) {
	$expiry_dates = [];
	foreach ( $post->post_type->repository->get_expiration_rules() as $rule ) {
		if ( $rule['type'] === 'fixed' ) {
			$post_date = $post->get_date();
			if ( $post_date === '0000-00-00 00:00:00' ) {
				$post_date = date( 'Y-m-d H:i:s', time() );
			}

			$timestamp = strtotime( $post_date ) + ( $rule['amount'] * DAY_IN_SECONDS );
			if ( $timestamp ) {
				$expiry_dates[] = $timestamp;
			}
		} elseif ( $rule['type'] === 'field' ) {
			$field = $post->get_field( $rule['field'] );
			if ( $field->get_type() === 'recurring-date' ) {
				$value = $field->get_value();
				$timestamp = null;
				foreach ( (array) $value as $date ) {
					$ts = strtotime( $date['until'] ?? $date['end'] );
					if ( $ts && ( $timestamp === null || $ts < $timestamp ) ) {
						$timestamp = $ts;
					}
				}

				if ( $timestamp ) {
					$expiry_dates[] = $timestamp;
				}
			} elseif ( $field->get_type() === 'date' ) {
				if ( $timestamp = strtotime( $field->get_value() ) ) {
					$expiry_dates[] = $timestamp;
				}
			}
		}
	}

	return $expiry_dates;
}

function get_previous_posts_link() {
	global $paged;

	if ( ! is_single() && $paged > 1 ) {
		return esc_url( get_previous_posts_page_link() );
	}
}

function get_next_posts_link() {
	global $paged, $wp_query;

	$max_page = $wp_query->max_num_pages;

	if ( ! $paged ) {
		$paged = 1;
	}

	$next_page = (int) $paged + 1;

	if ( ! is_single() && ( $next_page <= $max_page ) ) {
		return esc_url( get_next_posts_page_link( $max_page ) );
	}
}
