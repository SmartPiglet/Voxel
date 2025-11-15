<?php

namespace Voxel\Dynamic_Data\VoxelScript\Tokens;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plain_Text extends Token {

	protected $text;

	public function __construct( string $text ) {
		$this->text = $text;
	}

	public function render(): string {
		return $this->text;
	}

}
