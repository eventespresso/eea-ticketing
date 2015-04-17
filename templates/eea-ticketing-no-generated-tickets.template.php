<!DOCTYPE html>
<html>
<head>
	<title><?php _e('No Tickets available', 'event_espresso'); ?></title>
	<style media="screen" type="text/css">
		.error-container {
			margin:auto;
			width: 80%;
			margin-top: 300px;
			padding: 15px 10px;
			border: 2px solid #666;
			-webkit-border-radius: 4px ;
			-moz-border-radius: 4px;
			-ms-border-radius: 4px;
			-o-border-radius: 4px;
			border-radius 4px;
			font: 1em "Arial", sans-serif;
		}
		p {
			margin: 0;
			padding: 0;
		}

	</style>
</head>
<body>
	<div class="error-container">
		<p><?php printf( __('Hello,%s  Your ticket(s) are not available because your registration(s) are not
approved. If you think this is incorrect, please contact the site
administrator.', 'event_espresso'), '<br>' ); ?></p>
	</div>
</body>
</html>
