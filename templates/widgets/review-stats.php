<?php
/**
 * Review stats (VX) widget template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
}
?>
<?php if ( $stat_mode === 'by_category' ): ?>
	<div class="ts-review-bars">
		<?php foreach ( $post->post_type->reviews->get_categories() as $category ): ?>
			<?php if ( isset( $stats['by_category'][ $category['key'] ] ) ):
				$_score = $stats['by_category'][ $category['key'] ];
				$score = round( $_score + 3, 1 );
				$level = null;
				foreach ( $rating_levels as $lvl ) {
					if ( $_score >= ( $lvl['score'] - 0.5 ) && $_score < ( $lvl['score'] + 0.5 ) ) {
						$level = $lvl;
						break;
					}
				} ?>
				<?php if ( $level ): ?>
					<div class="ts-percentage-bar" style="<?= ! empty( $level['color'] ) ? '--ts-accent-1: '.$level['color'] : '' ?>">
						<div class="ts-bar-data">
							<?= \Voxel\get_icon_markup( $category['icon'] ) ?>
							<p><?= $category['label'] ?><span><?= number_format_i18n( $score, 1 ) ?> / 5</span></p>
						</div>
						<div class="ts-bar-chart">
							<div style="width: <?= ( $score / 5 ) * 100 ?>%;"></div>
						</div>
					</div>
				<?php endif ?>
			<?php endif ?>
		<?php endforeach ?>
	</div>
<?php else: ?>
	<div class="ts-review-bars">
		<?php foreach ( array_reverse( $rating_levels ) as $level ): ?>
			<?php if ( isset( $pct[ $level['key'] ] ) ): ?>
				<div
					class="ts-percentage-bar <?= esc_attr( $level['key'] ) ?>"
					style="<?= ! empty( $level['color'] ) ? '--ts-accent-1: '.$level['color'] : '' ?>"
				>
					<div class="ts-bar-data">
						<p><?= $level['label'] ?><span><?= absint( $pct[ $level['key'] ] ) ?>%</span></p>
					</div>
					<div class="ts-bar-chart">
						<div style="width: <?= absint( $pct[ $level['key'] ] ) ?>%;"></div>
					</div>
				</div>
			<?php endif ?>
		<?php endforeach ?>
	</div>
<?php endif ?>
