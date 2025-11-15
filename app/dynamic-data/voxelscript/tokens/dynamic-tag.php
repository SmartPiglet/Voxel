<?php

namespace Voxel\Dynamic_Data\VoxelScript\Tokens;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Dynamic_Tag extends Token {

	protected
		$group_key,
		$property_path,
		$modifiers;

	protected $raw_props;

	protected $group, $property, $parent;

	public function __construct( string $group_key, array $property_path, array $modifiers ) {
		$this->group_key = $group_key;
		$this->property_path = $property_path;
		$this->modifiers = $modifiers;
	}

	public function render(): string {
		$this->group = $this->renderer->get_group( $this->group_key );
		if ( ! $this->group ) {
			return $this->_render_raw_tag();
		}

		if ( $this->group->get_type() === 'value' ) {
			$this->group->set_token( $this );
		}

		$this->property = $this->group->get_property( $this->property_path );
		$value = (string) ( $this->property ? $this->property->get_value() : '' );

		$last_condition = true;
		foreach ( $this->modifiers as $modifier_data ) {
			if ( $modifier = $this->group->get_modifier( $modifier_data['key'] ) ) {
				$modifier->set_renderer( $this->renderer );
				$modifier->set_tag( $this );
				$modifier->set_args( $modifier_data['args'] );
				$modifier->set_current_value( $value );

				if ( $modifier->get_type() === 'control-structure' ) {
					$last_condition = $modifier->passes( $last_condition, $value );
				}

				if ( ! $last_condition ) {
					continue;
				}

				$value = (string) $modifier->apply( $value );
			}
		}

		return (string) $value;
	}

	public function get_group(): ?\Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {
		return $this->group;
	}

	public function add_modifier( array $modifier ): void {
		$this->modifiers[] = $modifier;
	}

	public function _render_raw_tag(): string {
		$content = sprintf( '@%s(%s)', $this->group_key, join( '.', $this->property_path ) );
		if ( ! empty( $this->modifiers ) ) {
			foreach ( $this->modifiers as $modifier ) {
				$content .= sprintf( '.%s(%s)', $modifier['key'], join( ',', array_map( function( $arg ) {
					return $arg['content'];
				}, $modifier['args'] ) ) );
			}
		}

		return $content;
	}

	public function get_property() {
		return $this->property;
	}

	public function set_parent( $parent ) {
		$this->parent = $parent;
	}

	public function get_parent() {
		return $this->parent;
	}

	public function get_group_key() {
		return $this->group_key;
	}

	public function get_property_path() {
		return $this->property_path;
	}

	public function set_raw_props( string $raw_props ): void {
		$this->raw_props = $raw_props;
	}

	public function to_script(): string {
		$tag = sprintf( '@%s(%s)', $this->group_key, $this->raw_props );

		foreach ( $this->modifiers as $modifier ) {
			$tag .= sprintf( '.%s(%s)', $modifier['key'], $modifier['raw_args'] );
		}

		return $tag;
	}
}
