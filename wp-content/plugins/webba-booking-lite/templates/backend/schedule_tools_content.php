<?php
if( ! defined( 'ABSPATH' ) ) exit;
$time_format = WBK_Date_Time_Utils::get_time_format();
date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
$html_time_options = '';
date_default_timezone_set( 'UTC' );
for( $time = 0; $time <= 86400;  $time += 900 ) {
    $temp_time = $time;
    if ( $time == 0 ){
    	$selected = ' selected ';
    } else {
    	$selected = '';
    }
    $html_time_options .= '<option ' . $selected . ' value="' . $temp_time . '">' . wp_date ( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . '</option>';
}
date_default_timezone_set( 'UTC' );

?>

<div class="appearance-block-wb">
    <div class="left-part-wb">
		<div class="appearance-menu-wrapper-wb">
			<ul class="appearance-menu-wb" data-js="appearance-menu-wb">
				<li class="active-wb" data-name="date_auto_lock"><?php echo __( 'Date auto lock / unlock', 'wbk' );?></li>
				<li data-name="time_auto_lock" class=""><?php echo __( 'Time slot auto lock / unlock', 'wbk' ); ?></li>
                <li data-name="mass_add_bookings" class=""><?php echo __( 'Create multiple bookings', 'wbk' ); ?></li>
			</ul><!-- /.appearance-menu-wb -->
		</div><!-- /.appearance-menu-wrapper-wb -->

		<div class="appearance-tabs-wb" data-js="appearance-tabs-wb">
    		<div class="single-tab-wb active-wb" data-js="single-tab-wb" data-name="date_auto_lock">
    			<div class="field-block-wb">
    				<label class="label-wb"><b><?php echo __( 'Choose an action', 'wbk' ) ?></b></label>
    				<div class="radio-row-wb">
    					<label class="custom-radiobutton-wb">
    						<input type="radio" checked="" class="schedule-tools-action-lock" name="schedule-tools-time-action" data-js="radio-schedule-tools-action-wb" data-name="lock">
    						<span class="checkmark-wb"></span>
    						<span class="radio-title-wb">Lock dates</span>
    					</label>
                    </div>
                    <div class="radio-row-wb">
						<label class="custom-radiobutton-wb">
							<input type="radio" class="schedule-tools-action-unlock" name="schedule-tools-time-action" data-js="radio-schedule-tools-action-wb" data-name="unlock">
							<span class="checkmark-wb"></span>
							<span class="radio-title-wb">Unlock dates</span>
						</label>
    				</div>
    			</div><!-- /.field-wrapper -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Select service', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select class="wbk_schedule_tools_service_id" name="schedule_tools_service_id">
                            <option value="0"><?php echo __( 'Select service', 'wbk'); ?></option>
                        <?php
                            $services = WBK_Model_Utils::get_services();
                            foreach( $services as $id => $name ){
                         ?>
                            <option value="<?php echo $id; ?>"><?php  echo $name; ?></option>
                        <?php
                            }
                        ?>

                        </select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Or category', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select class="wbk_schedule_tools_category_id" name="schedule_tools_category_id">
                            <option value="0"><?php echo __( 'Select category', 'wbk'); ?></option>
                        <?php
                            $categories = WBK_Model_Utils::get_service_categories();
                            foreach( $categories as $id => $name ){
                         ?>
                            <option value="<?php echo $id; ?>"><?php  echo $name; ?></option>
                        <?php
                            }
                        ?>
                        </select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Lock dates in the range', 'wbk' ) ?></b></label>
                    <div class="field-wrapper-wb">
    					<input type="text" value="" class="schedule_tools_date_range" name="schedule_tools_date_range" class="input-text-wb">
    				</div>
                </div>
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Except the following dates', 'wbk' ) ?></b></label>
                    <div class="field-wrapper-wb">
                        <input type="text" value="" class="schedule_tools_date_range_exclude" name="schedule_tools_date_range_exclude" class="input-text-wb">
                    </div>
                </div>
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Apply only for the next days of the week', 'wbk' ) ?></b></label>
                    <div class="custom-multiple-select-wb-holder">
                        <div class="custom-multiple-select-wb">
                            <select class="schedule_tools_days_of_week" multiple >
                                <option selected value="1"><?php echo __( 'Monday', 'wbk' ); ?></option>
                                <option selected value="2"><?php echo __( 'Tuesday', 'wbk' ); ?></option>
                                <option selected value="3"><?php echo __( 'Wednesday', 'wbk' ); ?></option>
                                <option selected value="4"><?php echo __( 'Thursday', 'wbk' ); ?></option>
                                <option selected value="5"><?php echo __( 'Friday', 'wbk' ); ?></option>
                                <option selected value="6"><?php echo __( 'Saturday', 'wbk' ); ?></option>
                                <option selected value="7"><?php echo __( 'Sunday', 'wbk' ); ?></option>
                            </select>
    					</div>
                    </div>
                </div><!-- /.field-block-wb -->
            </div><!-- /.singl[e-tab-wb -->

            <div class="single-tab-wb" data-js="single-tab-wb" data-name="time_auto_lock">
    			<div class="field-block-wb">
    				<label class="label-wb"><b><?php echo __( 'Choose an action', 'wbk' ) ?></b></label>
				    <div class="radio-row-wb">
    					<label class="custom-radiobutton-wb">
    						<input type="radio" checked="" class="schedule-tools-action-lock" name="schedule-tools-date-action" data-js="radio-schedule-tools-action-wb" data-name="lock">
    						<span class="checkmark-wb"></span>
    						<span class="radio-title-wb">Lock time slots</span>
    					</label>
                    </div>
                    <div class="radio-row-wb">
						<label class="custom-radiobutton-wb">
							<input type="radio" class="schedule-tools-action-unlock" name="schedule-tools-date-action" data-js="radio-schedule-tools-action-wb" data-name="unlock">
							<span class="checkmark-wb"></span>
							<span class="radio-title-wb">Unlock time slots</span>
						</label>
					</div>
    			</div><!-- /.field-wrapper -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Select service', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select class="wbk_schedule_tools_service_id" name="schedule_tools_service_id">
                            <option value="0"><?php echo __( 'Select service', 'wbk'); ?></option>
                        <?php
                            $services = WBK_Model_Utils::get_services();
                            foreach( $services as $id => $name ){
                         ?>
                            <option value="<?php echo $id; ?>"><?php  echo $name; ?></option>
                        <?php
                            }
                        ?>

                        </select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Or category', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select class="wbk_schedule_tools_category_id"  name="schedule_tools_category_id">
                            <option value="0"><?php echo __( 'Select category', 'wbk'); ?></option>
                        <?php
                            $categories = WBK_Model_Utils::get_service_categories();
                            foreach( $categories as $id => $name ){
                         ?>
                            <option value="<?php echo $id; ?>"><?php  echo $name; ?></option>
                        <?php
                            }
                        ?>
                        </select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Lock time slots in the range', 'wbk' ) ?></b></label>
                    <div class="field-wrapper-wb">
    					<input type="text" value="" class="schedule_tools_date_range" name="schedule_tools_date_range" class="input-text-wb">
    				</div>
                </div>
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'From', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select name="schedule_tools_time_from" class="schedule_tools_time_from"><?php echo $html_time_options ?></select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'To', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                        <select name="schedule_tools_time_from" class="schedule_tools_time_to"><?php echo $html_time_options ?></select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Apply only for the next days of the week', 'wbk' ) ?></b></label>
                    <div class="custom-multiple-select-wb-holder">
                        <div class="custom-multiple-select-wb">
                            <select class="schedule_tools_days_of_week" multiple >
                                <option selected value="1"><?php echo __( 'Monday', 'wbk' ); ?></option>
                                <option selected value="2"><?php echo __( 'Tuesday', 'wbk' ); ?></option>
                                <option selected value="3"><?php echo __( 'Wednesday', 'wbk' ); ?></option>
                                <option selected value="4"><?php echo __( 'Thursday', 'wbk' ); ?></option>
                                <option selected value="5"><?php echo __( 'Friday', 'wbk' ); ?></option>
                                <option selected value="6"><?php echo __( 'Saturday', 'wbk' ); ?></option>
                                <option selected value="7"><?php echo __( 'Sunday', 'wbk' ); ?></option>
                            </select>
    					</div>
                    </div>
                </div><!-- /.field-block-wb -->
            </div><!-- /.singl[e-tab-wb -->

            <div class="single-tab-wb" data-js="single-tab-wb" data-name="mass_add_bookings">

                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Select service', 'wbk' ) ?></b></label>
                    <div class="custom-select-wb">
                          <select class="schedule_tools_mass_add_service_id" id="schedule_tools_mass_add_service_id">
                            <option value="0"><?php echo __( 'Select service', 'wbk'); ?></option>
                        <?php
                            $services = WBK_Model_Utils::get_services();
                            foreach( $services as $id => $name ){
                         ?>
                            <option value="<?php echo $id; ?>"><?php  echo $name; ?></option>
                        <?php
                            }
                        ?>
                        </select>
                    </div><!-- /.field-wrapper-wb -->
                </div><!-- /.field-block-wb -->

 
                <div class="field-block-wb">
                    <label class="label-wb"><b><?php echo __( 'Select date', 'wbk' ) ?></b></label>
                    <div class="field-wrapper-wb">
                        <input type="text" value="" class="schedule_tools_single_date" name="schedule_tools_single_date" class="input-text-wb">
                    </div>
                </div>

              
                <div id="multiple_booking_form_container">

                </div>      
            </div>

		</div><!-- /.appearance-tabs-wb -->

		<div class="buttons-block-wb">
			<button data-url=<?php echo esc_url_raw( parse_url( rest_url(), PHP_URL_PATH ) ) ?> data-nonce="<?php echo wp_create_nonce( 'wp_rest' ); ?>"  class="button-wb schedule_tools_start_btn"><?php echo __( 'Start', 'wbk');?><span class="btn-ring-wb"></span></button>

		</div><!-- /.buttons-block-wb -->

	</div>
</div>
