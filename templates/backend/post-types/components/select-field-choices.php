<script type="text/html" id="post-type-select-field-choices">
	<div class="field-container" ref="fields-container">
		<draggable
			v-model="field.choices"
			group="field-choices"
			handle=".field-head"
			item-key="key"
			@start="dragStart"
			@end="dragEnd"
		>
			<template #item="{element: choice, index: index}">
				<div class="single-field wide" :class="{ 'open': active === choice }">
					<div class="field-head" @click="active = ( active === choice ) ? null : choice">
						<p class="field-name">{{ choice.label || '(empty)' }}</p>
						<span class="field-type">{{ choice.value || '(empty)' }}</span>
						<div class="field-actions">
							<span class="field-action all-center">
								<a href="#" @click.stop.prevent="remove(choice)">
									<i class="lar la-trash-alt icon-sm"></i>
								</a>
							</span>
						</div>
					</div>
					<div v-if="active === choice" class="field-body">
						<div class="x-row wrap-row">
							<div class="ts-form-group x-col-6">
								<label>Label</label>
								<input type="text" v-model="choice.label">
							</div>
							<div class="ts-form-group x-col-6">
								<label>Value</label>
								<input type="text" v-model="choice.value">
							</div>
							<div class="ts-form-group x-col-12">
								<label>Icon</label>
								<icon-picker v-model="choice.icon"></icon-picker>
							</div>
						</div>
					</div>
				</div>
			</template>
		</draggable>

		<a href="#" @click.prevent="add" class="ts-button ts-outline">Add choice</a>
	</div>
</script>