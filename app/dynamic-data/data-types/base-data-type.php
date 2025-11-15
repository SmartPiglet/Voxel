<?php

namespace Voxel\Dynamic_Data\Data_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Data_Type {

	protected
		$label,
		$description,
		$render_cb,
		$hidden,
		$parent;

	abstract public function get_type(): string;

	public function __construct( string $label, string $description = '' ) {
		$this->label = $label;
		$this->description = $description;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function set_label( string $label ): self {
		$this->label = $label;
		return $this;
	}

	public function get_description(): string {
		return $this->description;
	}

	// visually hide a dtag in backend (useful for soft deprecation)
	public function hidden(): self {
		$this->hidden = true;
		return $this;
	}

	public function is_hidden(): bool {
		return !! $this->hidden;
	}

	public function render( callable $cb ): self {
		$this->render_cb = $cb;
		return $this;
	}

	public function get_value() {
		if ( $this->render_cb === null ) {
			return null;
		}

		return ($this->render_cb)();
	}

	public $_path = [];
	public function export( ?\Voxel\Dynamic_Data\Exporter $exporter ): array {
		return [
			'type' => $this->get_type(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'hidden' => $this->is_hidden(),
			'path' => $this->_path,
		];
	}

	public function set_parent( ?Base_Data_Type $parent ): void {
		$this->parent = $parent;
	}

	public function get_parent(): ?Base_Data_Type {
		return $this->parent;
	}
}
