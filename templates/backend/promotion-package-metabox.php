<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-post-promotion">
	<div class="promotion-wrapper">
		<div class="promotion-title">
			<?php if ( $order_id !== null ): ?>
				<strong title="<?= esc_html( $package_title ?? '' ) ?>">
					<a href="<?= esc_url( $order_link ) ?>" style="text-decoration: none;">
						Order #<?= $order_id ?>
					</a>
				</strong>
			<?php else: ?>
				<strong title="<?= esc_html( $package_title ?? '' ) ?>">Details</strong>
			<?php endif ?>
		</div>
		<div class="promotion-details">
			<?php if ( $start_date && $end_date ): ?>
				<div class="mb10">
					<label style="display: block;">Duration</label>
					<strong><?= $start_date ?> - <?= $end_date ?></strong>
				</div>
			<?php elseif ( $end_date ): ?>
				<div class="mb10">
					<label style="display: block;">Expires on</label>
					<strong> <?= $end_date ?></strong>
				</div>
			<?php endif ?>
			<div>
				<label style="display: block;">Priority</label>
				<strong><?= $priority ?></strong>
			</div>
		</div>
	</div>
</div>
