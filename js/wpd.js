jQuery(document).ready(function($){
	
	/* color picker */
	var colorBoxes = ['title_color','content_color','bg_color'];
	var defaultPickerJS = {
		"built_in": {
			"change": function(event, ui){},
			"clear": function() {},
			"defaultColor": "#7c8a93",
			"hide": "false",
			"palettes": "true"
		},
		"class": "",
		"container_id": "body",
		"name": "color_picker",
		"value": ""
	};
		
	$(colorPickerJS).each(function() {
		$('#'+$(this).attr('id')).wpColorPicker(this.built_in);
	})
	
	//init color picker and other stuff on ajax response
	$(document).ajaxSuccess(function(e, xhr, settings) {
		
		var widget_id_base = 'post_snippet_widget';
		if(settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
			var maybeNewId = getQueryStringParam(settings.data,'widget-id');
			var isNew = true;
			$(colorPickerJS).each(function() {
				$('#'+$(this).attr('id')).wpColorPicker(this.built_in);
				if($('#'+$(this).attr('id')) == maybeNewId) {
					isNew = false;
				}
			})
			if(isNew) {
				initNewPicker(maybeNewId);					
			}
			var multi = getQueryStringParam(settings.data,'multi_number');
			if(multi != '') {
				var wid = getQueryStringParam(settings.data,'multi_number')
			} else {
				var wid = getQueryStringParam(settings.data,'widget_number')
			}
			toggleWidgetOptions(wid);
			initHeightBox();
			onchangeHeightBox();
		}
		
	})
	
	function initNewPicker(id) {
		$.each( colorBoxes, function( key, value ) {
			$('#widget-'+id+'-'+value).wpColorPicker(defaultPickerJS.built_in);
		});
	}
	
	function getQueryStringParam(qs, name) {
		var vars = [], hash;
		var q = qs.split('&');
		for(var i = 0; i < q.length; i++){
			hash = q[i].split('=');
			vars.push(hash[1]);
			vars[hash[0]] = hash[1];
		}
		return vars[name];
	}
	//end color picker
	
	
	/* widget */
	
	// toggle sections on click
	function toggleWidgetOptions(wNum) {
		var wn = wNum ? '.sec'+wNum : '';
		$( ".section_container"+wn+" .display_section_title, .section_container"+wn+" .style_section_title, .section_container"+wn+" .content_section_title" ).each(function(){
		   $(this).click(function(){
				$(this).next().slideToggle("fast")
		   })
		})
	}
	toggleWidgetOptions();
	
	// change section arrow direction on click
	$('.display_section_title, .style_section_title').click(function(){
		$(this).toggleClass('section_open');
	})
	
	// change section arrow direction on click
	$('.content_section_title').click(function(){
		$(this).toggleClass('section_open_content');
	})
	
	// disable/enable widget height according to choosen template
	function initHeightBox() {
		$( ".widget_template" ).each(function(index, element){
			if($(element.options[0]).attr('selected') == 'selected') {
			   $(this).parent().next().find('.widget_height').attr('disabled','disabled');
			}
		  }) 
	 }
	 initHeightBox();
	 
	 function onchangeHeightBox() {
		 $( ".widget_template" ).each(function(){
			   $(this).change(function(){
			   if($(this).attr('value') == 'narrow') {	
				   $(this).parent().next().find('.widget_height').attr('disabled','disabled');
			   } else {
				   $(this).parent().next().find('.widget_height').attr('disabled',false);
			   }     
		   })
		})
	}
	onchangeHeightBox();
	
	//dim text box and textarea if override checkbox is checked
	//on load
	function disableTextBoxes() {
		$("[class^='dont_display']").each(function(){ 
				if($(this).attr('checked') == 'checked') {
					$(this).siblings('input, textarea').attr('disabled',true)
				} else {
					$(this).siblings('input, textarea').attr('disabled',false)
				}
		})
	}
	
	//on click
	function disableTextBoxesOnclick() {
		$("[class^='dont_display']").each(function(){ 
			$(this).click(function() {
				if($(this).attr('checked') == 'checked') {
					$(this).siblings('input, textarea').attr('disabled',true)
				} else {
					$(this).siblings('input, textarea').attr('disabled',false)
				}
			})
		})
	}
	
});