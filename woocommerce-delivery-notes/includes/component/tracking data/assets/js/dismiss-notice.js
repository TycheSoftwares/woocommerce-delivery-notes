/**
 * This function allows to dismiss the notices which are shown from the plugin.
 *
 * @namespace orddd_notice_dismissible
 * @since 6.8
 */
// Make notices dismissible
jQuery(document).ready( function() {
	jQuery( '.notice.is-dismissible' ).each( function() {
		var $this = jQuery( this ),
			$button = jQuery( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
			btnText = commonL10n.dismiss || '';
		
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
					var data = {
						action: ts_dismiss_notice.ts_prefix_of_plugin + "_admin_notices"
					};
					var admin_url = ts_dismiss_notice.ts_admin_url;
					
					jQuery.post( admin_url , data, function( response ) {
					});
				});
			});
		});
	});
});