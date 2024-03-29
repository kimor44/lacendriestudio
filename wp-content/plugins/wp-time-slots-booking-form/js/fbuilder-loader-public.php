<?php 

header('Content-Type: application/x-javascript; charset=UTF-8'); 

require 'jQuery.stringify.js';
require 'jquery.validate.js';

?>
fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery(function(){
(function($) {
	// Namespace of fbuilder
	$.fbuilder = $.fbuilder || {};
	$.fbuilder[ 'objName' ] = 'fbuilderjQuery';	
	
<?php
	// Load Module files
	try 
	{
        $md = dir( dirname( __FILE__ )."/modules" );
		$modules_files = array();
        while( false !== ( $entry = $md->read() ) ) 
		{    
            if ( strlen( $entry ) > 3 && is_dir( $md->path.'/'.$entry ) )
			{
				if ( file_exists( $md->path.'/'.$entry.'/public' ) )
				{
					$m = dir( $md->path.'/'.$entry.'/public' );
					while( false !== ( $mentry = $m->read() ) )
					{	
						if( strlen( $mentry ) > 3 && strtolower( substr( $mentry, strlen( $mentry ) - 3 ) ) == '.js' )
						{
							$modules_files[] = $m->path.'/'.$mentry;
						}
					}
				}	
						
			}			
        }
		sort( $modules_files );
		foreach( $modules_files as $file )
		{
			require $file;
		}
	} 
	catch (Exception $e) 
	{
        // ignore the error
    }

	// Load Control files
    require 'fbuilder-pro-public.jquery.js';
    try {
        $d = dir( dirname( __FILE__ )."/fields-public" );
		$controls_files = array();
        while (false !== ($entry = $d->read())) {            
            if (strlen($entry) > 3 && strtolower(substr($entry,strlen($entry)-3)) == '.js')
                if ( file_exists( $d->path.'/'.$entry ) )
                    $controls_files[] = $d->path.'/'.$entry;
        }
		sort( $controls_files );
		foreach( $controls_files as $file )
		{
			require $file;
		}
    } catch (Exception $e) {
        // ignore the error
    }
?>
        var fcount = 1;
        var fnum = "_"+fcount;
        while (10>fcount || eval("typeof cp_tslotsbooking_fbuilder_config"+fnum+" != 'undefined'"))
        {
            try {
            var cp_tslotsbooking_fbuilder_config = eval("cp_tslotsbooking_fbuilder_config"+fnum);
            var f = $("#fbuilder"+fnum).fbuilder($.parseJSON(cp_tslotsbooking_fbuilder_config.obj));
			f.fBuild.loadData("form_structure"+fnum);
			$("#cp_tslotsbooking_pform"+fnum).validate({
                ignore:".ignore,.ignorepb",
			    errorElement: "div",
			    errorClass:"cpefb_error",
			    errorPlacement: function(e, element) 
	    	    {
	    	    	if (element.parents(".dfield").find(".cpefb_error.message").not("[style]").length>0)
	    	    	   return;
	    	    	e.insertAfter(element.parents(".dfield").children().last());
	    	    	e.addClass("message");
	    	    }
     		});
     		} catch (e) {}
	    	fcount++;
	    	fnum = "_"+fcount;
	    }
})(fbuilderjQuery);
});