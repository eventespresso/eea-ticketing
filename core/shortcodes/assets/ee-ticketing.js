jQuery(document).ready(function($) {
	//qrcodes
	$('.ee-qr-code').each( function(i) {
		var data = $( this ).data();
		var config = {
			size: parseInt( data.dimensions, 10 ),
			text: data.content,
			fill: data.color,
			mode: parseInt( data.mode, 10),
			label: data.label
		};
		$(this).qrcode( config );
	});

	//barcodes
	var barcode_val, bc_settings={}, bc_type, container;
	$('.ee-barcode').each( function(i) {
		barcode_val = $('.ee-barcode-reg_url_link', this ).text();
		container = $(this);
		bc_settings.barWidth = parseInt( $('.ee-barcode-width', this ).text(), 10 );
		bc_settings.barHeight = parseInt( $('.ee-barcode-height', this ).text(), 10 );
		bc_settings.color = $('.ee-barcode-color', this ).text();
		bc_settings.bgColor = $('.ee-barcode-bgcolor', this ).text();
		bc_settings.fontSize = parseInt( $('.ee-barcode-fsize', this ).text(), 10 );
		bc_settings.output= $('.ee-barcode-output-type', this ).text();
		bc_type = $('.ee-barcode-type', this ).text();
		container.barcode( { code: barcode_val }, bc_type, bc_settings );
	});

	$('.print_button_div').on('click', '.print_button', function(e) {
		e.preventDefault();
		e.stopPropagation();
		window.print();
		return false;
	});
});
