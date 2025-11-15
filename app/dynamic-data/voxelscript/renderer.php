<?php

namespace Voxel\Dynamic_Data\VoxelScript;

use Tokens\Plain_Text;
use Tokens\Dynamic_Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Renderer {

	protected
		$tokenizer,
		$groups;

	public function __construct( array $groups ) {
		$this->tokenizer = new \Voxel\Dynamic_Data\VoxelScript\Tokenizer;

		foreach ( $groups as $group_key => $group ) {
			if ( $group === null ) {
				$groups[ $group_key ] = \Voxel\Dynamic_Data\Group::Noop();
			}
		}

		$groups['tags'] = \Voxel\Dynamic_Data\Group::Noop();
		$groups['endtags'] = \Voxel\Dynamic_Data\Group::Noop();
		$groups['value'] = new \Voxel\Dynamic_Data\Data_Groups\Value_Data_Group;

		$this->groups = $groups;
	}

	public function render( string $content, array $options = [] ): string {
		$token_list = $this->tokenizer->tokenize( $content );

		$value = '';
		foreach ( $token_list->get_tokens() as $token ) {
			if ( $token instanceof Tokens\Dynamic_Tag ) {
				$token->set_renderer( $this );

				if ( isset( $options['parent'] ) ) {
					$token->set_parent( $options['parent'] );
				}

				$value .= $token->render();
			} elseif ( $token instanceof Tokens\Plain_Text ) {
				$value .= $token->render();
			} else {
				continue;
			}
		}

		return $value;
	}

	public function get_group( string $group_key ): ?\Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {
		return $this->groups[ $group_key ] ?? null;
	}
}
