<?php $value = $this->parse_value( $this->get_value() ) ?>
<?php $presets = $this->get_chosen_presets() ?>

<?php if ( $this->props['input_mode'] === 'date-range' ): ?>
	<?php foreach ( $presets as $preset ): ?>
		<div v-if="false" class="<?= $args['wrapper_class'] ?>">
			<?php if ( ! empty( $args['show_labels'] ) ): ?>
				<label><?= $this->get_label() ?></label>
			<?php endif ?>
			<div class="ts-filter <?= $value === $preset['key'] ? 'ts-filled' : '' ?>">
			<!-- 	<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span> -->
				<div class="ts-filter-text">
					<span><?= $preset['label'] ?></span>
				</div>

			</div>
		</div>
	<?php endforeach ?>
<?php endif ?>

<div v-if="false" class="<?= $args['wrapper_class'] ?>">
	<?php if ( ! empty( $args['show_labels'] ) ): ?>
		<label><?= $this->get_label() ?></label>
	<?php endif ?>
	<?php if ( $this->props['input_mode'] === 'single-date' ): ?>
		<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text">
				<?= $value
					? \Voxel\date_format( strtotime( $value['start'] ) )
					: $this->props['l10n_pickdate'] ?>
			</div>
			<div class="ts-down-icon"></div>
		</div>
	<?php else: ?>
		
			
				<div class="ts-filter ts-popup-target <?= is_array( $value ) ? 'ts-filled' : '' ?>">
					<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
					<div class="ts-filter-text">
						<?= is_array( $value )
							? \Voxel\date_format( strtotime( $value['start'] ) ).' - '.\Voxel\date_format( strtotime( $value['end'] ) )
							: $this->props['l10n_pickdate'] ?>
					</div>
					<div class="ts-down-icon"></div>
				</div>

		
	<?php endif ?>
</div>
