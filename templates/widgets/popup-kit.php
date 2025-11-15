<!-- The markup on this page is entirely static for preview purposes -->
<div class="popup-kit-holder">
   <style type="text/css">
      .popup-kit-holder {
      padding: 30px;
      width: 450px;
      margin: auto;
      display: flex;
      flex-direction: column;
      }
      @media (max-width:1024px) {
	      .popup-kit-holder, .popup-kit-holder1 {
	      display: none !important;
	      }
      }

      .popup-kit-holder .ts-popup-content-wrapper,  .popup-kit-holder1 .ts-popup-content-wrapper {
          max-height: none;
      }
   </style>
   <details>
     <summary>What's the purpose of this widget?</summary>
     <br>
     <p>This widget is used to apply global styles to Voxel popups. <br><br> It should be added in <code>WP-admin > Design > General > Style kits > Popup styles.</code><br><br> This is a static representation of each popup component. Click on the widget and browse styling options in the widget area. <br><br>Once saving changes, your settings are applied to all popups sitewide.</p>
   </details>
   <br>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-head flexify ts-sticky-top">
               <div class="ts-popup-name flexify">
                  <?= \Voxel\get_svg( 'notification.svg' ) ?>
                  <span>Popup head</span>
               </div>
               <ul class="flexify simplify-ul">
                  <li class="flexify">
                     <a href="#" class="ts-icon-btn">
                        <?= \Voxel\get_svg( 'trash-can.svg' ) ?>
                     </a>
                  </li>
               </ul>
            </div>
            <div class="ts-popup-content-wrapper min-scroll">
               <div class="ts-form-group" style="padding-bottom: 0;">
                  <label>Label <small>Some description</small>
                  </label>
               </div>
            </div>
            <div class="ts-popup-controller">
               <ul class="flexify simplify-ul">
                  <li class="flexify">
                     <a href="#" class="ts-btn ts-btn-1">Clear</a>
                  </li>
                  <li class="flexify">
                     <a href="#" class="ts-btn ts-btn-2">Save</a>
                  </li>
               </ul>
            </div>
         </div>
      </div>
   </div>



   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-content-wrapper min-scroll">
               <div class="ts-form-group">
                  <label>Switcher </label>
                  <div class="switch-slider">
                     <div class="onoffswitch">
                        <input type="checkbox" class="onoffswitch-checkbox" tabindex="0">
                        <label class="onoffswitch-label"></label>
                     </div>
                  </div>
               </div>
               <div class="ts-form-group">

                  <div class="switch-slider">
                     <div class="onoffswitch">
                        <input type="checkbox" checked="checked" class="onoffswitch-checkbox" tabindex="0">
                        <label class="onoffswitch-label"></label>
                     </div>
                  </div>
               </div>
               <div class="ts-form-group">
                  <label>
                     Stepper
                     <!--v-if-->
                  </label>
                  <div class="ts-stepper-input flexify">
                     <button class="ts-stepper-left ts-icon-btn">
                         <?= \Voxel\get_svg( 'minus.svg' ) ?>
                     </button>
                     <input type="number" class="ts-input-box" min="0" max="1000" step="1" placeholder="0">
                     <button class="ts-stepper-right ts-icon-btn">
                        <?= \Voxel\get_svg( 'plus.svg' ) ?>
                     </button>
                  </div>
               </div>
               <div class="ts-form-group">
                  <label>
                     Range
                     <!--v-if-->
                  </label>
                  <div class="range-slider-wrapper">
                     <div class="range-value">276 — 774</div>
                     <div class="range-slider noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr">
                        <div class="noUi-base">
                           <div class="noUi-connects">
                              <div class="noUi-connect" style="transform: translate(27.6%, 0px) scale(0.498, 1);"></div>
                           </div>
                           <div class="noUi-origin" style="transform: translate(-724%, 0px); z-index: 5;">
                              <div class="noUi-handle noUi-handle-lower" data-handle="0" tabindex="0" role="slider" aria-orientation="horizontal" aria-valuemin="0.0" aria-valuemax="774.0" aria-valuenow="276.0" aria-valuetext="276.00">
                                 <div class="noUi-touch-area"></div>
                              </div>
                           </div>
                           <div class="noUi-origin" style="transform: translate(-226%, 0px); z-index: 4;">
                              <div class="noUi-handle noUi-handle-upper" data-handle="1" tabindex="0" role="slider" aria-orientation="horizontal" aria-valuemin="276.0" aria-valuemax="1000.0" aria-valuenow="774.0" aria-valuetext="774.00">
                                 <div class="noUi-touch-area"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-head flexify ts-sticky-top">
               <div class="ts-popup-name flexify">
                  <?= \Voxel\get_svg( 'notification.svg' ) ?>
                  <span>No results</span>
               </div>
            </div>
            <div class="ts-popup-content-wrapper min-scroll">
               <div class="ts-empty-user-tab">
                   <?= \Voxel\get_svg( 'notification.svg' ) ?>
                  <p>No notifications received.</p>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup triggers-blur">
         <div class="ts-popup-content-wrapper min-scroll" style="max-height: none;">
            <div class="ts-popup-head flexify ts-sticky-top">
               <div class="ts-popup-name flexify">
                   <?= \Voxel\get_svg( 'notification.svg' ) ?>
                  <span>Notifications</span>
               </div>

            </div>
            <ul class="ts-notification-list simplify-ul">
               <li class="ts-unread-notification ts-new-notification">
                  <a href="#">
                     <div class="notification-image">
	                       <?= \Voxel\get_svg( 'notification.svg' ) ?>
                     </div>
                     <div class="notification-details">
                        <b>Unseen and unvisited notification</b>
                        <span>9 hours ago</span>
                     </div>
                  </a>
               </li>
               <li class="ts-unread-notification">
                  <a href="#">
                     <div class="notification-image">
                         <?= \Voxel\get_svg( 'notification.svg' ) ?>
                     </div>
                     <div class="notification-details">
                        <b>Unvisited notification</b>
                        <span>9 hours ago</span>
                     </div>
                  </a>
               </li>
               <li class="">
                  <a href="#">
                     <div class="notification-image">
                         <?= \Voxel\get_svg( 'notification.svg' ) ?>
                     </div>
                     <div class="notification-details">
                        <b>Seen and visited notification</b>
                        <span>9 hours ago</span>
                     </div>
                  </a>
               </li>
               <li class="ts-unread-notification ts-new-notification">
                  <a href="#">
                     <div class="notification-image">
                        <img src="
                           <?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg">
                     </div>
                     <div class="notification-details">
                        <b>Unseen and unvisited with image</b>
                        <span>9 hours ago</span>
                     </div>
                  </a>
               </li>
               <li class="ts-unread-notification">
                  <a href="#">
                     <div class="notification-image">
                        <img src="
                           <?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg">
                     </div>
                     <div class="notification-details">
                        <b>Unvisited with image</b>
                        <span>15 hours ago</span>
                     </div>
                  </a>
               </li>
               <li class="">
                  <a href="#">
                     <div class="notification-image">
                        <img src="
                           <?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg">
                     </div>
                     <div class="notification-details">
                        <b>Seen and visited with image</b>
                        <span>15 hours ago</span>
                     </div>
                  </a>
               </li>
               <li><a href="http://three-stays.test/hello-world/"><div class="notification-image"><img width="150" height="150" src="<?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg" class="ts-status-avatar" alt="" decoding="async" loading="lazy"></div><div class="notification-details"><b>Notification prompt with actions</b><!----></div></a><div class="ts-notification-actions"><a href="#" class="ts-btn ts-btn-1">Approve</a><a href="#" class="ts-btn ts-btn-1">Decline</a></div></li>
            </ul>
            <div class="ts-form-group">
               <div class="n-load-more">
                  <a href="#" class="ts-btn ts-btn-4">
                      <?= \Voxel\get_svg( 'reload.svg' ) ?>
                     Load more
                  </a>
               </div>
            </div>
         </div>
         <!---->
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-content-wrapper min-scroll">
               <div class="ts-sticky-top uib b-bottom">
                  <div class="ts-input-icon flexify">
                      <?= \Voxel\get_svg( 'search.svg' ) ?>
                     <input type="text" placeholder="Search" class="autofocus" maxlength="100">
                  </div>
               </div>
               <!--v-if-->
               <div class="ts-term-dropdown ts-multilevel-dropdown ts-md-group">
                  <ul class="simplify-ul ts-term-dropdown-list">
                     <li class="ts-selected">
                        <a href="#" class="flexify">
                           <div class="ts-checkbox-container">
                              <label class="container-checkbox">
                              <input type="checkbox" disabled="" hidden="" value="attractions" checked="checked">
                              <span class="checkmark"></span>
                              </label>
                           </div>
                           <span>Attractions</span>
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                        </a>
                     </li>
                     <li class="">
                        <a href="#" class="flexify">
                           <div class="ts-checkbox-container">
                              <label class="container-checkbox">
                              <input type="checkbox" disabled="" hidden="" value="bars">
                              <span class="checkmark"></span>
                              </label>
                           </div>
                           <span>Bars</span>
                           <div class="ts-right-icon"></div>
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                        </a>
                     </li>
                     <li class="">
                        <a href="#" class="flexify">
                           <div class="ts-checkbox-container">
                              <label class="container-checkbox">
                              <input type="checkbox" disabled="" hidden="" value="cinema">
                              <span class="checkmark"></span>
                              </label>
                           </div>
                           <span>Cinema</span>
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-term-dropdown ts-multilevel-dropdown ts-md-group">
               <!--v-if-->
               <ul class="simplify-ul ts-term-dropdown-list">
                  <li class="ts-term-centered">
                  	<a href="#" class="flexify">
                  	   <div class="ts-left-icon"></div>
                  	  	<span>Go back</span>
                  	</a>
                  </li>
                  <li class="ts-parent-item">
                     <a href="#" class="flexify">
                        <div class="ts-checkbox-container">
                           <label class="container-radio">
                           <input type="radio" disabled="" hidden="" value="bars">
                           <span class="checkmark"></span>
                           </label>
                        </div>
                        <span>All in Bars</span>
                        <div class="ts-term-icon">
                          <?= \Voxel\get_svg( 'file.svg' ) ?>
                        </div>
                     </a>
                  </li>
                  <li class="">
                     <a href="#" class="flexify">
                        <div class="ts-checkbox-container">
                           <label class="container-radio">
                           <input type="radio" disabled="" hidden="" value="nightlife">
                           <span class="checkmark"></span>
                           </label>
                        </div>
                        <span>Nightlife</span>
                        <!--v-if-->
                        <div class="ts-term-icon">
                           <?= \Voxel\get_svg( 'file.svg' ) ?>
                        </div>
                     </a>
                  </li>
                  <!--v-if-->
               </ul>
            </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-content-wrapper min-scroll">
               <div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
                  <ul class="simplify-ul ts-term-dropdown-list sub-menu">
                     <li id="menu-item-28" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-28">
                        <a href="#" class="flexify">
                           <div class="ts-term-icon">
                              <!--?xml version="1.0" encoding="UTF-8"?-->
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                           <span>Places</span>
                           <div class="ts-right-icon"></div>
                        </a>
                     </li>
                     <li id="menu-item-29" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-29">
                        <a href="#" class="flexify">
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                           <span>Events</span>
                           <div class="ts-right-icon"></div>
                        </a>
                     </li>
                     <li id="menu-item-2588" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2588">
                        <a href="#" class="flexify">
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                           <span>Jobs</span>
                           <div class="ts-right-icon"></div>
                        </a>
                     </li>
                     <li id="menu-item-2589" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2589">
                        <a href="#" class="flexify">
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                           <span>Groups</span>
                           <div class="ts-right-icon"></div>
                        </a>
                     </li>
                     <li id="menu-item-6070" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-6070">
                        <a href="http://192.168.178.55/city/collections/" class="flexify">
                           <div class="ts-term-icon">
                               <?= \Voxel\get_svg( 'file.svg' ) ?>
                           </div>
                           <span>Collections</span>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
           	<div class="ts-popup-content-wrapper min-scroll">
           	        <div class="ts-popup-head flexify ts-sticky-top">
           	          <div class="ts-popup-name flexify">
           	            <svg xmlns="http://www.w3.org/2000/svg" fill="#1C2033" width="52" height="52" viewBox="0 0 24 24">
           	              <path d="M4 2.00001L20 2C21.1046 2 22 2.89543 22 4V17C22 19.7614 19.7614 22 17 22H7C4.23858 22 2 19.7614 2 17V4.00001C2 2.89544 2.89543 2.00001 4 2.00001ZM8.75 7.00001V5.00001C8.75 4.58579 8.41421 4.25001 8 4.25001C7.58579 4.25001 7.25 4.58579 7.25 5.00001V7.00001C7.25 9.62336 9.37665 11.75 12 11.75C14.6234 11.75 16.75 9.62336 16.75 7.00001V5.00001C16.75 4.58579 16.4142 4.25001 16 4.25001C15.5858 4.25001 15.25 4.58579 15.25 5.00001V7.00001C15.25 8.79493 13.7949 10.25 12 10.25C10.2051 10.25 8.75 8.79493 8.75 7.00001Z"></path>
           	            </svg>
           	            <span>Cart</span>
           	          </div>
           	          <ul class="flexify simplify-ul">
           	            <li class="flexify">
           	              <a href="#" class="ts-icon-btn" role="button">
           	                <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	                  <path d="M12 1.25C9.92893 1.25 8.25 2.92893 8.25 5H4.5C3.67157 5 3 5.67157 3 6.5C3 7.32843 3.67157 8 4.5 8H19.5C20.3284 8 21 7.32843 21 6.5C21 5.67157 20.3284 5 19.5 5H15.75C15.75 2.92893 14.0711 1.25 12 1.25ZM12 2.75C13.2426 2.75 14.25 3.75736 14.25 5H9.75C9.75 3.75736 10.7574 2.75 12 2.75Z"></path>
           	                  <path d="M5 20V9.5H19V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20ZM10 16.25C9.58579 16.25 9.25 16.5858 9.25 17C9.25 17.4142 9.58579 17.75 10 17.75H14C14.4142 17.75 14.75 17.4142 14.75 17C14.75 16.5858 14.4142 16.25 14 16.25H10ZM7.75 13.5C7.75 13.9142 8.08579 14.25 8.5 14.25H15.5C15.9142 14.25 16.25 13.9142 16.25 13.5C16.25 13.0858 15.9142 12.75 15.5 12.75H8.5C8.08579 12.75 7.75 13.0858 7.75 13.5Z"></path>
           	                </svg>
           	              </a>
           	            </li>
           	            <li class="flexify ts-popup-close">
           	              <a href="#" class="ts-icon-btn" role="button">
           	                <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	                  <path d="M1.46967 1.46967C1.76256 1.17678 2.23744 1.17678 2.53033 1.46967L12 10.9393L21.4697 1.46967C21.7626 1.17678 22.2374 1.17678 22.5303 1.46967C22.8232 1.76256 22.8232 2.23744 22.5303 2.53033L13.0607 12L22.5303 21.4697C22.8232 21.7626 22.8232 22.2374 22.5303 22.5303C22.2374 22.8232 21.7626 22.8232 21.4697 22.5303L12 13.0607L2.53033 22.5303C2.23744 22.8232 1.76256 22.8232 1.46967 22.5303C1.17678 22.2374 1.17678 21.7626 1.46967 21.4697L10.9393 12L1.46967 2.53033C1.17678 2.23744 1.17678 1.76256 1.46967 1.46967Z"></path>
           	                </svg>
           	              </a>
           	            </li>
           	          </ul>
           	        </div>
           	        <div class="ts-form-group">
           	          <ul class="ts-cart-list simplify-ul">
           	            <li class="">
           	              <div class="cart-image">
           	                <img width="150" height="150" src="<?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg');" class="ts-status-avatar" alt="" decoding="async">
           	              </div>
           	              <div class="cart-item-details">
           	                <a href="http://ecommerce.test/products/grizl-8-1by/">Grizl 8 1by</a>
           	                <span>Color: Hazy IPA, Size: M</span>
           	                <span>€1,949.00</span>
           	              </div>
           	              <div class="cart-stepper">
           	                <a href="#" class="ts-icon-btn ts-smaller">
           	                  <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	                    <path d="M1.25 12C1.25 11.5858 1.58579 11.25 2 11.25H22C22.4142 11.25 22.75 11.5858 22.75 12C22.75 12.4142 22.4142 12.75 22 12.75H2C1.58579 12.75 1.25 12.4142 1.25 12Z"></path>
           	                  </svg>
           	                </a>
           	                <span>1</span>
           	                <a href="#" class="ts-icon-btn ts-smaller">
           	                  <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	                    <path d="M12 1.25C12.4142 1.25 12.75 1.58579 12.75 2V11.25H22C22.4142 11.25 22.75 11.5858 22.75 12C22.75 12.4142 22.4142 12.75 22 12.75H12.75V22C12.75 22.4142 12.4142 22.75 12 22.75C11.5858 22.75 11.25 22.4142 11.25 22V12.75H2C1.58579 12.75 1.25 12.4142 1.25 12C1.25 11.5858 1.58579 11.25 2 11.25H11.25V2C11.25 1.58579 11.5858 1.25 12 1.25Z"></path>
           	                  </svg>
           	                </a>
           	              </div>
           	            </li>
           	            <li class="">
           	              <div class="cart-image">
           	                <img width="150" height="150" src="<?php echo get_template_directory_uri(); ?>/assets/images/bg.jpg');" class="ts-status-avatar" >
           	              </div>
           	              <div class="cart-item-details">
           	                <a href="http://ecommerce.test/products/line-icon-pack/">Line icon pack</a>
           	                <!--v-if-->
           	                <span>€99.00</span>
           	              </div>
           	              <div class="cart-stepper">
           	                <a href="#" class="ts-icon-btn ts-smaller">
           	                  <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	                    <path d="M12 1.25C9.92893 1.25 8.25 2.92893 8.25 5H4.5C3.67157 5 3 5.67157 3 6.5C3 7.32843 3.67157 8 4.5 8H19.5C20.3284 8 21 7.32843 21 6.5C21 5.67157 20.3284 5 19.5 5H15.75C15.75 2.92893 14.0711 1.25 12 1.25ZM12 2.75C13.2426 2.75 14.25 3.75736 14.25 5H9.75C9.75 3.75736 10.7574 2.75 12 2.75Z"></path>
           	                    <path d="M5 20V9.5H19V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20ZM10 16.25C9.58579 16.25 9.25 16.5858 9.25 17C9.25 17.4142 9.58579 17.75 10 17.75H14C14.4142 17.75 14.75 17.4142 14.75 17C14.75 16.5858 14.4142 16.25 14 16.25H10ZM7.75 13.5C7.75 13.9142 8.08579 14.25 8.5 14.25H15.5C15.9142 14.25 16.25 13.9142 16.25 13.5C16.25 13.0858 15.9142 12.75 15.5 12.75H8.5C8.08579 12.75 7.75 13.0858 7.75 13.5Z"></path>
           	                  </svg>
           	                </a>
           	              </div>
           	            </li>
           	          </ul>
           	        </div>
           	      </div>
           	      <div class="ts-cart-controller">
           	        <div class="cart-subtotal">
           	          <span>Subtotal</span>
           	          <span>€2,048.00</span>
           	        </div>
           	        <a href="http://ecommerce.test/cart-summary/" class="ts-btn ts-btn-2"> Continue <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
           	            <path d="M14.5303 3.46969C14.3158 3.25519 13.9932 3.19103 13.713 3.30711C13.4327 3.4232 13.25 3.69668 13.25 4.00002V11.25H2C1.58579 11.25 1.25 11.5858 1.25 12C1.25 12.4142 1.58579 12.75 2 12.75H13.25V20C13.25 20.3034 13.4327 20.5768 13.713 20.6929C13.9932 20.809 14.3158 20.7449 14.5303 20.5304L22.5303 12.5304C22.8232 12.2375 22.8232 11.7626 22.5303 11.4697L14.5303 3.46969Z"></path>
           	          </svg>
           	        </a>
           	      </div>
         </div>
      </div>
   </div>

   <div class="ts-form elementor-element elementor-element-14b73b99">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
	           <div class="ts-popup-content-wrapper min-scroll">
  <div class="ts-form-group datepicker-head">
    <h3>
      <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.75 3C17.75 2.58579 17.4142 2.25 17 2.25C16.5858 2.25 16.25 2.58579 16.25 3V4H7.75V3C7.75 2.58579 7.41421 2.25 7 2.25C6.58579 2.25 6.25 2.58579 6.25 3V4H4C2.89543 4 2 4.89543 2 6V7.25H22V6C22 4.89543 21.1046 4 20 4H17.75V3Z"></path>
        <path d="M22 8.75H2V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V8.75Z"></path>
      </svg> Select date
    </h3>
    <p>Select a date to view available timeslots</p>
  </div>
  <div class="ts-booking-date ts-booking-date-single ts-form-group">
    <input type="hidden">
    <div class="pika-single">
      <div class="pika-lendar">
        <div id="pika-title-sd" class="pika-title" role="heading" aria-live="assertive">
          <div class="pika-label">March <select class="pika-select pika-select-month" tabindex="-1">
              <option value="0" disabled="disabled">January</option>
              <option value="1" disabled="disabled">February</option>
              <option value="2" selected="selected">March</option>
              <option value="3">April</option>
              <option value="4" disabled="disabled">May</option>
              <option value="5" disabled="disabled">June</option>
              <option value="6" disabled="disabled">July</option>
              <option value="7" disabled="disabled">August</option>
              <option value="8" disabled="disabled">September</option>
              <option value="9" disabled="disabled">October</option>
              <option value="10" disabled="disabled">November</option>
              <option value="11" disabled="disabled">December</option>
            </select>
          </div>
          <div class="pika-label">2024 <select class="pika-select pika-select-year" tabindex="-1">
              <option value="2024" selected="selected">2024</option>
            </select>
          </div>
          <button class="pika-prev ts-icon-btn is-disabled" type="button">
            <svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-arrow-left" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M56,29.8H13.3l17-17.3c0.9-0.9,0.9-2.3,0-3.2c-0.9-0.9-2.3-0.9-3.2,0l-20.7,21c-0.9,0.9-0.9,2.3,0,3.2l20.7,21
	c0.4,0.4,1,0.7,1.6,0.7c0.6,0,1.1-0.2,1.6-0.6c0.9-0.9,0.9-2.3,0-3.2L13.4,34.3H56c1.2,0,2.2-1,2.2-2.2C58.2,30.8,57.2,29.8,56,29.8
	z"></path>
            </svg>
          </button>
          <button class="pika-next ts-icon-btn" type="button">
            <svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-arrow-right" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M57.6,30.4l-20.7-21c-0.9-0.9-2.3-0.9-3.2,0c-0.9,0.9-0.9,2.3,0,3.2l16.8,17.1H8c-1.2,0-2.2,1-2.2,2.2s1,2.3,2.2,2.3h42.7
	l-17,17.3c-0.9,0.9-0.9,2.3,0,3.2c0.4,0.4,1,0.6,1.6,0.6c0.6,0,1.2-0.2,1.6-0.7l20.7-21C58.5,32.7,58.5,31.3,57.6,30.4z"></path>
            </svg>
          </button>
        </div>
        <table cellpadding="0" cellspacing="0" class="pika-table" role="grid" aria-labelledby="pika-title-sd">
          <thead>
            <tr>
              <th scope="col">
                <abbr title="Monday">Mon</abbr>
              </th>
              <th scope="col">
                <abbr title="Tuesday">Tue</abbr>
              </th>
              <th scope="col">
                <abbr title="Wednesday">Wed</abbr>
              </th>
              <th scope="col">
                <abbr title="Thursday">Thu</abbr>
              </th>
              <th scope="col">
                <abbr title="Friday">Fri</abbr>
              </th>
              <th scope="col">
                <abbr title="Saturday">Sat</abbr>
              </th>
              <th scope="col">
                <abbr title="Sunday">Sun</abbr>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="pika-row">
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td data-day="1" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="1">1</button>
              </td>
              <td data-day="2" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="2">2</button>
              </td>
              <td data-day="3" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="3">3</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="4" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="4">4</button>
              </td>
              <td data-day="5" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="5">5</button>
              </td>
              <td data-day="6" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="6">6</button>
              </td>
              <td data-day="7" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="7">7</button>
              </td>
              <td data-day="8" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="8">8</button>
              </td>
              <td data-day="9" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="9">9</button>
              </td>
              <td data-day="10" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="10">10</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="11" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="11">11</button>
              </td>
              <td data-day="12" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="12">12</button>
              </td>
              <td data-day="13" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="13">13</button>
              </td>
              <td data-day="14" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="14">14</button>
              </td>
              <td data-day="15" class="is-today" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="15">15</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="16" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="16">16</button>
              </td>
              <td data-day="17" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="17">17</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="18" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="18">18</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="19" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="19">19</button>
              </td>
              <td data-day="20" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="20">20</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="21" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="21">21</button>
              </td>
              <td data-day="22" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="22">22</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="23" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="23">23</button>
              </td>
              <td data-day="24" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="24">24</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="25" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="25">25</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="26" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="26">26</button>
              </td>
              <td data-day="27" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="27">27</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="28" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="28">28</button>
              </td>
              <td data-day="29" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="29">29</button>

                <div class="pika-tooltip">3+ available</div>
              </td>
              <td data-day="30" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="30">30</button>
              </td>
              <td data-day="31" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="2" data-pika-day="31">31</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
		<div class="ts-popup-content-wrapper min-scroll">
  <div class="ts-form-group datepicker-head">
    <h3>
      <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22ZM12.75 6V11.6893L16.0303 14.9697C16.3232 15.2626 16.3232 15.7374 16.0303 16.0303C15.7374 16.3232 15.2626 16.3232 14.9697 16.0303L11.4697 12.5303C11.329 12.3897 11.25 12.1989 11.25 12V6C11.25 5.58579 11.5858 5.25 12 5.25C12.4142 5.25 12.75 5.58579 12.75 6Z"></path>
      </svg> Choose slot
    </h3>
    <p>Pick a slot for Mar 15, 2024</p>
    <a href="#" class="ts-icon-btn">
      <svg fill="#1C2033" width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.75 3C17.75 2.58579 17.4142 2.25 17 2.25C16.5858 2.25 16.25 2.58579 16.25 3V4H7.75V3C7.75 2.58579 7.41421 2.25 7 2.25C6.58579 2.25 6.25 2.58579 6.25 3V4H4C2.89543 4 2 4.89543 2 6V7.25H22V6C22 4.89543 21.1046 4 20 4H17.75V3Z"></path>
        <path d="M22 8.75H2V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V8.75Z"></path>
      </svg>
    </a>
  </div>
  <div class="ts-booking-slot ts-form-group">
  	<div class="simplify-ul flexify ts-slot-list"><a class="ts-btn ts-btn-1 ts-filled" href="#">9:00 AM - 9:30 AM</a><a class="ts-btn ts-btn-1" href="#">4:00 PM - 4:30 PM</a><a class="ts-btn ts-btn-1" href="#">4:30 PM - 5:00 PM</a></div>
  </div>
