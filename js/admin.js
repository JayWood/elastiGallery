/* Javascript Document */

jQuery(document).ready(function($) {
	// Uploading files
	var file_frame, uploadID;
	
	$('.upload_image_button').live('click', function( event ){
		event.preventDefault();
		uploadID = $(this).attr('data-id');
		console.log(event);
		
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}
		
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false
		});
		
		file_frame.on( 'select', function() {
		  attachment = file_frame.state().get('selection').first().toJSON();
		  
		  $('#'+uploadID).val(attachment.url);	  
		});
		
		file_frame.open();
	});
	//END UPLOAD FUNCTIONS
	
	var tData = $('#d_items tbody');
	var addRow = $('.addrow');
	var delRow = $('.removerow');
	
	// Number of starting fields.
	var sNum = tData.children('tr').length;
	
	addRow.click(function(e){
		sNum = sNum + 1;
		// Get the ID of the last field.
		var newRow = $('.data_row').last().clone(true);

		newRow.attr('id', 'data_row_'+sNum);
		newRow.children('td').each(function(index){
			if( $(this).has('input') ){
				var name = $(this).children('input').first().attr('name');
				if(name != undefined){
					var nName = name.replace(/\[[0-9]\]/, '['+sNum+']');
					//console.log(nName);
				}
				$(this).children('input').first().attr('name', nName);
				$(this).children('input').first().attr('value', '');
				$(this).children('.removerow').attr('id', sNum);
			}
		});
		
		tData.append(newRow);
		
	});
	
	
	delRow.click(function(e){
		var id = $(this).attr('id');
		var len = tData.children('tr').length;
		if( len != 1 ){
			$('#data_row_'+id).fadeOut(500,function(e){
				$('#data_row_'+id).remove();
			})
		}
	});
	
	$('.color_select').spectrum({
		showButtons:	 false,
		showInput:		 true,
		preferredFormat: "hex6"
	});
		
});
