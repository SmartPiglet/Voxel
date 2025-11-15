<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Filter {
	use Traits\Model_Helpers;

	/**
	 * Post type object which this filter belongs to.
	 *
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * List of filter properties/configuration. Values below are available for
	 * all filter types, but there can be additional props for specific filter types.
	 *
	 * @since 1.0
	 */
	protected $props = [];

	/**
	 * Used to cache/memoize various method calls.
	 *
	 * @since 1.0
	 */
	protected $cache = [];

	protected $value;

	protected $elementor_config = [];

	protected $search_widget;

	protected $frontend_config;

	protected $resets_to = null;

	protected $conditions;

	protected $supported_conditions;

	public function __construct( $props = [] ) {
		$this->props = array_merge( [
			'type' => 'keywords',
			'key' => '',
			'label' => '',
			'description' => '',
			'icon' => '',
			'conditions_enabled' => false,
			'conditions_behavior' => 'show',
			'conditions' => [],
		], $this->props );

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}
	}

	abstract public function get_models(): array;

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		//
	}

	public function index( \Voxel\Post $post ): array {
		return [];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		//
	}

	public function frontend_props() {
		return [];
	}

	public function parse_value( $value ) {
		return null;
	}

	public function get_frontend_config() {
		if ( $this->frontend_config === null ) {
			$is_valid_value = $this->parse_value( $this->get_value() ) !== null;

			$this->frontend_config = [
				'id' => sprintf( '%s.%s', $this->post_type->get_key(), $this->get_key() ),
				'type' => $this->get_type(),
				'key' => $this->get_key(),
				'label' => $this->get_label(),
				'description' => $this->get_description(),
				'icon' => \Voxel\get_icon_markup( $this->get_icon() ),
				'value' => $is_valid_value ? $this->get_value() : null,
				'props' => $this->frontend_props(),
				'resets_to' => $this->parse_value( $this->resets_to ),
				'conditions' => $this->frontend_conditions_config(),
				'conditions_behavior' => $this->get_prop('conditions_behavior') === 'hide' ? 'hide' : 'show',
				'adaptive' => $this->is_adaptive(),
			];
		}

		return $this->frontend_config;
	}

	public function reset_frontend_config() {
		$this->frontend_config = null;
	}

	public function is_ui() {
		return false;
	}

	public function get_elementor_controls(): array {
		return [];
	}

	public function get_default_value_from_elementor( $controls ) {
		return $controls['value'] ?? null;
	}

	public function resets_to( $value ) {
		$this->resets_to = $value;
	}

	/* Getters */
	public function get_type() {
		return $this->props['type'];
	}

	public function get_key() {
		return $this->props['key'];
	}

	public function db_key() {
		return '_'.$this->get_key();
	}

	public function get_label() {
		return $this->props['label'];
	}

	public function get_description() {
		return $this->props['description'];
	}

	public function get_icon() {
		return $this->props['icon'];
	}

	public function get_prop( $prop ) {
		if ( ! isset( $this->props[ $prop ] ) ) {
			return null;
		}

		return $this->props[ $prop ];
	}

	public function get_props() {
		return $this->props;
	}

	public function get_supported_conditions() {
		if ( $this->is_ui() ) {
			return null;
		}

		$supported_conditions = [ 'common' ];

		if ( is_array( $this->supported_conditions ) ) {
			$supported_conditions = array_merge( $supported_conditions, $this->supported_conditions );
		}

		return $supported_conditions;
	}

	public function get_conditions() {
		if ( ! is_null( $this->conditions ) ) {
			return $this->conditions;
		}

		$condition_types = \Voxel\config('post_types.filter_condition_types');
		$this->conditions = [];
		foreach ( (array) $this->props['conditions'] as $condition_group ) {
			$group = [];
			foreach ( (array) $condition_group as $condition_data ) {
				if ( empty( $condition_data['source'] ) || empty( $condition_data['type'] ) ) {
					continue;
				}

				if ( ! isset( $condition_types[ $condition_data['type'] ] ) ) {
					continue;
				}

				$condition = new $condition_types[ $condition_data['type'] ]( $condition_data );
				$condition->set_filter( $this );
				$condition->set_post_type( $this->post_type );

				$group[] = $condition;
			}

			if ( ! empty( $group ) ) {
				$this->conditions[] = $group;
			}
		}

		return $this->conditions;
	}

	protected function frontend_conditions_config() {
		if ( ! $this->props['conditions_enabled'] ) {
			return null;
		}

		$all_conditions = $this->get_conditions();
		$config = [];

		foreach ( $this->get_conditions() as $condition_group ) {
			$group = [];

			foreach ( $condition_group as $condition ) {
				$group[] = array_merge( $condition->get_props(), [
					'source' => $condition->get_source(),
					'type' => $condition->get_type(),
					'_passes' => true,
				] );
			}

			if ( ! empty( $group ) ) {
				$config[] = $group;
			}
		}

		return $config;
	}

	public function passes_conditional_logic( array $values ): bool {
		if ( $this->get_prop('conditions_enabled') ) {
			$filters = $this->post_type->get_filters();
			$conditions = $this->get_conditions();
			if ( empty( $conditions ) ) {
				return true;
			}

			$behavior = $this->get_prop('conditions_behavior');
			$passes_conditions = false;

			foreach ( $conditions as $condition_group ) {
				if ( empty( $condition_group ) ) {
					continue;
				}

				$group_passes = true;
				foreach ( $condition_group as $condition ) {
					$subject_parts = explode( '.', $condition->get_source() );
					$subject_filter_key = $subject_parts[0];
					$subject_subfield_key = $subject_parts[1] ?? null;

					$subject_filter = $filters[ $subject_filter_key ] ?? null;
					if ( ! ( $subject_filter && array_key_exists( $subject_filter_key, $values ) ) ) {
						$group_passes = false;
					} else {
						$value = $values[ $subject_filter->get_key() ] ?? null;
						if ( $subject_subfield_key !== null ) {
							$value = $value[ $subject_subfield_key ] ?? null;
						}

						if ( $subject_filter->get_type() === 'order-by' && $value !== null ) {
							$value = $value['key'];
						}

						if ( $condition->evaluate( $value ) === false ) {
							$group_passes = false;
						}
					}
				}

				if ( $group_passes ) {
					$passes_conditions = true;
				}
			}

			if ( $behavior === 'hide' ) {
				return ! $passes_conditions;
			} else {
				return $passes_conditions;
			}
		} else {
			return true;
		}
	}

	public function is_adaptive(): bool {
		return false;
	}

	public function ssr( array $args ) {
		if ( $template = locate_template( sprintf( 'templates/widgets/search-form/ssr/%s-ssr.php', $this->get_type() ) ) ) {
			require $template;
			return;
		}

		$value = $this->parse_value( $this->get_value() );
		?>
		<div v-if="false" class="<?= $args['wrapper_class'] ?>">
			<?php if ( ! empty( $args['show_labels'] ) ): ?>
				<label><?= $this->get_label() ?></label>
			<?php endif ?>
			<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
				<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
				<div class="ts-filter-text"><?= $value ?? ( ! empty( $this->props['placeholder'] ) ? $this->props['placeholder'] : $this->get_label() ) ?></div>
			</div>
		</div>
	<?php }

	public function set_post_type( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function get_value() {
		return $this->value;
	}

	public function set_value( $value ) {
		$this->value = $value;
	}

	public function set_elementor_config( $controls ) {
		$this->elementor_config = $controls;
	}

	public function set_search_widget( $widget ) {
		$this->search_widget = $widget;
	}

	public function get_required_scripts(): array {
		return [];
	}
}
