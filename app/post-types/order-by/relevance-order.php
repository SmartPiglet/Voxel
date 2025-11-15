<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relevance_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'relevance',
		'source' => '',
	];

	public function get_label(): string {
		return 'Relevance';
	}

	public function get_models(): array {
		return [
			'source' => function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Keywords filter:</label>
					<select v-model="clause.source">
						<option v-for="filter in $root.getFiltersByType('keywords')" :value="filter.key">
							{{ filter.label }}
						</option>
					</select>
				</div>
			<?php }
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$filter = $this->post_type->get_filter( $this->props['source'] );
		if ( $filter && $filter->get_type() === 'keywords' ) {
			$filter->orderby_relevance( $query, $args );
		}
	}
}
