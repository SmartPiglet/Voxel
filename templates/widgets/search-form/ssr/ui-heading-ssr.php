<div v-if="false" class="<?= $args['wrapper_class'] ?> ui-heading">
	<label>
		<?= $this->get_label() ?>
		<?php if ( ! empty( $this->get_description() ) ): ?>
			<small><?= esc_html( $this->get_description() ) ?></small>
		<?php endif ?>
	</label>
</div>