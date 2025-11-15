<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Visibility_Rule {

	protected
		$args,
		$defined_args;

	abstract public function get_type(): string;

	abstract public function get_label(): string;

	abstract public function evaluate(): bool;

	public function set_args( array $args ) {
		$this->args = $args;
	}

	public function get_arg( $key ) {
		return $this->args[ $key ] ?? null;
	}

	protected function define_args(): void {
		//
	}

	final protected function define_arg( string $key, array $options ): void {
		if ( $this->defined_args === null ) {
			$this->defined_args = [];
		}

		$this->defined_args[ $key ] = $options;
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
			'type' => $this->get_type(),
			'label' => $this->get_label(),
			'arguments' => (object) array_map( function( $argument ) {
				if ( ! isset( $argument['value'] ) ) {
					$argument['value'] = null;
				}

				return $argument;
			}, $this->get_defined_args() ),
		];
	}
}
