"use strict";

var tyche = {
	version: '1.1', // version of tyche constructor.
	constructor: tyche,
	extend: function() {
		let target = arguments[ 0 ] || {},
			length = arguments.length,
			self = this,
			option;

		for ( let i = 0; i < length; i++ ) {

			let args = [];

			// Use default target is none was supplied fpr the first iteration.
			if ( 0 === i ) {

				if ( 1 !== length ) {
					continue;
				}

				target = self;
				args = arguments[ 0 ];
			}

			if ( 0 === args.length ) {
				args = arguments[ i ];
			}

			for ( option in args ) {
				target[ option ] = args[ option ];
			}
		}

		return target;
	},
	init: function() {
		// Here we can run codes we want initialized after tyche object core.
		if ( typeof String.prototype.replaceAll == "undefined" ) {
			String.prototype.replaceAll = function( match, replace ) {
				return this.replace( new RegExp( match, 'g' ), () => replace );
			}
		}
	},
	orddd: {}, // Order Delivery Date Pro for WooCommerce.
	bkap: {}, // Booking & Appointment Plugin for WooCommerce.
	ac: {}, // Abandoned Cart Pro for WooCommerce.
	pdd: {}, // Product Delivery Date Pro for WooCommerce.
	pif: {}, // Product Input Fields for WooCommerce.
	cos: {}, // Custom Order Status for WooCommerce.
	con: {}, // Custom Order Numbers for WooCommerce.
	dw: {}, // Deposits for WooCommerce.
	pur: {}, // Price by User Role for WooCommerce.
	pgf: {}, // Payment Gateway Based Fees and Discounts for WooCommerce.
	cfp: {}, // Call for Price for WooCommerce.
	cpp: {}, // Currency per Product for WooCommerce.

	// Putting some demo keys here, in case we release new plugins in future, we wouldn't need to update tyche.js file across the plugins.
	dm_1: {},
	dm_2: {},
	dm_3: {},
	dm_4: {},
	dm_5: {},
	dm_6: {},
	dm_7: {},
	dm_8: {},
	dm_9: {},
	dm_10: {},
	dm_11: {},
	dm_12: {},
	dm_13: {},
	dm_14: {},
	dm_15: {},

	// Plugin Deactivation.
	plugin_deactivation: {
		fn: {
			return: function( data, index, return_value = '' ) {
				return 'undefined' !== typeof data[ index ] && '' !== data[ index ] ? ( '' !== return_value ? return_value : data[ index ] ) : ''
			}
		},
		modal: function( $plugin, $data ) {

			if ( '' === tyche.plugin_deactivation.fn.return( $data, 'deactivation_data' ) || '' === tyche.plugin_deactivation.fn.return( $data, 'nonce' ) ) {
				return '';
			}

			let data = $data.deactivation_data,
				nonce = $data.nonce;

			if ( 'undefined' === typeof data.template || 'undefined' === typeof data.reasons ) {
				return '';
			}

			let template = data.template,
				reasons = data.reasons,
				html = '';

			if ( Array.isArray( reasons ) && reasons.length > 0 ) {
				reasons.forEach( function( item ) {
					html += `
						<li
							class="reason${tyche.plugin_deactivation.fn.return(item,'input_type',' has-input')}${tyche.plugin_deactivation.fn.return(item,'html',' has-html')}"
							data-input-type="${tyche.plugin_deactivation.fn.return(item,'input_type')}"
							data-input-placeholder="${tyche.plugin_deactivation.fn.return(item,'input_placeholder')}">
								<label>
									<span>
										<input type="radio" name="selected-reason" value="${tyche.plugin_deactivation.fn.return(item,'id')}" />
									</span>
									<span>
										${tyche.plugin_deactivation.fn.return(item,'text')}
									</span>
								</label>
							${'' !== tyche.plugin_deactivation.fn.return(item,'html') ? '<div class="reason_html">' + tyche.plugin_deactivation.fn.return(item,'html') + '</div>' : ''}
						</li>`;
				} );

				html += `<input type="hidden" name="nonce" value="${nonce}" />`;

				let modal = jQuery( template.replace( '{PLUGIN}', $plugin ).replace( '{HTML}', html ) );
				modal.appendTo( jQuery( 'body' ) );

				return modal;
			}
		},

		show_modal: function( modal ) {

			modal.find( '.button' ).removeClass( 'disabled' );

			let btn_deactivate = modal.find( '.button-deactivate' );

			if ( btn_deactivate.length > 0 && modal.hasClass( 'no-confirmation-message' ) && !btn_deactivate.hasClass( 'allow-deactivate' ) ) {
				btn_deactivate.addClass( 'allow-deactivate' );
				modal.find( '.ts-modal-panel' ).removeClass( 'active ' );
				modal.find( '[data-panel-id="reasons"]' ).addClass( 'active' );
			}

			modal.addClass( 'active' );
			jQuery( 'body' ).addClass( 'has-ts-modal' );
		},

		close_modal: function( modal ) {
			modal.removeClass( 'active' );
			jQuery( 'body' ).removeClass( 'has-ts-modal' );
		},

		events: {
			listeners: function( data, modal, plugin ) {
				if ( 0 !== jQuery( `.${plugin}.ts-slug` ).prev( 'a' ) ) {
					jQuery( `.${plugin}.ts-slug` ).prev( 'a' ).on( 'click', function( e ) {
						e.preventDefault();
						tyche.plugin_deactivation.show_modal( modal );
					} );
				}

				modal.on( 'click', '.button-deactivate', function( e ) {
					e.preventDefault();
					tyche.plugin_deactivation.events.button_submit( this, data, plugin );
				} );

				modal.on( 'click', 'input[type="radio"]', function() {
					tyche.plugin_deactivation.events.button_option_selection( this, modal );
				} );

				// If the user has clicked outside the window, cancel it.
				modal.on( 'click', function( e ) {
					tyche.plugin_deactivation.events.button_click_outside_window( e, modal )
				} );
			},

			button_submit: function( $this, $data, plugin ) {

				if ( jQuery( $this ).hasClass( 'disabled' ) || !jQuery( $this ).hasClass( 'allow-deactivate' ) ) {
					return;
				}

				let modal = jQuery( $this ).parents( `.${plugin}.ts-modal` ),
					option = jQuery( 'input[type="radio"]:checked' ),
					reason = option.parents( 'li:first' ),
					response = reason.find( 'textarea, input[type="text"]' ),
					nonce = jQuery( 'input[name="nonce"]' ).val(),
					data = {
						'action': 'tyche_plugin_deactivation_submit_action',
						'reason_id': 0,
						'reason_text': 'Deactivated without any option',
						nonce
					};

				if ( 0 !== option.length ) {
					data.reason_id = option.val();
					data.reason_text = reason.text().trim();
					data.reason_info = 0 !== response.length ? response.val().trim() : '';
				}

				let ajax_url = tyche.plugin_deactivation.fn.return( $data, 'ajax_url' ),
					href = jQuery( `.${plugin}.ts-slug` ).prev().prop( 'href' );

				if ( '' !== ajax_url && '' !== href ) {
					jQuery.ajax( {
						url: ajax_url,
						method: 'POST',
						data,
						beforeSend: function() {
							modal.find( '.button-deactivate' ).addClass( 'disabled' );
						},
						complete: function() {
							window.location.href = href;
						}
					} );
				}
			},

			button_click_outside_window: function( e, modal ) {

				let target = jQuery( e.target );

				// If the user has clicked anywhere in the modal dialog, just return.
				if ( target.hasClass( 'ts-modal-body' ) || target.hasClass( 'ts-modal-footer' ) ) {
					return;
				}

				// If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
				if ( !target.hasClass( 'button-close' ) && ( target.parents( '.ts-modal-body' ).length > 0 || target.parents( '.ts-modal-footer' ).length > 0 ) ) {
					return;
				}

				tyche.plugin_deactivation.close_modal( modal );
			},

			button_option_selection: function( $this, modal ) {

				modal.find( '.reason-input' ).remove();
				jQuery( $this ).parents( 'ul#reasons-list' ).children( "li.li-active" ).removeClass( "li-active" );
				let parent = jQuery( $this ).parents( 'li:first' );

				if ( parent.hasClass( 'has_html' ) ) {
					parent.addClass( 'li-active' );
				}

				if ( parent.hasClass( 'has-input' ) ) {
					parent.append( jQuery( `<div class="reason-input">${'textfield' === parent.data( 'input-type' ) ? '<input type="text" />' : '<textarea rows="5"></textarea>'}</div>` ) );
					parent.find( 'input, textarea' ).attr( 'placeholder', parent.data( 'input-placeholder' ) ).focus();
				}
			}
		}
	}
}

tyche.init();
