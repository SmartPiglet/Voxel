<div class="ts-work-hours <?= $this->get_settings('ts_wh_collapse') ?>">
	<div class="ts-hours-today flexify">
		<?php if ( ( $schedule[ $today ]['status'] ?? null ) === 'hours' ): ?>
			<?php if ( $is_open_now ): ?>
				<div class="flexify ts-open-status open">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_open_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
					<p><?= $this->get_settings('ts_wh_open_text') ?></p>
				</div>
				<?php else: ?>
				<div class="flexify ts-open-status closed">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_closed_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
					<p><?= $this->get_settings('ts_wh_closed_text') ?></p>
				</div>
			<?php endif ?>
		<?php elseif ( ( $schedule[ $today ]['status'] ?? null ) === 'open' ): ?>
			<div class="flexify ts-open-status open">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_open_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<p><?= $this->get_settings('ts_wh_open_text') ?></p>
			</div>
		<?php elseif ( ( $schedule[ $today ]['status'] ?? null ) === 'closed' ): ?>
			<div class="flexify ts-open-status closed">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_closed_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<p><?= $this->get_settings('ts_wh_closed_text') ?></p>
			</div>
		<?php elseif ( ( $schedule[ $today ]['status'] ?? null ) === 'appointments_only' ): ?>
			<div class="flexify ts-open-status appt-only">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_appt_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<p><?= $this->get_settings('ts_wh_appt_text') ?></p>
			</div>
		<?php else: ?>
			<div class="flexify ts-open-status not-available">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_wh_na_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<p><?= $this->get_settings('ts_wh_na_text') ?></p>
			</div>
		<?php endif ?>
		<!--
		<div class="flexify ts-open-status closing-soon">
			<div class="wh-icon-con flexify">
			<?php \Voxel\render_icon( $this->get_settings('ts_wh_closing_icon') ); ?>
			</div>
			<p><?= $this->get_settings('ts_wh_closing_text') ?></p>
		</div>
		<div class="flexify ts-open-status opening-soon">
			<div class="wh-icon-con flexify">
			<?php \Voxel\render_icon( $this->get_settings('ts_wh_opening_icon') ); ?>
			</div>
			<p><?= $this->get_settings('ts_wh_opening_text') ?></p>
		</div>
		 -->
		<p class="ts-current-period">
			<!-- <?= $weekdays[ $today ] ?>: -->
			<?php if ( ! isset( $schedule[ $today ] ) ): ?>
				<span><?= _x( 'Not available', 'work hours', 'voxel' ) ?></span>
			<?php elseif ( $schedule[ $today ]['status'] === 'open' ): ?>
				<span><?= _x( 'Open all day', 'work hours', 'voxel' ) ?></span>
			<?php elseif ( $schedule[ $today ]['status'] === 'closed' ): ?>
				<span><?= _x( 'Closed all day', 'work hours', 'voxel' ) ?></span>
			<?php elseif ( $schedule[ $today ]['status'] === 'appointments_only' ): ?>
				<span><?= _x( 'Appointments only', 'work hours', 'voxel' ) ?></span>
			<?php else: ?>
				<?php $last_index = count( $schedule[ $today ]['hours'] ) - 1 ?>
				<?php foreach ( $schedule[ $today ]['hours'] as $i => $hours ): ?>
					<span><?= sprintf(
						'%s - %s%s',
						\Voxel\time_format( strtotime( $hours['from'] ) ),
						\Voxel\time_format( strtotime( $hours['to'] ) ),
						$last_index === $i ? '' : ', '
					) ?></span>
				<?php endforeach ?>
			<?php endif ?>
		</p>
		<a href="#" class="ts-expand-hours ts-icon-btn ts-smaller">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
		</a>
	</div>
	<div class="ts-work-hours-list">
		<ul class="simplify-ul flexify">
			<?php foreach ( $weekdays as $key => $label ): ?>
				<li>
					<p class="ts-day"><?= $label ?></p>
					<small class="ts-hours">
						<?php if ( ! isset( $schedule[ $key ] ) ): ?>
							<span><?= _x( 'Not available', 'work hours', 'voxel' ) ?></span>
						<?php elseif ( $schedule[ $key ]['status'] === 'open' ): ?>
							<span><?= _x( 'Open all day', 'work hours', 'voxel' ) ?></span>
						<?php elseif ( $schedule[ $key ]['status'] === 'closed' ): ?>
							<span><?= _x( 'Closed all day', 'work hours', 'voxel' ) ?></span>
						<?php elseif ( $schedule[ $key ]['status'] === 'appointments_only' ): ?>
							<span><?= _x( 'Appointments only', 'work hours', 'voxel' ) ?></span>
						<?php else: ?>
							<?php foreach ( $schedule[ $key ]['hours'] as $hours ): ?>
								<span><?= sprintf(
									'%s - %s',
									\Voxel\time_format( strtotime( $hours['from'] ) ),
									\Voxel\time_format( strtotime( $hours['to'] ) ) )
								?></span>
							<?php endforeach ?>
						<?php endif ?>
					</small>
				</li>
			<?php endforeach ?>
		   <li>
				<p class="ts-timezone"><?= _x( 'Local time', 'work hours', 'voxel' ) ?> </p>
				<small>
					<span><?= \Voxel\replace_vars( _x( '@current_time', 'work hours', 'voxel' ), [
					'@current_time' => \Voxel\datetime_format( $local_time ),
				] ) ?></span>

				</small>
		   </li>
		</ul>
	</div>
</div>
