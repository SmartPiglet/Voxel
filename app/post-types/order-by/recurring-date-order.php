<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Recurring_Date_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'recurring-date',
		'source' => '',
		'order' => 'ASC',
	];

	public function get_label(): string {
		return 'Recurring date';
	}

	public function get_models(): array {
		return [
			'source' => function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Recurring date filter:</label>
					<select v-model="clause.source">
						<option v-for="filter in $root.getFiltersByType('recurring-date')" :value="filter.key">
							{{ filter.label }}
						</option>
					</select>
				</div>
			<?php },
			'order' => $this->get_order_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$filter = $this->post_type->get_filter( $this->props['source'] );
		if ( $filter && $filter->get_type() === 'recurring-date' ) {
			$value = $filter->parse_value( $args[ $filter->get_key() ] ?? null );
			if ( $value === null ) {
				// if recurring date filter has no value set, default to the "all" preset
				$args[ $filter->get_key() ] = 'all';
				$filter->query( $query, $args );
			}

			$query->orderby( sprintf(
				'`current_start` %s',
				$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
			) );
		}
	}
}
