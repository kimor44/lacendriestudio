	$.fbuilder.controls[ 'femail' ] = function(){};
	$.extend( 
		$.fbuilder.controls[ 'femail' ].prototype, 
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Email",
			ftype:"femail",
			predefined:"",
			predefinedClick:false,
			required:false,
			size:"medium",
			equalTo:"",
			show:function()
				{
					return '<div class="fields '+$.fbuilder.htmlEncode(this.csslayout)+'" id="field'+this.form_identifier+'-'+this.index+'"><label for="'+this.name+'">'+$.fbuilder.htmlEncode(this.title)+''+((this.required)?"<span class='r'>*</span>":"")+'</label><div class="dfield"><input id="'+this.name+'" name="'+this.name+'" '+((this.equalTo!="")?"equalTo=\"#"+$.fbuilder.htmlEncode(this.equalTo+this.form_identifier)+"\"":"" )+' class="field email '+this.size+((this.required)?" required":"")+'" type="text" value="'+$.fbuilder.htmlEncode(this.predefined)+'"/><span class="uh">'+$.fbuilder.htmlEncode(this.userhelp)+'</span></div><div class="clearer"></div></div>';
				},
			after_show:function()
				{
					$( "#"+this.name ).keyup(function() {
                      $(this).val($(this).val().trim());
                    });
				},
			val:function()
				{
					var e = $( '[id="' + this.name + '"]:not(.ignore)' );
					if( e.length ) return $.fbuilder.parseValStr( e.val() );
					return '';
				}	
		}
	);