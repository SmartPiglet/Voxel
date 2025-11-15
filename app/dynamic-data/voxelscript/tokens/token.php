<?php

namespace Voxel\Dynamic_Data\VoxelScript\Tokens;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Token {

	protected $renderer;

	abstract public function render(): string;

	public function set_renderer( \Voxel\Dynamic_Data\VoxelScript\Renderer $renderer ) {
		$this->renderer = $renderer;
	}
}
