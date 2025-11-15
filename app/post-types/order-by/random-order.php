<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Random_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'random',
		'seed' => 10800,
	];

	public function get_label(): string {
		return 'Random';
	}

	public function get_models(): array {
		return [
			'seed' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Randomize every',
				'classes' => 'x-col-12',
				'choices' => [
					0 => 'Always',
					1800 => '30 minutes',
					3600 => '1 hour',
					7200 => '2 hours',
					10800 => '3 hours',
					21600 => '6 hours',
					43200 => '12 hours',
					86400 => '24 hours',
				],
			],
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$seed = (int) $this->props['seed'];
		$query->orderby( sprintf(
			'RAND(%s)',
			$seed === 0 ? '' : ( floor( time() / $seed ) )
		) );
	}
}
