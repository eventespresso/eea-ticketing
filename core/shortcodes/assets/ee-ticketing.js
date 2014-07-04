jQuery(document).ready(function($) {
	var qrcode_val, dimensions;
	$('.ee-qr-code').each( function(i) {
		qrcode_val = $('.ee-qrcode-reg_url_link', this).text();
		dimensions = parseInt( $('.ee-qrcode-dimensions', this).text(), 10 );
		$(this).qrcode( {width: dimensions, height: dimensions, text: qrcode_val } );
	});
});
