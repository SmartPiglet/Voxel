<?php

namespace Voxel\Product_Types\Order_Items;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Item_Regular extends Order_Item {
	use Traits\Addons_Trait;

	public function get_type(): string {
		return 'regular';
	}

	public function get_product_description() {
		$description = [];

		if ( $this->get_product_field_key() === 'voxel:promotion' ) {
			$description[] = _x( 'Promotion', 'promotion order description', 'voxel' );
		}

		if ( $this->has_summary_item( 'addons' ) ) {
			$addon_summary = $this->get_addon_summary();
			if ( ! empty( $addon_summary ) ) {
				$description[] = $addon_summary;
			}
		}

		$description = join( ', ', $description );

		return apply_filters( 'voxel/order_item/product_description', $description, $this );
	}

	public function get_order_page_details(): array {
		$details = parent::get_order_page_details();

		if ( $this->get_product_field_key() === 'voxel:promotion' ) {
			$details['promotion_package'] = $this->_get_promotion_package_details();
		}

		return $details;
	}

	public function reduce_stock() {
		$schema = Schema::Object( [
			'handled' => Schema::Bool()->default( false ),
			'reduced' => Schema::Bool()->default( false ),
			'quantity' => Schema::Int()->min(0),
		] );

		$schema->set_value( $this->get_details( 'stock' ) );
		$config = $schema->export();

		// stock already handled for this order item
		if ( $config['handled'] ) {
			return;
		}

		$field = $this->get_product_field();
		if ( ! ( $field && $field->get_product_field('stock') ) ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$reserved_quantity = $this->get_quantity();
		if ( $reserved_quantity === null ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$value = $field->get_value();

		if ( ! $value['stock']['enabled'] ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$value['stock']['quantity'] = max( 0, $value['stock']['quantity'] - $reserved_quantity );
		$field->_direct_update( $value );

		$post = \Voxel\Post::force_get( $this->post_id ); // refresh post cache
		$post->should_index() ? $post->index() : $post->unindex();

		$this->set_details( 'stock', [
			'handled' => true,
			'reduced' => true,
			'quantity' => $reserved_quantity,
		] );

		$this->save();
	}

	protected function _get_promotion_package_details(): ?array {
		$order = $this->get_order();
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			return null;
		}

		$details = [
			'status' => null, // pending|active|inactive|ended|canceled
			'start_date' => null,
			'end_date' => null,
			'canceled_at' => null,
			'assigned_to_post' => false,
			'post_link' => null,
			'stats_link' => null,
		];

		$schema = Schema::Object( [
			'key' => Schema::String(),
			'duration' => Schema::Object( [
				'type' => Schema::Enum( ['days'] ),
				'amount' => Schema::Int()->min(1),
			] ),
			'priority' => Schema::Int()->min(1)->default(2),
		] );

		$schema->set_value( $this->get_details( 'promotion_package' ) );
		$config = $schema->export();

		$post = $this->get_post();
		if ( $post ) {
			$details['post_link'] = $post->get_link();
			if ( $post->post_type && $post->post_type->is_tracking_enabled() && $post->is_editable_by_current_user() ) {
				if ( $stats_page = get_permalink( \Voxel\get('templates.post_stats') ) ) {
					$details['stats_link'] = add_query_arg( 'post_id', $post->get_id(), $stats_page );
				}
			}
		}

		if ( ! $this->get_details( 'promotion.start_date' ) ) {
			$details['status'] = 'pending';
		} else {
			$start_date = strtotime( (string) $this->get_details( 'promotion.start_date', '' ) );
			$end_date = strtotime( (string) $this->get_details( 'promotion.end_date', '' ) );
			$canceled_at = strtotime( (string) $this->get_details( 'promotion.canceled_at', '' ) );

			$details['start_date'] = $start_date ? \Voxel\date_format( $start_date ) : null;
			$details['end_date'] = $end_date ? \Voxel\date_format( $end_date ) : null;
			$details['canceled_at'] = $canceled_at ? \Voxel\date_format( $canceled_at ) : null;

			if ( $canceled_at ) {
				$details['status'] = 'canceled';
			} elseif ( $start_date && $end_date ) {
				if ( time() >= $start_date && time() <= $end_date ) {
					$details['status'] = 'active';
				} elseif ( time() > $end_date ) {
					$details['status'] = 'ended';
				} else {
					$details['status'] = 'pending';
				}
			} else {
				$details['status'] = 'inactive';
			}

			$postmeta = (array) json_decode( get_post_meta( $post->get_id(), 'voxel:promotion', true ), true );
			if ( ( $postmeta['order_id'] ?? null ) === $order->get_id() ) {
				$details['assigned_to_post'] = true;
			}

			return $details;
		}

		return $details;
	}
}
