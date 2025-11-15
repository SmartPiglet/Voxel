<script type="text/html" id="search-form-post-types-filter">
	<form-group class="choose-cpt-filter" popup-key="cpt-dropdown" ref="formGroup" @blur="onBlur" @save="onSave">
		<template #trigger>
			<label v-if="$root.config.showLabels"><?= _x( 'Post type', 'post types filter', 'voxel' ) ?></label>
	 		<div class="ts-filter ts-popup-target ts-filled" @mousedown="$root.activePopup = 'cpt-dropdown'">
				<span v-html="$root.post_type.icon"></span>
	 			<div class="ts-filter-text">{{ $root.post_type.label }}</div>
	 			<div class="ts-down-icon"></div>
	 		</div>
	 	</template>
		<template #popup>
			<div class="ts-term-dropdown ts-md-group">
				<div class="ts-sticky-top uib b-bottom" v-if="Object.keys( $root.post_types ).length >= 15">
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
						<input v-model="search" type="text" placeholder="<?= esc_attr( _x( 'Search', 'post types filter', 'voxel' ) ) ?>" class="autofocus">
					</div>
				</div>
				<transition name="dropdown-popup" mode="out-in">
					<ul class="simplify-ul ts-term-dropdown-list min-scroll">
						<li v-for="post_type in postTypes">
							<a href="#" class="flexify" @click.prevent="selected = post_type.key; onSave();">
								<div class="ts-radio-container">
									<label class="container-radio">
										<input type="radio" :checked="selected === post_type.key" disabled hidden>
										<span class="checkmark"></span>
									</label>
								</div>
								<span>{{ post_type.label }}</span>
								<div class="ts-term-icon ts-pull-right">
									<span v-html="post_type.icon"></span>
								</div>
							</a>
						</li>
						<li v-if="!postTypes.length">
							<a href="#" class="flexify" @click.prevent>
								<p><?= _x( 'No post types found', 'post types filter', 'voxel' ) ?></p>
							</a>
						</li>
					</ul>
				</transition>
			</div>
		</template>
		<template #controller>
			<div class="ts-popup-controller hide-d hide-t">
				<ul class="flexify simplify-ul">
					<li></li>
					<li class="flexify">
						<a href="#" class="ts-btn ts-btn-2" @click.prevent="onSave">
							<?= __( 'Save', 'voxel' ) ?>
						</a>
					</li>
				</ul>
			</div>
		</template>
	</form-group>
</script>
