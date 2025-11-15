<div class="ts-countdown-widget flexify" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<?php if ( $diff->invert ): ?>
		<ul class="countdown-timer flexify simplify-ul">
			<li><span class="timer-days"><?= $config['days'] ?></span><p><?= _x( 'Days', 'countdown widget', 'voxel' ) ?></p></li>
			<li><span class="timer-hours"><?= $config['hours'] ?></span><p><?= _x( 'Hours', 'countdown widget', 'voxel' ) ?></p></li>
			<li><span class="timer-minutes"><?= $config['minutes'] ?></span><p><?= _x( 'Minutes', 'countdown widget', 'voxel' ) ?></p></li>
			<li><span class="timer-seconds"><?= $config['seconds'] ?></span><p><?= _x( 'Seconds', 'countdown widget', 'voxel' ) ?></p></li>
		</ul>
	<?php endif ?>
	<div class="countdown-ended" style="<?= $diff->invert ? 'display:none;' : '' ?>"><?= $countdown_ended_text ?></div>
</div>
