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
    var nonce = jQuery('#wcdn_remove_shoplogo_nonce').val();
    jQuery.ajax({
        type: "POST",
        url: admin_object.ajax_url,
        data: {
            action: "wcdn_remove_shoplogo",
            shop_logoid: shop_logoid,
            wcdn_remove_shoplogo_nonce: nonce
        },
        success: function() {
            jQuery('.file-upload-content').hide();
            jQuery('.image-upload-wrap').show();
        }
    });
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

	// Block settings when default template is selected.
	$(document).ready(function() {
		$('.accordion-button').click(function() {
			$(this).closest('.accordion-item').toggleClass('expanded');
		});
	
		var documentTypeElement = document.querySelector('#document_type');
		var document_type = documentTypeElement ? documentTypeElement.value : null;
		var targetCondition;
	
		if (document_type === 'wcdn_invoice') {
			targetCondition = '#ct_acc_2_content';
		} else if (document_type === 'wcdn_receipt' || document_type === 'wcdn_deliverynote') {
			targetCondition = '#ct_acc_1_content';
		}
	
		if (typeof admin_object !== 'undefined' && admin_object.template_save === 'default' && targetCondition) {
			$('.accordion-button').each(function() {
				var target = $(this).attr('data-bs-target');
				if (target !== targetCondition) {
					$(this).attr('disabled', true).attr('title', '');
					if (!(document_type === 'wcdn_invoice' && target === '#ct_acc_1_content')) {
						$(this).closest('.accordion-item').addClass('disabled');
					}
				}
			});
			$('.accordion-button').eq(0).attr('disabled', false).removeAttr('title');
			$('.accordion-item .switch').css('pointer-events', 'none');
			// Add hover message for parent elements of .switch with disabled accordion-button.
			$('.accordion-item.disabled').each(function() {
				var switchElement = $(this).find('.switch');
				if (switchElement.length) {
					var overlay = $('<div class="checkbox-overlay"></div>');
					var tooltip = $('<div class="hover-tooltip">Change the template from Default to Simple from the General Settings to enable customization.</div>');
					
					overlay.css({
						position: 'absolute',
						top: 0,
						left: 0,
						width: '100%',
						height: '100%',
						background: 'rgba(255, 255, 255, 0.7)',
						zIndex: 999,
						borderRadius: '34px'
					}).show();
	
					tooltip.css({
						position: 'absolute',
						background: '#fff',
						color: '#000',
						padding: '2px',
						border: '1px solid #000',
						borderRadius: '3px',
						fontSize: '12px',
						zIndex: 1000,
						display: 'none'
					});
					
					switchElement.append(overlay);
					$('body').append(tooltip);
					$(this).hover(function() {
						tooltip.fadeIn(200);
					}, function() {
						tooltip.hide();
					}).mousemove(function(event) {
						tooltip.css({
							top: event.pageY + 10 + 'px',
							left: event.pageX + 10 + 'px'
						});
					});
				}
			});
		
		}
	});

	//Set checkbox on and off when default template is selected.
	$(document).ready(function() {
		if (typeof admin_object !== 'undefined' && admin_object.template_save === 'default') {
			var documentTypeElement = document.querySelector('#document_type');
			var document_type = documentTypeElement ? documentTypeElement.value : null;
			var checkboxes = document.querySelectorAll('.custom-checkbox');
	
			if (document_type === 'wcdn_receipt' || document_type === 'wcdn_deliverynote') {
				for (var i = 1; i < checkboxes.length; i++) {
					if ((document_type === 'wcdn_receipt' && (i === 3 || i === 5 || i === 9 || i === 18)) ||
						(document_type === 'wcdn_deliverynote' && (i === 3 || i === 5))) {
						checkboxes[i].checked = false;
					} else {
						checkboxes[i].checked = true;
					}
				}
			} else if (document_type === 'wcdn_invoice') {
				for (var i = 2; i < checkboxes.length; i++) {
					if (i === 4) {
						checkboxes[i].checked = false;
					} else {
						checkboxes[i].checked = true;
					}
				}
			}
		}
	});
	
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	const wdcn_setting = urlParams.get('wdcn_setting');

	$('#document_type').val(wdcn_setting);
	
	jQuery(document).ready(function($) {
		var document_type = $('#document_type').val();
		if (document_type === 'wcdn_invoice') {
			// Show Invoice field
			$('#invoice_checkbox').closest('.form-group').show();
			$('.accordion-button').eq(0).attr('disabled', false);
			$('.accordion-button').eq(1).attr('disabled', false);
			$('.accordion-item .switch').eq(0).css('pointer-events', 'auto');
			$('.accordion-item .switch').eq(1).css('pointer-events', 'auto');

			// Hide Receipt and Delivery Notes fields
			$('#receipt, #delivery_note').closest('.form-group').hide();
		} else {
			// Hide Invoice field
			$('#invoice-checkbox').hide();
		}
		if (document_type === 'wcdn_receipt') {
			// Show Invoice field
			$('#receipt').closest('.form-group').show();
			$('.accordion-item .switch').eq(0).css('pointer-events', 'auto');

			// Hide Receipt and Delivery Notes fields
			$('#invoice_checkbox, #delivery_note').closest('.form-group').hide();
		} else {
			// Hide Invoice field
			$('#receipt').hide();
		}
		if (document_type === 'wcdn_deliverynote') {
			// Show Invoice field
			$('#delivery_note').closest('.form-group').show();
			$('.accordion-item .switch').eq(0).css('pointer-events', 'auto');
			
			// Hide Receipt and Delivery Notes fields
			$('#invoice_checkbox, #receipt').closest('.form-group').hide();
		} else {
			// Hide Invoice field
			$('#delivery_note').hide();
		}
	});

	if (typeof $.fn.tooltip === 'function') {
		$('[data-toggle="tooltip"]').tooltip();
	}
	
	var footer = jQuery(".wcdn-footer-top").html();
	jQuery("#mainform").append( footer );
});
