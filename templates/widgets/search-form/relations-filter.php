<script type="text/html" id="search-form-relations-filter">
	<form-group :popup-key="filter.id" ref="formGroup" v-if="filter.value !== null" :wrapper-class="repeaterId">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">
					<!-- <span v-if="filter.props.post.logo" v-html="filter.props.post.logo"></span> -->
					<template v-if="filter.props.post.title">
						{{ filter.props.post.title }}
					</template>
					<template v-else>
						<?= _x( 'Unknown', 'relations filter', 'voxel' ) ?>
					</template>
				</div>
			</div>
		</template>
	</form-group>
</script>