</div>
         </div>
      </div>
   </div>

</div>
</div>
<div class="popup-kit-holder1">
   <style type="text/css">
      .popup-kit-holder1 {
      padding: 30px;
      width: 700px;
      margin: auto;
      display: flex;
      flex-direction: column;
      }

   </style>

   <div class="ts-form elementor-element elementor-element-14b73b99" style="grid-column-end: span 2;">
      <div class="ts-field-popup-container">
         <div class="ts-field-popup triggers-blur">
            <div class="ts-popup-content-wrapper min-scroll">
  <div class="ts-form-group datepicker-head">
    <h3>
      <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
      </svg> 10 nights
    </h3>
    <p>Apr 1, 2024 - Apr 11, 2024</p>
  </div>
  <div class="ts-booking-date ts-booking-date-range ts-form-group">
    <input type="hidden">
    <div class="pika-single">
      <div class="pika-lendar">
        <div id="pika-title-ga" class="pika-title" role="heading" aria-live="assertive">
          <div class="pika-label">April <select class="pika-select pika-select-month" tabindex="-1">
              <option value="0" disabled="disabled">January</option>
              <option value="1" disabled="disabled">February</option>
              <option value="2">March</option>
              <option value="3" selected="selected">April</option>
              <option value="4" disabled="disabled">May</option>
              <option value="5" disabled="disabled">June</option>
              <option value="6" disabled="disabled">July</option>
              <option value="7" disabled="disabled">August</option>
              <option value="8" disabled="disabled">September</option>
              <option value="9" disabled="disabled">October</option>
              <option value="10" disabled="disabled">November</option>
              <option value="11" disabled="disabled">December</option>
            </select>
          </div>
          <div class="pika-label">2024 <select class="pika-select pika-select-year" tabindex="-1">
              <option value="2024" selected="selected">2024</option>
            </select>
          </div>
          <button class="pika-prev ts-icon-btn" type="button">
            <svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-arrow-left" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M56,29.8H13.3l17-17.3c0.9-0.9,0.9-2.3,0-3.2c-0.9-0.9-2.3-0.9-3.2,0l-20.7,21c-0.9,0.9-0.9,2.3,0,3.2l20.7,21
	c0.4,0.4,1,0.7,1.6,0.7c0.6,0,1.1-0.2,1.6-0.6c0.9-0.9,0.9-2.3,0-3.2L13.4,34.3H56c1.2,0,2.2-1,2.2-2.2C58.2,30.8,57.2,29.8,56,29.8
	z"></path>
            </svg>
          </button>
        </div>
        <table cellpadding="0" cellspacing="0" class="pika-table" role="grid" aria-labelledby="pika-title-ga">
          <thead>
            <tr>
              <th scope="col">
                <abbr title="Monday">Mon</abbr>
              </th>
              <th scope="col">
                <abbr title="Tuesday">Tue</abbr>
              </th>
              <th scope="col">
                <abbr title="Wednesday">Wed</abbr>
              </th>
              <th scope="col">
                <abbr title="Thursday">Thu</abbr>
              </th>
              <th scope="col">
                <abbr title="Friday">Fri</abbr>
              </th>
              <th scope="col">
                <abbr title="Saturday">Sat</abbr>
              </th>
              <th scope="col">
                <abbr title="Sunday">Sun</abbr>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="pika-row">
              <td data-day="1" class="is-selected is-startrange" aria-selected="true">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="1">1</button>
              </td>
              <td data-day="2" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="2">2</button>
              </td>
              <td data-day="3" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="3">3</button>
              </td>
              <td data-day="4" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="4">4</button>
              </td>
              <td data-day="5" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="5">5</button>
              </td>
              <td data-day="6" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="6">6</button>
              </td>
              <td data-day="7" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="7">7</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="8" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="8">8</button>
              </td>
              <td data-day="9" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="9">9</button>
              </td>
              <td data-day="10" class="is-inrange" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="10">10</button>
              </td>
              <td data-day="11" class="is-selected is-endrange" aria-selected="true">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="11">11</button>
              </td>
              <td data-day="12" class="" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="12">12</button>
              </td>
              <td data-day="13" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="13">13</button>
              </td>
              <td data-day="14" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="14">14</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="15" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="15">15</button>
              </td>
              <td data-day="16" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="16">16</button>
              </td>
              <td data-day="17" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="17">17</button>
              </td>
              <td data-day="18" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="18">18</button>
              </td>
              <td data-day="19" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="19">19</button>
              </td>
              <td data-day="20" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="20">20</button>
              </td>
              <td data-day="21" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="21">21</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="22" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="22">22</button>
              </td>
              <td data-day="23" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="23">23</button>
              </td>
              <td data-day="24" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="24">24</button>
              </td>
              <td data-day="25" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="25">25</button>
              </td>
              <td data-day="26" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="26">26</button>
              </td>
              <td data-day="27" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="27">27</button>
              </td>
              <td data-day="28" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="28">28</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="29" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="29">29</button>
              </td>
              <td data-day="30" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="3" data-pika-day="30">30</button>
              </td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pika-lendar">
        <div id="pika-title-hq" class="pika-title" role="heading" aria-live="assertive">
          <div class="pika-label">May <select class="pika-select pika-select-month" tabindex="-1">
              <option value="-1" disabled="disabled">January</option>
              <option value="0" disabled="disabled">February</option>
              <option value="1">March</option>
              <option value="2">April</option>
              <option value="3" selected="selected" disabled="disabled">May</option>
              <option value="4" disabled="disabled">June</option>
              <option value="5" disabled="disabled">July</option>
              <option value="6" disabled="disabled">August</option>
              <option value="7" disabled="disabled">September</option>
              <option value="8" disabled="disabled">October</option>
              <option value="9" disabled="disabled">November</option>
              <option value="10" disabled="disabled">December</option>
            </select>
          </div>
          <div class="pika-label">2024 <select class="pika-select pika-select-year" tabindex="-1">
              <option value="2024" selected="selected">2024</option>
            </select>
          </div>
          <button class="pika-next ts-icon-btn is-disabled" type="button">
            <svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-arrow-right" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M57.6,30.4l-20.7-21c-0.9-0.9-2.3-0.9-3.2,0c-0.9,0.9-0.9,2.3,0,3.2l16.8,17.1H8c-1.2,0-2.2,1-2.2,2.2s1,2.3,2.2,2.3h42.7
	l-17,17.3c-0.9,0.9-0.9,2.3,0,3.2c0.4,0.4,1,0.6,1.6,0.6c0.6,0,1.2-0.2,1.6-0.7l20.7-21C58.5,32.7,58.5,31.3,57.6,30.4z"></path>
            </svg>
          </button>
        </div>
        <table cellpadding="0" cellspacing="0" class="pika-table" role="grid" aria-labelledby="pika-title-hq">
          <thead>
            <tr>
              <th scope="col">
                <abbr title="Monday">Mon</abbr>
              </th>
              <th scope="col">
                <abbr title="Tuesday">Tue</abbr>
              </th>
              <th scope="col">
                <abbr title="Wednesday">Wed</abbr>
              </th>
              <th scope="col">
                <abbr title="Thursday">Thu</abbr>
              </th>
              <th scope="col">
                <abbr title="Friday">Fri</abbr>
              </th>
              <th scope="col">
                <abbr title="Saturday">Sat</abbr>
              </th>
              <th scope="col">
                <abbr title="Sunday">Sun</abbr>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="pika-row">
              <td class="is-empty"></td>
              <td class="is-empty"></td>
              <td data-day="1" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="1">1</button>
              </td>
              <td data-day="2" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="2">2</button>
              </td>
              <td data-day="3" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="3">3</button>
              </td>
              <td data-day="4" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="4">4</button>
              </td>
              <td data-day="5" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="5">5</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="6" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="6">6</button>
              </td>
              <td data-day="7" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="7">7</button>
              </td>
              <td data-day="8" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="8">8</button>
              </td>
              <td data-day="9" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="9">9</button>
              </td>
              <td data-day="10" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="10">10</button>
              </td>
              <td data-day="11" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="11">11</button>
              </td>
              <td data-day="12" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="12">12</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="13" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="13">13</button>
              </td>
              <td data-day="14" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="14">14</button>
              </td>
              <td data-day="15" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="15">15</button>
              </td>
              <td data-day="16" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="16">16</button>
              </td>
              <td data-day="17" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="17">17</button>
              </td>
              <td data-day="18" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="18">18</button>
              </td>
              <td data-day="19" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="19">19</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="20" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="20">20</button>
              </td>
              <td data-day="21" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="21">21</button>
              </td>
              <td data-day="22" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="22">22</button>
              </td>
              <td data-day="23" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="23">23</button>
              </td>
              <td data-day="24" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="24">24</button>
              </td>
              <td data-day="25" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="25">25</button>
              </td>
              <td data-day="26" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="26">26</button>
              </td>
            </tr>
            <tr class="pika-row">
              <td data-day="27" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="27">27</button>
              </td>
              <td data-day="28" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="28">28</button>
              </td>
              <td data-day="29" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="29">29</button>
              </td>
              <td data-day="30" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="30">30</button>
              </td>
              <td data-day="31" class="is-disabled" aria-selected="false">
                <button class="pika-button pika-day" type="button" data-pika-year="2024" data-pika-month="4" data-pika-day="31">31</button>
              </td>
              <td class="is-empty"></td>
              <td class="is-empty"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
         </div>
      </div>
   </div>
   
