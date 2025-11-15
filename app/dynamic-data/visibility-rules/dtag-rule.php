<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class DTag_Rule extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'dtag';
	}

	public function get_label(): string {
		return _x( 'Dynamic tag', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'tag', [ 'type' => 'hidden', 'value' => '' ] );
		$this->define_arg( 'compare', [ 'type' => 'hidden' ] );
		$this->define_arg( 'arguments', [ 'type' => 'hidden', 'value' => [] ] );
	}

	public function evaluate(): bool {
		$tag = $this->get_arg('tag');
		if ( empty( $tag ) ) {
			return false;
		}

		$modifier_class = \Voxel\Dynamic_Data\Config::get_modifiers()[ $this->get_arg('compare') ] ?? null;
		if ( $modifier_class === null ) {
			return false;
		}

		$modifier = new $modifier_class;
		if ( $modifier->get_type() !== 'control-structure' ) {
			return false;
		}

		$arguments = is_array( $this->get_arg('arguments') ) ? join( ',', $this->get_arg('arguments') ) : '';
		$statement = sprintf( '@site().then(%s).%s(%s).then(yes).else(no)', $tag, $modifier->get_key(), $arguments );

		$result = \Voxel\render( $statement );

		return $result === 'yes';
	}
}
