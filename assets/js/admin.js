function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function(e) {
  			jQuery('.image-upload-wrap').hide();

  			jQuery('.file-upload-image').attr('src', e.target.result);
  			jQuery('.file-upload-content').show();

  			jQuery('.image-title').html(input.files[0].name);
		};
		reader.readAsDataURL(input.files[0]);
	} else {
		removeUpload();
	}
}

function removeUpload() {
  	jQuery('.file-upload-input').replaceWith(jQuery('.file-upload-input').clone());
  	var shop_logoid = jQuery('input[name="shop_logoid"]').val();
  	jQuery.ajax({
		type : "post",
        dataType : "json",
        url : admin_object.ajax_url,
        data : {action: "wcdn_remove_shoplogo", 'shop_logoid': shop_logoid },
        success: function(response) {
           	jQuery('.file-upload-content').hide();
			jQuery('.image-upload-wrap').show();
        }
    }) 
}

jQuery(document).ready(function($) {
    $('.wcdn_email').select2();
    $('body').on('click', '.wcdn_back_arrow', function() {
	    window.location.href = admin_object.admin_url+'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document';
	});

	/*
	 * Print button
	*/	 
	// Button on list and edit screen
	$('.print-preview-button').printLink();
	$('.print-preview-button').on('printLinkInit', function(event) {
		$(this).parent().find('.print-preview-loading').addClass('is-active');
	});
	$('.print-preview-button').on('printLinkComplete', function(event) {
		$('.print-preview-loading').removeClass('is-active');
	});
	$('.print-preview-button').on('printLinkError', function(event) {
		$('.print-preview-loading').removeClass('is-active');
		tb_show('', $(this).attr('href') + '&amp;TB_iframe=true&amp;width=800&amp;height=500');
	});

	/*
	 * Bulk actions print button in the confirm message
	 */	
	$(window).on('load', function(event) {
		var bulkButton = $('#woocommerce-delivery-notes-bulk-print-button');
		if( bulkButton.length > 0 ) {
			bulkButton.trigger('click');
		}
	});

	/*
	 * Settings
	*/	 
	 
	// Media managment
	var media_modal;
 
	// Button to open the media uploader
	$('.wcdn-image-select-add-button, .wcdn-image-select-attachment').on('click', function(event) {
		event.preventDefault();
		
		// If the modal already exists, reopen it.
		if(media_modal) {
			media_modal.open();
			return;
		}
		
		// Create the modal.
		media_modal = wp.media.frames.media_modal = wp.media({
			title: $('.wcdn-image-select-add-button').data( 'uploader-title' ),
			button: {
				text: $('.wcdn-image-select-add-button').data( 'uploader-button-title' ),
			},
			multiple: false 
		});
		
		// Open the modal.
		media_modal.open();
		
		// When an image is selected, run a callback.
		media_modal.on( 'select', function(event) {
			// We set multiple to false so only get one image from the uploader
			var attachment = media_modal.state().get('selection').first().toJSON();
			
			// Do something with attachment.id and/or attachment.url here
			addImage(attachment.id);
		});
	});
	
	// Button to remove the media 
	$('.wcdn-image-select-remove-button').on('click', function(event) {
		event.preventDefault();
		removeImage();
	});
	
	// add media 
	function addImage(id) {
		removeImage();
		$('.wcdn-image-select-spinner').addClass('is-active');

		// load the image		
		var data = {
			attachment_id: id,
			action: 'wcdn_settings_load_image',
			nonce: $('.submit #_wpnonce').val()
		}
		
		$.post(ajaxurl, data, function(response) {
			$('.wcdn-image-select-image-id').val(data.attachment_id);		
			$('.wcdn-image-select-attachment .thumbnail').html(response);
			$('.wcdn-image-select-spinner').removeClass('is-active');
			$('.wcdn-image-select-add-button').addClass('hidden');
			$('.wcdn-image-select-remove-button').removeClass('hidden');
		}).error(function() {
			removeImage();
		});
	}
	
	// remove media 
	function removeImage() {
		$('.wcdn-image-select-image-id').val('');		
		$('.wcdn-image-select-attachment .thumbnail').empty();
		$('.wcdn-image-select-spinner').removeClass('is-active');
		$('.wcdn-image-select-add-button').removeClass('hidden');
		$('.wcdn-image-select-remove-button').addClass('hidden');
	}
	
	$('input#woocommerce_demo_store').change(function() {
		if ($(this).is(':checked')) {
			$('#woocommerce_demo_store_notice').closest('tr').show();
		} else {
			$('#woocommerce_demo_store_notice').closest('tr').hide();
		}
	}).change();
	
	// Toggle invoice number fields
	$('input[name="wcdn_invoice\\[numbering\\]"]').on('change', function(event) {
		if ($(this).is(':checked')) {
			$('.wcdn_depend_row').show();
		} else {
			$('.wcdn_depend_row').hide();
		}
	});

	$(document).ready(function(){
		var initialValue = $('select[name*="[template_setting][template_setting_template]"]').val();
		if (initialValue == 'default') {
			$('.accordion-button').click(function(){
				$(this).closest('.accordion-item').toggleClass('expanded');
			});
			$('.accordion-button').attr('disabled', true);
			$('.accordion-item .switch').css('pointer-events', 'none');
		}
	});

	
	$('select[name*="\\[template_setting\\][\\template_setting_template\\]"]').change(function () {
		if( this.value == 'simple' ) {
			jQuery('.accordion-button').attr('disabled', false);
      jQuery('.accordion-item .switch').css('pointer-events', 'auto');
			jQuery('.wcdn_for_simple').css('display','block');
			jQuery('.wcdn_for_default').css('display','none');

		} else {
			jQuery('.accordion-button').attr('disabled', true);
      jQuery('.accordion-item .switch').css('pointer-events', 'none');
			jQuery('.wcdn_for_simple').css('display','none');
			jQuery('.wcdn_for_default').css('display','block');
		}
    });

	$('[data-toggle="tooltip"]').tooltip();
	
	var footer = jQuery(".wcdn-footer-top").html();
	jQuery("#mainform").append( footer );
});

