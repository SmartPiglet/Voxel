<?php
/**
 * Edit term order.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div id="voxel-reorder-terms" data-terms="<?= esc_attr( wp_json_encode( $terms ) ) ?>" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="onSubmit">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2>Reorder</h2>
				<div>
					<input type="hidden" name="taxonomy" value="<?= esc_attr( $taxonomy->get_key() ) ?>">
					<input type="hidden" name="terms" ref="termsInput">
					<input type="hidden" name="action" value="voxel_save_term_order">
					<?php wp_nonce_field( 'voxel_save_term_order' ) ?>
					<button type="submit" class="ts-button ts-save-settings btn-shadow">
					<?php \Voxel\svg( 'floppy-disk.svg' ) ?>Save changes
					</button>
				</div>
			</div>
		</div>
		<div class="ts-spacer"></div>
		<div class="x-container ts-terms-order">
			<div class="ts-tab-content" v-cloak>
				<div v-if="terms.length" class="inner-tab x-row h-center">
					<div class="x-col-6">
						<div class="field-container" ref="list">
							<term-list
								:terms="terms"
								:level="1"
								group="toplevel"
							></term-list>
						</div>
					</div>
				</div>
				<div v-else>
					<p>No terms found for this taxonomy.</p>
				</div>
			</div>
		</div>
	</form>
</div>


<script type="text/html" id="voxel-reorder-term-list-component">
	<draggable
		v-model="items"
		:group="group"
		handle=".field-head"
		item-key="id"
		@start="onDragStart"
		@end="onDragEnd"
		class="ts-drag-animation"
	>
		<template #item="{element: term}">
			<div class="single-field wide" :class="'field-level-'+level" ref="field">
				<div class="field-head" :title="'term id: '+term.id" @click="toggleCollapse">
					<i class="las la-bars field-handle"></i>
					<p class="field-name">{{ term.label }}</p>
					<span class="field-type">{{ term.slug }}</span>
					<div class="field-actions" v-if="term.children.length">
						<span class="field-action all-center">
							<a @click.prevent href="#">
								<i class="las la-angle-double-down"></i>
							</a>
						</span>
					</div>
				</div>
				<div v-if="term.children.length" class="field-container nested" style="padding-bottom: 0;" ref="list">
					<term-list
						:terms="term.children"
						:group="'term_'+term.id"
						:level="level+1"
						:parent="term"
					></term-list>
				</div>
			</div>
		</template>
	</draggable>
</script>
