<?php
$display_value = function( $value ) {
	if ( $this->props['format_numeric'] ) {
		$value = number_format_i18n( $value );
	}

	return $this->props['format_prefix'].$value.$this->props['format_suffix'];
};

$value = $this->parse_value( $this->get_value() );
$config = $this->get_frontend_config();
$label = $value ? join( ' &mdash; ', array_map( $display_value, $value ) ) : null;
$is_adaptive = $this->is_adaptive() && ( $args['config']['general_config']['supports_adaptive_filters'] ?? false );
$narrowed_values = (array) ( $args['config']['post_type_config'][ $this->post_type->get_key() ]['narrowed_values']['ranges'] ?? [] );
$range_start = $config['props']['range_start'];
$range_end = $config['props']['range_end'];
$is_hidden = false;

if ( $is_adaptive ) {
	$min = $narrowed_values[ $this->get_key() ]['min'] ?? null;
	$max = $narrowed_values[ $this->get_key() ]['max'] ?? null;
	if ( $min === null || $max === null ) {
		$is_hidden = true;
	} else {
		$range_start = $min;
		$range_end = $max;

		if ( isset( $value[0] ) && $value[0] < $range_start ) {
			$value[0] = $range_start;
		}

		if ( isset( $value[1] ) && $value[1] > $range_end ) {
			$value[1] = $range_end;
		}
	}
}

$scale = $range_end - $range_start;
if ( floatval( $scale ) === 0.0 ) {
	$scale = 0.1;
}

if ( $is_hidden ) {
	return;
}

if ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'minmax' && $this->props['handles'] === 'double' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-minmax">
			<input value="<?= esc_attr( $value[0] ?? '' ) ?>" type="number" placeholder="<?= esc_attr( $range_start ) ?>" class="inline-input input-no-icon">
			<input value="<?= esc_attr( $value[1] ?? '' ) ?>" type="number" placeholder="<?= esc_attr( $range_end ) ?>" class="inline-input input-no-icon">
		</div>
	</div>
<?php elseif ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'inline' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> ts-inline-filter range-ssr">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>

		<?php if ( $this->props['handles'] === 'single' ): ?>
			<?php $default = $this->props['compare'] === 'outside_range' ? $range_start : $range_end ?>
			<?php $percent = $value
				? \Voxel\clamp( ( ( $value[0] - $range_start ) / $scale ) * 100, 0, 100 )
				: ( $this->props['compare'] === 'outside_range' ? 0 : 100 ) ?>
			<?php $translate = $this->props['compare'] === 'outside_range' ? $percent : 0 ?>
			<?php $css_scale = $this->props['compare'] === 'outside_range' ? 1 - ( $percent / 100 ) : $percent / 100 ?>
			<div class="range-slider-wrapper">
				<div class="range-value"><?= $value ? $label : $display_value( $default ) ?></div>
				<div class="range-slider noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr">
					<div class="noUi-base">
						<div class="noUi-connects">
							<div class="noUi-connect" style="transform: translate(<?= $translate ?>%, 0px) scale(<?= $css_scale ?>, 1);"></div>
						</div>
						<div class="noUi-origin" style="transform: translate(-<?= 1000 - ( $percent * 10 ) ?>%, 0px); z-index: 4;">
							<div class="noUi-handle noUi-handle-lower">
								<div class="noUi-touch-area"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php else: ?>
			<?php
			$default = $this->props['compare'] === 'outside_range'
				? [ $range_start, $range_start ]
				: [ $range_start, $range_end ];

			$percent_start = 0;
			$percent_end = $this->props['compare'] === 'outside_range' ? 0 : 100;
			if ( $value ) {
				$percent_start = \Voxel\clamp( ( ( $value[0] - $range_start ) / $scale ) * 100, 0, 100 );
				$percent_end = \Voxel\clamp( ( ( $value[1] - $range_start ) / $scale ) * 100, 0, 100 );
			} ?>
			<div class="range-slider-wrapper">
				<div class="range-value"><?= $value ? $label : join( ' &mdash; ', array_map( $display_value, $default ) ) ?></div>
				<div class="range-slider noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr">
					<div class="noUi-base">
						<div class="noUi-origin" style="transform: translate(-<?= 1000 - ( $percent_start * 10 ) ?>%, 0px); z-index: 4;">
							<div class="noUi-handle noUi-handle-lower">
								<div class="noUi-touch-area"></div>
							</div>
						</div>
						<div class="noUi-origin" style="transform: translate(-<?= 1000 - ( $percent_end * 10 ) ?>%, 0px); z-index: 4;">
							<div class="noUi-handle noUi-handle-lower">
								<div class="noUi-touch-area"></div>
							</div>
						</div>

						<?php if ( $this->props['compare'] === 'outside_range' ): ?>
							<div class="noUi-connects">
								<div class="noUi-connect" style="transform: translate(0%, 0px) scale(<?= $percent_start / 100 ?>, 1);"></div>
								<div class="noUi-connect" style="transform: translate(<?= $percent_end ?>%, 0px) scale(<?= 1 - ( $percent_end / 100 ) ?>, 1);"></div>
							</div>
						<?php else: ?>
							<div class="noUi-connects">
								<div class="noUi-connect" style="transform: translate(<?= $percent_start ?>%, 0px) scale(<?= ( $percent_end - $percent_start ) / 100 ?>, 1);"></div>
							</div>
						<?php endif ?>
					</div>
				</div>
			</div>
		<?php endif ?>
	</div>
<?php else: ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text"><?= $value ? $label : ( $this->props['placeholder'] ?: $this->props['label'] ) ?></div>
			<div class="ts-down-icon"></div>
		</div>
	</div>
<?php endif ?>