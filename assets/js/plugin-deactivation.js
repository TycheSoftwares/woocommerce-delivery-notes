var $wcdn_tyche_plugin_deactivation_modal = {},
	$tyche_plugin_name = 'wcdn';

( function() {

	if ( 'undefined' === typeof tyche.plugin_deactivation || 'undefined' === typeof window[ `tyche_plugin_deactivation_${$tyche_plugin_name}_js` ] ) {
		return;
	}

	var plugin = $tyche_plugin_name;
	var data   = window[ `tyche_plugin_deactivation_${plugin}_js` ];

	$wcdn_tyche_plugin_deactivation_modal = tyche.plugin_deactivation.modal( plugin, data );

	var modal = $wcdn_tyche_plugin_deactivation_modal;

	if ( '' === modal || 'undefined' === typeof modal ) {
		return;
	}

	// Show modal when deactivate link is clicked.
	if ( 0 !== jQuery( `.${ plugin }.ts-slug` ).prev( 'a' ).length ) {
		jQuery( `.${ plugin }.ts-slug` ).prev( 'a' ).on( 'click', function( e ) {
			e.preventDefault();
			showModal();
		} );
	}

	// Skip & Deactivate.
	modal.on( 'click', '.button-skip-deactivate', function( e ) {
		e.preventDefault();
		modal.find( '.ts-modal-footer p' ).hide();
		submitDeactivation( this, true );
	} );

	// Submit & Deactivate.
	modal.on( 'click', '.button-deactivate', function( e ) {
		e.preventDefault();
		modal.find( '.ts-modal-footer p' ).hide();
		submitDeactivation( this, false );
	} );

	// Radio selection.
	modal.on( 'click', 'input[type="radio"]', function() {
		modal.find( '.ts-modal-footer p' ).hide();
		handleOptionSelection( this );
	} );

	// Click outside to close.
	modal.on( 'click', function( e ) {
		var target = jQuery( e.target );
		if ( target.hasClass( 'ts-modal-body' ) || target.hasClass( 'ts-modal-footer' ) ) {
			return;
		}
		if (
			! target.hasClass( 'button-close' ) &&
			( target.parents( '.ts-modal-body' ).length > 0 || target.parents( '.ts-modal-footer' ).length > 0 )
		) {
			return;
		}
		closeModal();
	} );

	function showModal() {
		modal.find( '.button' ).removeClass( 'disabled' );

		var btn_deactivate = modal.find( '.button-deactivate' );
		if ( btn_deactivate.length > 0 && modal.hasClass( 'no-confirmation-message' ) ) {
			modal.find( '.ts-modal-panel' ).removeClass( 'active' );
			modal.find( '[data-panel-id="reasons"]' ).addClass( 'active' );
		}

		modal.addClass( 'active' );
		jQuery( 'body' ).addClass( 'has-ts-modal' );
	}

	function closeModal() {
		modal.removeClass( 'active' );
		jQuery( 'body' ).removeClass( 'has-ts-modal' );
	}

	function submitDeactivation( $this, skip ) {
		if ( jQuery( $this ).hasClass( 'disabled' ) ) {
			return;
		}

		var option      = modal.find( 'input[type="radio"]:checked' ),
			reason      = option.parents( 'li:first' ),
			response    = reason.find( 'textarea, input[type="text"]' ),
			reason_id   = skip ? 0 : option.val(),
			reason_text = skip ? 'Deactivation Reason Skipped' : reason.text().trim();

		if ( 0 === option.length && ! skip ) {
			modal.find( '.ts-modal-footer p' ).css( 'display', 'inline-block' );
			return;
		}

		var ajax_data = {
			action:            'tyche_plugin_deactivation_submit_action',
			reason_id:         reason_id,
			reason_text:       reason_text,
			reason_info:       0 !== response.length ? response.val().trim() : '',
			plugin_short_name: plugin,
			plugin_name:       jQuery( `.${ plugin }.ts-slug` ).attr( 'data-plugin' ),
			nonce:             modal.find( 'input[name="nonce"]' ).val(),
		};

		var ajax_url = tyche.plugin_deactivation.fn.return( data, 'ajax_url' ),
			href     = jQuery( `.${ plugin }.ts-slug` ).prev().prop( 'href' );

		if ( '' !== ajax_url && '' !== href ) {
			jQuery.ajax( {
				url:    ajax_url,
				method: 'POST',
				data:   ajax_data,
				beforeSend: function() {
					modal.find( '.button-deactivate' ).addClass( 'disabled' );
					modal.find( '.button-skip-deactivate' ).addClass( 'disabled' );
				},
				complete: function() {
					window.location.href = href;
				},
			} );
		}
	}

	function handleOptionSelection( $this ) {
		modal.find( '.reason-input' ).remove();
		jQuery( $this ).parents( 'ul#reasons-list' ).children( 'li.li-active' ).removeClass( 'li-active' );

		var parent = jQuery( $this ).parents( 'li:first' );

		if ( parent.hasClass( 'has_html' ) ) {
			parent.addClass( 'li-active' );
		}

		if ( parent.hasClass( 'has-input' ) ) {
			parent.append(
				jQuery(
					`<div class="reason-input">${
						'textfield' === parent.data( 'input-type' )
							? '<input type="text" />'
							: '<textarea rows="5"></textarea>'
					}</div>`
				)
			);
			parent.find( 'input, textarea' )
				.attr( 'placeholder', parent.data( 'input-placeholder' ) )
				.focus();
		}
	}

} )();
