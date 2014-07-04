jQuery(document).ready(function($) {
	var qrcode_val, dimensions, qrcolor, qrmode, qrlabel;
	$('.ee-qr-code').each( function(i) {
		qrcode_val = $('.ee-qrcode-reg_url_link', this).text();
		dimensions = parseInt( $('.ee-qrcode-dimensions', this).text(), 10 );
		qrcolor = $('.ee-qrcode-color', this).text();
		qrmode = parseInt( $('.ee-qrcode-mode', this).text(), 10);
		qrlabel = $('.ee-qrcode-label', this).text();
		$(this).qrcode( {width: dimensions, height: dimensions, text: qrcode_val, fill: qrcolor, mode: qrmode, label : qrlabel } );
	});

	$('.print_button_div').on('click', '.print_button', function(e) {
		e.preventDefault();
		e.stopPropagation();
		window.print();
		return false;
	});
});
