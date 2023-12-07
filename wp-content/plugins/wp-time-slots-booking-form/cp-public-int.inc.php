<?php if ( !defined('CP_AUTH_INCLUDE') ) { echo 'Direct access not allowed.'; exit; } ?>
<form class="cpp_form" name="<?php echo esc_attr($this->prefix); ?>_pform<?php echo '_'.esc_attr($this->print_counter); ?>" id="<?php echo esc_attr($this->prefix); ?>_pform<?php echo '_'.esc_attr($this->print_counter); ?>" action="<?php $this->get_site_url(); ?>" method="post" enctype="multipart/form-data" onsubmit="return <?php echo esc_attr($this->prefix); ?>_pform_doValidate<?php echo '_'.esc_attr($this->print_counter); ?>(this);"><input type="hidden" name="cp_pform_psequence" value="<?php echo '_'.esc_attr($this->print_counter); ?>" /><input type="hidden" name="<?php echo esc_attr($this->prefix); ?>_pform_process" value="1" /><input type="hidden" name="<?php echo esc_attr($this->prefix); ?>_id" value="<?php echo intval($this->item); ?>" /><input type="hidden" name="cp_ref_page" value="<?php esc_attr($this->get_site_url()); ?>" /><input type="hidden" name="form_structure<?php echo '_'.esc_attr($this->print_counter); ?>" id="form_structure<?php echo '_'.esc_attr($this->print_counter); ?>" size="180" value="<?php echo $this->clean_sanitize($raw_form_str); ?>" /><input type="hidden" name="refpage<?php echo '_'.esc_attr($this->print_counter); ?>" id="refpage<?php echo '_'.esc_attr($this->print_counter); ?>" value=""><input type="hidden" name="<?php echo esc_attr($this->prefix); ?>_pform_status" value="0" />
<?php if (is_admin() && !defined('WPTIMESLOTSBK_ELEMENTOR_EDIT_MODE') && (empty($_GET["action"]) || @$_GET["action"] != 'edit')) {?>
  <fieldset style="border: 1px solid black; -webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; padding:15px;">
   <legend><?php _e('Administrator options','wp-time-slots-booking-form'); ?></legend>
    <input type="checkbox" name="sendemails_admin" value="1" vt="1" checked /> <?php _e('Send notification emails for this booking','wp-time-slots-booking-form'); ?><br /><br />    
    <div id="wptsremoveval"> <input type="checkbox" name="ignorewptsval" value="1" vt="1" onclick="wptsingnoreval();" /><?php _e('Ignore validation of required fields?','wp-time-slots-booking-form'); ?></div>
    <div id="wptsremovedval" style="display:none"> <input type="checkbox" name="ignorewptsval" value="1" vt="1" checked disabled /> <?php echo esc_js(__('Required field validation ignored!','wp-time-slots-booking-form')); ?></div>
    <script>
        function wptsingnoreval() {
           jQuery(".required").removeClass("required");
           jQuery("#wptsremoveval").hide();
           jQuery("#wptsremovedval").show();
         }
    </script>
  </fieldset> 
<?php } ?>                         
<div id="fbuilder">    
    <div id="fbuilder<?php echo '_'.esc_attr($this->print_counter); ?>">
        <div id="formheader<?php echo '_'.esc_attr($this->print_counter); ?>"></div>
        <div id="fieldlist<?php echo '_'.esc_attr($this->print_counter); ?>"></div>
    </div>
</div>
<div style="display:none">
<div id="cpcaptchalayer<?php echo '_'.esc_attr($this->print_counter); ?>" class="cpcaptchalayer">
 <div class="fields" id="field-c0" style="display:none">
    <label></label>
    <div class="dfield"><!--addons-payment-options--></div>
    <div class="clearer"></div>
 </div><!--addons-payment-fields-->
<?php if (!is_admin() && $this->get_option('cv_enable_captcha', CP_TSLOTSBOOK_DEFAULT_cv_enable_captcha) != 'false') { ?>
  <?php _e("Security Code",'wp-time-slots-booking-form'); ?>:<br />
  <img src="<?php echo esc_url($this->get_site_url()).'/?'.$this->prefix.'_captcha=captcha&ps=_'.$this->print_counter.'&inAdmin=1&width='.$this->get_option('cv_width', CP_TSLOTSBOOK_DEFAULT_cv_width).'&height='.$this->get_option('cv_height', CP_TSLOTSBOOK_DEFAULT_cv_height).'&letter_count='.$this->get_option('cv_chars', CP_TSLOTSBOOK_DEFAULT_cv_chars).'&min_size='.$this->get_option('cv_min_font_size', CP_TSLOTSBOOK_DEFAULT_cv_min_font_size).'&max_size='.$this->get_option('cv_max_font_size', CP_TSLOTSBOOK_DEFAULT_cv_max_font_size).'&noise='.$this->get_option('cv_noise', CP_TSLOTSBOOK_DEFAULT_cv_noise).'&noiselength='.$this->get_option('cv_noise_length', CP_TSLOTSBOOK_DEFAULT_cv_noise_length).'&bcolor='.$this->get_option('cv_background', CP_TSLOTSBOOK_DEFAULT_cv_background).'&border='.$this->get_option('cv_border', CP_TSLOTSBOOK_DEFAULT_cv_border).'&font='.$this->get_option('cv_font', CP_TSLOTSBOOK_DEFAULT_cv_font); ?>"  id="captchaimg<?php echo '_'.esc_attr($this->print_counter); ?>" alt="security code" border="0" class="skip-lazy"  />
  <br /><?php _e("Please enter the security code",'wp-time-slots-booking-form'); ?>:<br />
  <div class="dfield"><input type="text" size="20" name="hdcaptcha_<?php echo esc_attr($this->prefix); ?>_post" id="hdcaptcha_<?php echo esc_attr($this->prefix); ?>_post<?php echo '_'.esc_attr($this->print_counter); ?>" value="" />
  <div class="cpefb_error message" id="hdcaptcha_error<?php echo '_'.esc_attr($this->print_counter); ?>" generated="true" style="display:none;position: absolute; left: 0px; top: 25px;"><?php echo esc_attr($this->translate_dynamic($this->get_option('cv_text_enter_valid_captcha', CP_TSLOTSBOOK_DEFAULT_cv_text_enter_valid_captcha))); ?></div>
  </div><br />
<?php } ?><!-- rcadon -->
</div>
</div>
<div id="cp_subbtn<?php echo '_'.esc_attr($this->print_counter); ?>" class="cp_subbtn"><?php echo esc_html($button_label); ?></div>
<div style="clear:both;"></div>
</form>