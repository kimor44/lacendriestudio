	$.fbuilder.controls[ 'ftext' ]=function(){};
	$.extend(
		$.fbuilder.controls[ 'ftext' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Untitled",
			ftype:"ftext",
			predefined:"",
			predefinedClick:false,
			required:false,
			size:"medium",
			minlength:"",
			maxlength:"",
			equalTo:"",
			show:function()
				{
					return '<div class="fields '+$.fbuilder.htmlEncode(this.csslayout)+'" id="field'+this.form_identifier+'-'+this.index+'"><label for="'+this.name+'">'+$.fbuilder.htmlEncode(this.title)+''+((this.required)?"<span class='r'>*</span>":"")+'</label><div class="dfield"><input id="'+this.name+'" name="'+this.name+'" '+((this.minlength!="")?" minlength=\""+parseInt(this.minlength)+"\"":"")+' '+((this.maxlength!="")?" maxlength=\""+parseInt(this.maxlength)+"\"":"")+' '+((this.equalTo!="")?"equalTo=\"#"+$.fbuilder.htmlEncode(this.equalTo+this.form_identifier)+"\"":"" )+' class="field '+this.size+((this.required)?" required":"")+'" type="text" value="'+$.fbuilder.htmlEncode(this.predefined)+'"/><span class="uh">'+$.fbuilder.htmlEncode(this.userhelp)+'</span></div><div class="clearer"></div></div>';
				}
		}	
	);