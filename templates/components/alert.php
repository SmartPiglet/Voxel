<script type="text/html" id="vx-alert-tpl">
	<div class="ts-notice ts-notice-{type}">
		<div class="alert-msg">
			<div class="alert-ic">
				<?php \Voxel\svg( 'checkmark-circle.svg' ) ?>
				<?php \Voxel\svg( 'cross-circle.svg' ) ?>
				<?php \Voxel\svg( 'notification.svg' ) ?>
				<?php \Voxel\svg( 'info.svg' ) ?>
			</div>
			{message}
		</div>

		<div class="a-btn alert-actions">
			<a href="#" class="ts-btn ts-btn-4 close-alert"><?= _x( 'Close', 'close alert', 'voxel' ) ?></a>
		</div>
	</div>
</script>
