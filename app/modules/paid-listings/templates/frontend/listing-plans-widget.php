<?php

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<ul class="ts-plan-tabs simplify-ul flexify ts-generic-tabs">
	<?php foreach ( $groups as $group ): ?>
		<li class="<?= $group['_id'] === $default_group ? 'ts-tab-active' : '' ?>">
			<a href="#" data-id="<?= esc_attr( $group['_id'] ) ?>"><?= $group['group_label'] ?></a>
		</li>
	<?php endforeach ?>
</ul>
<div class="ts-plans-list ts-paid-listings-plans">
	<?php foreach ( $plans as $plan ): ?>
		<div class="ts-plan-container <?= $plan['group'] !== $default_group ? 'hidden' : '' ?> <?= ! empty( $plan['featured'] ) ? 'plan-featured' : '' ?>" data-group="<?= esc_attr( $plan['group'] ) ?>">
			<?php if ( ! empty( $plan['featured'] ) && ! empty( $plan['featured_text'] ) ): ?>
				<span class="ts-plan-featured-text"><?= esc_html( $plan['featured_text'] ) ?></span>
			<?php endif ?>
			
			<div class="ts-plan-body">
				<div class="ts-plan-details">
					<span class="ts-plan-name"><?= $plan['label'] ?></span>
				</div>
				<div class="ts-plan-pricing">
					<?php if ( $plan['billing']['is_free'] ): ?>
						<span class="ts-plan-price"><?= _x( 'Free', 'listing plans', 'voxel' ) ?></span>
					<?php else: ?>
						<?php if ( $plan['billing']['discount_amount'] ): ?>
							<span class="ts-plan-price"><?= $plan['billing']['discount_amount'] ?></span>
							<span class="ts-plan-price"><s><?= $plan['billing']['amount'] ?></s></span>
						<?php else: ?>
							<span class="ts-plan-price"><?= $plan['billing']['amount'] ?></span>
						<?php endif ?>
						<?php if ( $plan['billing']['period'] ): ?>
							<div class="ts-price-period">/ <?= $plan['billing']['period'] ?></div>
						<?php endif ?>
					<?php endif ?>
				</div>
				<?php if ( ! empty( $plan['image'] ) ): ?>
					<div class="ts-plan-image flexify">
						<?= $plan['image'] ?>
					</div>
				<?php endif ?>
				<?php if ( ! empty( $plan['description'] ) ): ?>
					<div class="ts-plan-desc">
						<p><?= nl2br( $plan['description'] ) ?></p>
					</div>
				<?php endif ?>
				<?php if ( ! empty( $plan['features'] ) ): ?>
					<div class="ts-plan-features">
						<ul class="simplify-ul">
							<?php foreach ( $plan['features'] as $feature ): ?>
								<li>
									<?= \Voxel\get_icon_markup( $feature['feature_ico'] ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ) ?>

									<span><?= $feature['text'] ?></span>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				<?php endif ?>
				<div class="ts-plan-footer">
					<?php if ( is_user_logged_in() ): ?>
						<?php if ( $process === 'switch' && Module\get_assigned_package( $post )['details']['plan'] === $plan['key'] ): ?>
							<a href="javascript:void(0)" class="ts-btn ts-btn-1 ts-btn-large vx-disabled" rel="nofollow">
								<?= _x( 'Current plan', 'listing plans', 'voxel' ) ?>
							</a>
						<?php else: ?>
							<?php if ( isset( $packages_by_plan[ $plan['key'] ] ) ): ?>
								<a href="<?= esc_url( add_query_arg( 'package_id', $packages_by_plan[ $plan['key'] ]['package_id'], $plan['link'] ) ) ?>" class="ts-btn ts-btn-1 ts-btn-large vx-pick-plan use-available-plan" rel="nofollow">
									<?= _x( 'Use available plan', 'listing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								</a>
								<div class="vx-dialog-content min-scroll"><?= \Voxel\replace_vars( _x( 'You have used @used out of @total allowed submissions', 'listing plans', 'voxel' ), [
									'@total' => $packages_by_plan[ $plan['key'] ]['total'],
									'@used' => $packages_by_plan[ $plan['key'] ]['used'],
								] ) ?></div>
							<?php else: ?>
								<a href="<?= esc_url( $plan['link'] ) ?>" class="ts-btn ts-btn-2 ts-btn-large vx-pick-plan" rel="nofollow">
									<?= $plan['billing']['is_free'] ? _x( 'Pick plan', 'listing plans', 'voxel' ) : _x( 'Buy plan', 'listing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								</a>
							<?php endif ?>
						<?php endif ?>
					<?php else: ?>
						<a href="<?= esc_url( $plan['link'] ) ?>" class="ts-btn ts-btn-2 ts-btn-large vx-pick-plan" rel="nofollow">
							<?= $plan['billing']['is_free'] ? _x( 'Pick plan', 'listing plans', 'voxel' ) : _x( 'Buy plan', 'listing plans', 'voxel' ) ?>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
						</a>
					<?php endif ?>
				</div>
			</div>
		</div>
	<?php endforeach ?>
</div>
