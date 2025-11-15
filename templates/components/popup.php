<script type="text/html" id="voxel-popup-template">
	<div class="elementor vx-popup" :class="'elementor-'+$root.post_id">
		<div class="ts-popup-root elementor-element" :class="'elementor-element-'+$root.widget_id+'-wrap'" v-cloak>
			<div class="ts-form elementor-element" :class="'elementor-element-'+$root.widget_id" :style="styles" ref="popup">
				<div class="ts-field-popup-container">
					<div class="ts-field-popup triggers-blur" ref="popup-box">
						<div class="ts-popup-content-wrapper min-scroll">
							<slot></slot>
						</div>
						<slot name="controller">
							<div class="ts-popup-controller" :class="controllerClass" v-if="showSave || showClear">
								<ul class="flexify simplify-ul">
									<li class="flexify ts-popup-close">
										<a @click.prevent="$emit('blur')" href="#" class="ts-icon-btn" role="button">
											<?= \Voxel\svg( 'close.svg' ) ?>
										</a>
									</li>

									<li class="flexify hide-d" @click.prevent="$emit('clear')">
										<a v-if="showClear && showClearMobile" href="#" class="ts-icon-btn">
											<!-- {{ clearLabel || 'Clear' }} -->
											<?= \Voxel\svg( 'reload.svg' ) ?>
										</a>
									</li>
									<li class="flexify hide-m" @click.prevent="$emit('clear')">
										<a v-if="showClear" href="#" class="ts-btn ts-btn-1">
											{{ clearLabel || <?= wp_json_encode( _x( 'Clear', 'popup actions', 'voxel' ) ) ?> }}
										</a>
									</li>
									<slot name="custom-actions"></slot>
									<li class="flexify">
										<a v-if="showSave" href="#" class="ts-btn ts-btn-2" @click.prevent="$emit('save')">
											{{ saveLabel || <?= wp_json_encode( _x( 'Save', 'popup actions', 'voxel' ) ) ?> }}
											<div class="ts-loader-wrapper">
												<span class="ts-loader"></span>
											</div>
										</a>
									</li>

								</ul>
							</div>
							<div v-else-if="showClose" class="ts-popup-controller hide-d" :class="controllerClass">
								<ul class="flexify simplify-ul">
									<li class="flexify ts-popup-close">
										<a @click.prevent="$emit('blur')" href="#" class="ts-icon-btn" role="button">
											<?= \Voxel\svg( 'close.svg' ) ?>
										</a>
									</li>
								</ul>
							</div>
						</slot>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
