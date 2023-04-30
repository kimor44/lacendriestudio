// WEBBA Booking settings page scripts
// onload function

jQuery(function ($) {
    jQuery( '#tabs' ).tabs();
    var format = 'yyyy-mm-dd';
   	jQuery( '#wbk_holydays' ).datepick( {multiSelect: 999, monthsToShow: 3, dateFormat: format });

   	jQuery( '.wbk_customer_message_btn' ).on( 'click', function() {
	    var caretPos = document.getElementById( 'wbk_email_customer_book_message' ).selectionStart;
	    var textAreaTxt = jQuery( '#wbk_email_customer_book_message' ).val();
	    var txtToAdd = '#' + jQuery(this).attr('id');
	    var newCaretPos = caretPos + txtToAdd.length;
	    jQuery( '#wbk_email_customer_book_message' ).val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
	    jQuery( '#wbk_email_customer_book_message' ).focus();
	    document.getElementById( 'wbk_email_customer_book_message' ).setSelectionRange(newCaretPos, newCaretPos);
	});
   	jQuery( '.wbk_email_editor_toggle' ).on( 'click', function() {
		jQuery(this).siblings('.wbk_email_editor_wrap').toggle('fast');
	});

    jQuery('.wbk_option_field_select_multiple').chosen({width: '300px'});

     jQuery('#wbk_remove_ediotors').on( 'click', function() {
        if( tinymce.editors.length > 0 ) {
            for( i = 0; i < tinymce.editors.length; i++ )  {
               tinyMCE.editors[ i ].destroy();
            }
         }
        return false;
    });

    function wbk_init_dependencies() {

        jQuery('.wbk_option_block').each(function() {
            var dependency = JSON.parse(jQuery(this).attr('data-dependency'));
            for (var slug in dependency) {
                var valueString = dependency[slug];
                var valueArray = valueString.split('|');
                var elememt = jQuery(this);
                var $check = false;
                valueArray.forEach(function(value, i, valueArray) {
                    if (value == jQuery('#' + slug).val()) {
                        $check = true;
                    }
                });
                if ($check) {
                    elememt.closest( "tr" ).show();
                } else {
                    elememt.closest( "tr" ).hide();
                }
            }
        });
    }
    wbk_init_dependencies();
    jQuery(document).on('input',function(){
        wbk_init_dependencies()
    });
    jQuery('.wbk_zoom_remove_auth').click( function(){
        jQuery('#wbk_zoom_auth_stat').val('');
        jQuery(this).replaceWith('Please, do not forget to save settings.');
        jQuery('.wbk_zoom_authorized_label').remove();

    });

});