</div>


<div class="popup-kit-holder">
	<div class="ts-notice ts-notice-info" style="position: static; transform: none; left: auto;    animation: none;">
			<div class="alert-msg">
				<div class="alert-ic">
					<!--?xml version="1.0" encoding="utf-8"?-->
	<!-- Generator: Adobe Illustrator 22.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
	<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-checkmark-circle" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
	<g>
		<path d="M32,1.8C15.3,1.8,1.8,15.3,1.8,32S15.3,62.3,32,62.3S62.3,48.7,62.3,32S48.7,1.8,32,1.8z M32,57.8
			C17.8,57.8,6.3,46.2,6.3,32C6.3,17.8,17.8,6.3,32,6.3c14.2,0,25.8,11.6,25.8,25.8C57.8,46.2,46.2,57.8,32,57.8z"></path>
		<path d="M40.6,22.7L28.7,34.3L23.3,29c-0.9-0.9-2.3-0.8-3.2,0c-0.9,0.9-0.8,2.3,0,3.2l6.4,6.2c0.6,0.6,1.4,0.9,2.2,0.9
			c0.8,0,1.6-0.3,2.2-0.9L43.8,26c0.9-0.9,0.9-2.3,0-3.2S41.5,21.9,40.6,22.7z"></path>
	</g>
	</svg>
					<!--?xml version="1.0" encoding="utf-8"?-->
	<!-- Generator: Adobe Illustrator 22.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
	<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-cross-circle" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
	<g>
		<path d="M32,1.8C15.3,1.8,1.8,15.3,1.8,32S15.3,62.3,32,62.3S62.3,48.7,62.3,32S48.7,1.8,32,1.8z M32,57.8
			C17.8,57.8,6.3,46.2,6.3,32C6.3,17.8,17.8,6.3,32,6.3c14.2,0,25.8,11.6,25.8,25.8C57.8,46.2,46.2,57.8,32,57.8z"></path>
		<path d="M41.2,22.7c-0.9-0.9-2.3-0.9-3.2,0L32,28.8l-6.1-6.1c-0.9-0.9-2.3-0.9-3.2,0c-0.9,0.9-0.9,2.3,0,3.2l6.1,6.1l-6.1,6.1
			c-0.9,0.9-0.9,2.3,0,3.2c0.4,0.4,1,0.7,1.6,0.7c0.6,0,1.2-0.2,1.6-0.7l6.1-6.1l6.1,6.1c0.4,0.4,1,0.7,1.6,0.7
			c0.6,0,1.2-0.2,1.6-0.7c0.9-0.9,0.9-2.3,0-3.2L35.2,32l6.1-6.1C42.1,25,42.1,23.6,41.2,22.7z"></path>
	</g>
	</svg>
					<!--?xml version="1.0" encoding="utf-8"?-->
	<!-- Generator: Adobe Illustrator 22.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
	<svg fill="#1C2033" width="52" height="52" version="1.1" id="lni_lni-alarm" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
	<path d="M57.6,53.1l-2-3.1c-0.4-0.6-0.6-1.2-0.6-1.9V27.3c0-5.9-2.5-11.4-7.1-15.5C44.2,8.5,39.4,6.4,34.3,6V4c0-1.2-1-2.3-2.3-2.3
		c-1.2,0-2.3,1-2.3,2.3v1.9c-0.2,0-0.4,0-0.6,0.1C17.5,7.3,8.8,16.6,8.8,27.7v20.4c-0.1,1-0.3,1.5-0.5,1.8l-1.9,3.2
		c-0.6,1-0.6,2.2,0,3.2c0.6,0.9,1.6,1.5,2.7,1.5h20.7V60c0,1.2,1,2.3,2.3,2.3c1.2,0,2.3-1,2.3-2.3v-2.2H55c1.1,0,2.1-0.6,2.7-1.5
		C58.3,55.3,58.3,54.1,57.6,53.1z M11.5,53.3l0.7-1.2c0.6-1,0.9-2.2,1.1-3.6l0-20.8c0-8.8,7-16.2,16.3-17.2
		c5.7-0.6,11.3,1.1,15.4,4.7c3.6,3.2,5.6,7.5,5.6,12.1v20.8c0,1.5,0.4,2.9,1.3,4.3l0.6,0.9H11.5z"></path>
	</svg>
				</div>
				An account is required to perform this action
			</div>

			<div class="a-btn alert-actions"><a class="ts-btn ts-btn-4" href="http://three-stays.test/auth/">Log in</a><a class="ts-btn ts-btn-4" href="http://three-stays.test/auth/?register">Register</a>
				<a href="#" class="ts-btn ts-btn-4 close-alert">Close</a>
			</div>
		</div>

</div>