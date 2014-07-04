<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/**
 * ------------------------------------------------------------------------
 *
 * Class  EE_Ticketing
 *
 * @package			EE Ticketing
 * @subpackage		core
 * @author			Darren Ethier
 * @since		 	1.0.0
 *
 * ------------------------------------------------------------------------
 */
// define the plugin directory path and URL
define( 'EE_TICKETING_PATH', plugin_dir_path( __FILE__ ));
define( 'EE_TICKETING_URL', plugin_dir_url( __FILE__ ));
Class  EE_Ticketing extends EE_Addon {

	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'EE_Ticketing',
			array(
				'version' 					=> EE_TICKETING_VERSION,
				'min_core_version' => '4.3.0',
				'main_file_path' 				=> EE_TICKETING_PLUGIN_FILE,
				'autoloader_paths' => array(
					'EE_Ticketing' 						=> EE_TICKETING_PATH . 'EE_Ticketing.class.php'
				),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'ee-addon-ticketing',
					'plugin_basename' => EE_TICKETING_PLUGIN_FILE,
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
					)
			)
		);

		add_action( 'EE_Brewing_Regular___messages_caf', array( 'EE_Ticketing', 'register_ticketing_message_type' ) );

		//register new shortcodes with existing libraries.
		add_filter( 'FHEE__EE_Shortcodes__shortcodes', array( 'EE_Ticketing', 'register_new_shortcodes' ), 10, 2 );
		add_filter( 'FHEE__EE_Shortcodes__parser_after', array( 'EE_Ticketing', 'register_new_shortcode_parsers'), 10, 5 );
	}



	public static function register_ticketing_message_type() {
		$setup_args = array(
			'mtfilename' => 'EE_Ticketing_message_type.class.php',
			'autoloadpaths' => array(
				EE_TICKETING_PATH . 'core/messages/'
				),
			'messengers_to_activate_with' => array( 'html' ),
			'messengers_to_validate_with' => array( 'html' )
			);
		EE_Register_Message_Type::register( 'ticketing', $setup_args );
	}




	/**
	 * Callback for FHEE__EE_Shortcodes__shortcodes
	 *
	 * @since 1.0.0
	 *
	 * @param array         $shortcodes The existing shortcodes in this library
	 * @param EE_Shortcodes $lib
	 *
	 * @return array          new array of shortcodes
	 */
	public static function register_new_shortcodes( $shortcodes, EE_Shortcodes $lib ) {
		//shortcodes to add to EE_Ticket_Shortcodes
		if ( $lib instanceof EE_Ticket_Shortcodes ) {
			$shortcodes['[QRCODE_*]'] = __('This is a shortcode used for generating a qrcode for the registration.  The only thing stored via this code is the unique reg_url_link code attached to a registration record.  Note: you can add a extra param for setting the dimensions of the qr code using "d=20" format.  So [QRCODE_* d=40] will parse to a qrcode that is 40 pixels wide by 40 pixels high.');
		}
		return $shortcodes;
	}




	/**
	 * Call back for the FHEE__EE_Shortcodes__parser_after filter.
	 * This contains the logic for parsing the new shortcodes introduced by this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param string        $parsed       The current parsed template string.
	 * @param string        $shortcode  The incoming shortcode being setup for parsing.
	 * @param array|obj   $data           Depending on the shortcode parser the filter is called in, this will represent either an array of data objects or a specific data object.
	 * @param array|obj   $extra_data Depending on the shortcode parser the filter is called in, this will either represent an array with an array of templates being parsed, and a EE_Addressee_Data object OR just an EE_Addresee_Data object.
	 * @param EE_Shortcodes $lib
	 *
	 * @return string        The parsed string
	 */
	public static function register_new_shortcode_parsers( $parsed, $shortcode, $data, $extra_data, EE_Shortcodes $lib ) {
		//only do this parsing on the EE_Ticket_Shortcodes parser
		if ( $lib instanceof EE_Ticket_Shortcodes ) {
			$ticket = $lib->get_ticket_set();
			$aee = $data instanceof EE_Messages_Addressee ? $data : NULL;
			$aee = $extra_data instanceof EE_Messages_Addressee ? $extra_data : $aee;
			$registration = $aee instanceof EE_Messages_Addressee && $aee->reg_obj instanceof EE_Registration ? $aee->reg_obj : NULL;
			//qrcode check
			if ( strpos( $shortcode, '[QRCODE_*' ) === FALSE || ! $ticket instanceof EE_Ticket ) {
				return $parsed;
			}

			//let's see if there are any atts on the shortcode.
			//require the shortcode file if necessary
			if ( ! function_exists( 'shortcode_parse_atts' ) ) {
				require_once( ABSPATH . WPINC . '/shortcodes.php');
			}

			$shortcode_to_parse = str_replace( '[', '', str_replace( ']', '', $shortcode ) );
			$attrs = shortcode_parse_atts( $shortcode_to_parse );

			//set custom dimension if present or default if not.
			$d = isset( $attrs['d'] ) ? intval( $attrs['d'] ) : 135;

			//all the parsed qr code really does is setup some hidden values for the qrcode js to do its thing.
			$parsed = '<div class="ee-qr-code"><span class="ee-qrcode-dimensions" style="display:none;">' . $d . '</span>';
			$parsed .= '<span class="ee-qrcode-reg_url_link" style="display:none;">' . $registration->reg_url_link() . '</span>';
			$parsed .= '</div>';
		}
		return $parsed;
	}

}
// End of file EE_Ticketing.class.php
// Location: wp-content/plugins/espresso-new-addon/EE_Ticketing.class.php
