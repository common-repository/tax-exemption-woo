jQuery( document ).ready(function() {
	jQuery( '#tefw_exempt, #tefw_exempt_expiration' ).on( 'change', function() {
		jQuery( 'body' ).trigger( 'update_checkout' );
	});
});