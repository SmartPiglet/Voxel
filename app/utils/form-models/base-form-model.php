<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Form_Model {

	protected $args = [];

	public function __construct( $args ) {
		$this->args = array_merge( [
			'label' => '',
			'description' => '',
			'infobox' => '',
			'infobox_class' => '',
			'infobox_element' => 'p',
			'sublabel' => '',
			'footnote' => '',
			'required' => false,
			'classes' => [],
			'v-model' => '',
			'v-if' => '',
			':class' => '',
		], $this->args );

		foreach ( $args as $key => $value ) {
			if ( array_key_exists( $key, $this->args ) ) {
				if ( is_array( $this->args[ $key ] ) ) {
					$this->args[ $key ] = $this->args[ $key ] + ((array) $value );
				} else {
					$this->args[ $key ] = $value;
				}
			}
		}

		$this->init();
	}

	abstract protected function template();

	public function set_prop( $prop, $value ) {
		$this->args[ $prop ] = $value;
	}

	public function get_prop( $prop ) {
		return $this->args[ $prop ] ?? null;
	}

	protected function init() {
		//
	}

	protected function get_wrapper_classes(): string {
		return sprintf(
			'ts-form-group %s',
			join( ' ', $this->args['classes'] )
		);
	}

	protected function attr( $key, $as = null ): string {
		$as = is_string( $as ) ? $as : $key;
		if ( ! empty( $this->args[ $key ] ) || in_array( $this->args[ $key ], [ 0, '0', 0.0 ], true ) ) {
			return sprintf( '%s="%s"', esc_attr( $as ), esc_attr( $this->args[ $key ] ) );
		}

		return '';
	}

	protected function attributes(): string {
		$attributes = [];
		foreach ( func_get_args() as $key ) {
			$attributes[] = $this->attr( $key );
		}
		return join( ' ', $attributes );
	}

	public function get_template() {
		ob_start(); ?>
			<div class="<?= esc_attr( $this->get_wrapper_classes() ) ?>" <?= $this->attr('v-if') ?> <?= $this->attr(':class') ?>>
				<?php if ( $this->args['label'] || $this->args['description'] ): ?>
					<?php if ( $infobox = $this->args['infobox']): ?>
						<span class="vx-info-box wide <?= esc_attr( $this->args['infobox_class'] ?? '' ) ?>" style="float: right;">
							<?php \Voxel\svg( 'info.svg' ) ?>
							<<?= $this->args['infobox_element'] ?>><?= $infobox ?></<?= $this->args['infobox_element'] ?>>
						</span>
					<?php endif ?>
					<label>
						<?= $this->args['label'] ?>
						<?php if ( $description = $this->args['description']): ?>
							<span title="<?= esc_attr( $description ) ?>">[?]</span>
						<?php endif ?>
					</label>
				<?php endif ?>
				<?php if ( $this->args['sublabel'] ): ?>
					<p class="mt15"><?= $this->args['sublabel'] ?></p>
				<?php endif ?>
				<?php $this->template() ?>
				<?php if ( $this->args['footnote'] ): ?>
					<p style="margin-top: 10px;"><?= $this->args['footnote'] ?></p>
				<?php endif ?>
			</div>
		<?php
		return ob_get_clean();
	}

	public static function render( $args ): void {
		$input = new static( $args );
		echo $input->get_template();
	}
}
