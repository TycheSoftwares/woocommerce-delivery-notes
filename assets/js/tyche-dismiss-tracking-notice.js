jQuery(document).ready( function() {
	jQuery( '.notice.is-dismissible' ).each( function() {
		var $this = jQuery( this ),
			$button = jQuery( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
			btnText = wp.i18n.dismiss || '';

		// Ensure plain text
		$button.find( '.screen-reader-text' ).text( btnText );

		$this.append( $button );

		/**
		 * Event when close icon is clicked.
		 * @fires event:notice-dismiss
		 * @since 6.8
		*/
		$button.on( 'click.notice-dismiss', function( event ) {
			event.preventDefault();
			$this.fadeTo( 100 , 0, function() {
				//alert();
				jQuery(this).slideUp( 100, function() {
					jQuery(this).remove();				
					jQuery.post(
						wcdn_ts_dismiss_notice.ts_admin_url,
						{
							action: wcdn_ts_dismiss_notice.ts_prefix_of_plugin + "_tracker_dismiss_notice",
							tracking_notice : wcdn_ts_dismiss_notice.tracking_notice,
						},
						function( response ) {}
					);
				});
			});
		});
	});
});

jQuery( document ).ready( function() {
	jQuery('.reset_tracking').click(function() {
	var data = {
		action: 'ts_reset_tracking_setting',
		plugin_short_name: 'wcdn',
		ts_tracker_nonce : wcdn_ts_dismiss_notice.tracking_notice,
	}
	jQuery.post(ajaxurl, data, function( res ) {
		if ( 'success' == res.message ) {
			window.location.href = res.redirect_url;
		}
		}).error(function() {
			console.log('error');
		});
	});
});

jQuery( document ).ready( function() {
	jQuery('.reset_tracking').click(function() {
	var data = {
		action: 'ts_reset_tracking_setting',
		plugin_short_name: 'wcdn',
		ts_tracker_nonce : wcdn_ts_dismiss_notice.tracking_notice,
	}
	jQuery.post(ajaxurl, data, function( res ) {
		if ( 'success' == res.message ) {
			window.location.href = res.redirect_url;
		}
		}).error(function() {
			console.log('error');
		});
	});
});