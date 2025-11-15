<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-post-priority">
	<div class="priority-options">
		<div class="priority-option">
			<label>
				<input type="radio" name="vx_post_priority" value="low" <?php checked( $priority === -1 ) ?>>
				Low
			</label>
		</div>
		<div class="priority-option">
			<label>
				<input type="radio" name="vx_post_priority" value="medium" <?php checked( $priority === 0 ) ?>>
				Medium (Default)
			</label>
		</div>
		<div class="priority-option">
			<label>
				<input type="radio" name="vx_post_priority" value="high" <?php checked( $priority === 1 ) ?>>
				High
			</label>
		</div>
		<div class="priority-option">
			<label>
				<input type="radio" name="vx_post_priority" value="custom" <?php checked( $priority < -1 || $priority > 1 ) ?>>
				Custom
				<input type="number" name="vx_post_priority_custom" min="-128" max="127" value="<?= $priority ?>" class="priority-custom">
			</label>
		</div>
	</div>
</div>
