<?php

namespace Voxel\Dynamic_Data;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Exporter {

	protected static $instance;
	public static function get(): self {
		if ( static::$instance === null ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	protected
		$exports = [],
		$groups = [],
		$subgroups = [];

	public function add_group( \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group $group ) {
		$this->groups[] = $group;
	}

	public function add_group_by_key( string $group_key, ...$mock_args ) {
		$list = \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group::get_all();
		if ( ! isset( $list[ $group_key ] ) ) {
			return null;
		}

		$group_class = $list[ $group_key ];
		$group = ( $group_class )::mock( ...$mock_args );

		$this->groups[] = $group;
	}

	public function add_subgroup( \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group $subgroup ) {
		$this->subgroups[] = $subgroup;
	}

	public function reset(): void {
		$this->exports = [];
		$this->groups = [];
		$this->subgroups = [];
	}

	public function export(): array {
		foreach ( $this->groups as $group ) {
			$this->export_group( $group );
		}

		$i = 0;
		do {
			$i++;
			$subgroups = $this->subgroups;
			foreach ( $subgroups as $subgroup ) {
				$this->export_group( $subgroup );
			}
		} while ( count( $subgroups ) !== count( $this->subgroups ) && $i <= 10 );

		return [
			'groups' => $this->exports,
			'modifiers' => $this->export_common_modifiers(),
			'visibility_rules' => $this->export_visibility_rules(),
		];
	}

	protected function export_group( \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group $group ) {
		if ( isset( $this->exports[ $group->get_export_key() ] ) ) {
			return;
		}

		$properties = $group->get_properties();
		$group_exports = [];
		foreach ( $properties as $property_key => $property ) {
			$property->_path[] = $property_key;
			$group_exports[ $property_key ] = $property->export( $this );
		}

		$methods = [];
		foreach ( $group->get_methods() as $method_key => $method_class ) {
			$method = new $method_class;
			$methods[ $method_key ] = $method->get_editor_config();
		}

		$this->exports[ $group->get_export_key() ] = [
			'type' => $group->get_type(),
			'export_key' => $group->get_export_key(),
			'aliases' => (object) $group->get_aliases(),
			'exports' => (object) $group_exports,
			'methods' => (object) $methods,
		];
	}

	protected function export_common_modifiers(): array {
		$exports = [];

		$list = \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group::get_common_modifiers();
		foreach ( $list as $modifier_key => $modifier_class ) {
			$modifier = new $modifier_class;
			$exports[ $modifier_key ] = $modifier->get_editor_config();
		}

		return $exports;
	}

	protected function export_visibility_rules(): array {
		$exports = [];

		$list = \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group::get_visibility_rules();
		foreach ( $list as $rule_key => $rule_class ) {
			$rule = new $rule_class;
			$exports[ $rule_key ] = $rule->get_editor_config();
		}

		return $exports;
	}

}
