<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Modifier {

	protected
		$args,
		$renderer,
		$tag,
		$defined_args;

	const TYPE_DATE = 'date';
	const TYPE_ARRAY = 'array';
	const TYPE_NUMBER = 'number';
	const TYPE_STRING = 'string';

	public function get_type(): string {
		return 'modifier';
	}

	public function expects(): array {
		return [ static::TYPE_STRING ];
	}

	abstract public function get_key(): string;

	abstract public function get_label(): string;

	public function get_description(): string {
		return '';
	}

	abstract public function apply( string $value );

	public function set_args( array $args ) {
		$this->args = $args;
	}

	public function get_arg( int $index ): string {
		$arg = $this->args[ $index ] ?? null;
		if ( $arg === null ) {
			return '';
		}

		$content = $arg['content'];

		if ( $arg['dynamic'] ) {
			$content = $this->renderer->render( $content, [
				'parent' => $this,
			] );
		}

		return $content;
	}

	public function set_renderer( \Voxel\Dynamic_Data\VoxelScript\Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function set_tag( \Voxel\Dynamic_Data\VoxelScript\Tokens\Dynamic_Tag $tag ) {
		$this->tag = $tag;
	}

	protected function define_args(): void {
		//
	}

	final protected function define_arg( array $data ): void {
		if ( $this->defined_args === null ) {
			$this->defined_args = [];
		}

		$this->defined_args[] = $data;
	}

	final public function get_defined_args(): array {
		if ( $this->defined_args === null ) {
			$this->defined_args = [];
			$this->define_args();
		}

		return $this->defined_args;
	}

	public function get_editor_config() {
		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'type' => $this->get_type(),
			'expects' => $this->expects(),
			'arguments' => array_map( function( $argument ) {
				$argument['value'] = '';
				return $argument;
			}, $this->get_defined_args() ),
		];
	}

	protected $current_value = '';
	public function set_current_value( string $current_value ): void {
		$this->current_value = $current_value;
	}

	public function get_current_value(): string {
		return $this->current_value;
	}

	protected function _get_nearest_loopable_ancestor(): ?array {
		$property = $this->tag->get_property();
		if ( ! $property ) {
			return null;
		}

		$loopable = null;
		$parent = $property;
		$property_path = $this->tag->get_property_path();
		$last_path_index = array_key_last( $property_path );

		// backward compat (< 1.5) with property bracket notation
		if ( $last_path_index !== null && str_ends_with( $property_path[ $last_path_index ], '[]' ) ) {
			$property_path[ $last_path_index ] = mb_substr( $property_path[ $last_path_index ], 0, -2 );
		}

		$depth = count( $property_path );
		while ( ( $parent = $parent->get_parent() ) && $depth >= 0 ) {
			$depth--;

			if ( $parent->get_type() === 'object-list' ) {
				return [
					'property' => $parent,
					'subpath' => array_slice( $property_path, $depth ), // path from nearest loopable ancestor to the original tag property
				];
			}
		}

		return null;
	}
}
