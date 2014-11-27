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
			'Ticketing',
			array(
				'version' => EE_TICKETING_VERSION,
				'min_core_version' => '4.5.0.dev.000',
				'main_file_path' => EE_TICKETING_PLUGIN_FILE,
				'autoloader_paths' => array(
					'EE_Ticketing' 						=> EE_TICKETING_PATH . 'EE_Ticketing.class.php'
				),
				'module_paths' => array( EE_TICKETING_PATH . 'EED_Ticketing.module.php' ),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'eea-ticketing',
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
					),
				'message_types' => array(
					'ticketing' => self::register_ticketing_message_type()
					)
			)
		);

		//register new shortcodes with existing libraries.
		add_filter( 'FHEE__EE_Shortcodes__shortcodes', array( 'EE_Ticketing', 'register_new_shortcodes' ), 10, 2 );
		add_filter( 'FHEE__EE_Shortcodes__parser_after', array( 'EE_Ticketing', 'register_new_shortcode_parsers'), 10, 5 );
		add_filter( 'FHEE__EE_Messages_Validator__get_specific_shortcode_excludes', array( 'EE_Ticketing', 'exclude_new_shortcodes' ), 10, 3 );

		self::_add_template_pack_filters();
	}


	/**
	* Takes care of adding all filters for template packs this message type connects with.
	*
	* @since 1.0.0
	*
	* @return void.
	*/
	protected static function _add_template_pack_filters() {
		add_filter( 'FHEE__EE_Messages_Template_Pack_Default__get_supports', array( 'EE_Ticketing', 'register_supports_for_default_template_pack' ), 10  );
		add_filter( 'FHEE__EE_Template_Pack___get_specific_template__filtered_base_path', array( 'EE_Ticketing', 'register_base_path_for_ticketing_templates' ), 10, 6 );
		add_filter( 'FHEE__EE_Messages_Template_Pack__get_variation__base_path_or_url', array( 'EE_Ticketing', 'get_ticketing_css_path_or_url' ), 10, 8 );
		add_filter( 'FHEE__EE_Messages_Template_Pack__get_variation__base_path', array( 'EE_Ticketing', 'get_ticketing_css_path_or_url' ), 10, 8 );
	}



	/**
	 * Adds the ticketing message type to the supports array for the default template pack.
	 *
	 * @since %VER%
	 *
	 * @param array  $supports Original "supports" value for default template pack.
	 * @return array  new supports value.
	 */
	public static function register_supports_for_default_template_pack( $supports ) {
		$supports['html'][] = 'ticketing';
		return $supports;
	}



	/**
	 * This registers the correct base path for the ticketing default templates.
	 *
	 * @param string $base_path    The original base path for templates.
	 * @param EE_messenger $messenger
	 * @param EE_message_type $message_type
	 * @param string $field        The field requesting a template.
	 * @param string $context      The context requesting a template.
	 * @param EE_Messages_Template_Pack $template_pack
	 *
	 * @return string The new base path.
	 */
	public static function register_base_path_for_ticketing_templates( $base_path, $messenger, $message_type, $field, $context, $template_pack ) {
		if ( ! $template_pack instanceof EE_Messages_Template_Pack_Default || ! $message_type instanceof EE_Ticketing_message_type ) {
			return $base_path; //we're only setting up default templates for the default pack or for ticketing message type.
		}

		return EE_TICKETING_PATH . 'core/messages/templates/';
	}




	/**
	 * This is the callback for the FHEE__EE_Messages_Template_Pack__get_variation__base_path_or_url filter.
	 * Used by ticketing addon to ensure it's css is used for default ticket templates.
	 *
	 * @param string $base_path_or_url       The original incoming base url or path
	 * @param string $messenger      The slug of the messenger the template is being generated for.
	 * @param string $message_type The slug of the message type the template is being generated for.
	 * @param string $type             The "type" of css being requested.
	 * @param string $variation      The variation being requested.
	 * @param string $file_extension What file extension is expected for the variation file.
	 * @param bool $url whether a url or path is being requested.
	 * @param EE_Messages_Template_Pack $template_pack
	 *
	 * @return string new base path or url
	 */
	public static function get_ticketing_css_path_or_url( $base_path_or_url, $messenger, $message_type, $type, $variation, $url, $file_extension, $template_pack ) {
		if ( ! $template_pack instanceof EE_Messages_Template_Pack_Default || $messenger != 'html' ) {
			return $base_path_or_url;
		}

		return  self::_get_ticketing_path_or_url( $url );
	}




	/**
	 * Simply returns the url or path  for the ticketing templates
	 *
	 * @since 1.0.0
	 *
	 * @param bool $url true = return url, false = return path
	 *
	 * @return string
	 */
	private static function _get_ticketing_path_or_url( $url = FALSE ) {
		return $url ? EE_TICKETING_URL . 'core/messages/templates/' : EE_TICKETING_PATH . 'core/messages/templates/';
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
		return $setup_args;
	}




	public static function exclude_new_shortcodes( $shortcode_excludes, $context, EE_Messages_Validator $validator ) {
		//we exclude ALL qrcode and barcode shortcodes from non ticketing message types.
		if ( ! $validator instanceof EE_Messages_Html_Ticketing_Validator ) {
			$fields = array_keys($validator->get_validators());
			foreach ( $fields as $field ) {
				$shortcode_excludes[$field][] = '[QRCODE_*]';
				$shortcode_excludes[$field][] = '[BARCODE_*]';
			}
		}

		return $shortcode_excludes;
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
			$shortcodes['[QRCODE_*]'] = __('This is a shortcode used for generating a qrcode for the registration.  The only thing stored via this code is the unique reg_url_link code attached to a registration record.', 'event_espresso' ) . '<p>' . __('Note: there are a number of different parameters you can use for the qrcode generated.  We have the defaults at the recommended settings, however, you can change these:', 'event_espresso') . '<ul>' .
					'<li><strong>d</strong>:' . __('You can add a extra param for setting the dimensions of the qr code using "d=20" format.  So [QRCODE_* d=40] will parse to a qrcode that is 40 pixels wide by 40 pixels high.', 'event_espresso') . '</li>' .
					'<li><strong>color</strong>:' . __('Use a hexadecimal color for the qr code color.  So you can do [QRCODE_* color=#f00] to have the code printed in red.', 'event_espresso' ) . '</li>' .
					'<li><strong>mode</strong>:' . __('This parameter is used to indicate what mode the code is generated in.  0 = normal, 1 = label strip, 2 = label box.  Use in the format [QRCODE_* mode=2].', 'event_espresso') . '</li>' .
					'<li><strong>label</strong>:' . __('This allows you to set a custom label that will appear over the code. [QRCODE_* label="My QR Code"]', 'event_espresso' ) . '</li>' .
					'</ul></p>';
			$shortcodes['[GRAVATAR_*]'] = __('This shortcode will grab the email address attached to the registration and use that to attempt to grab a gravatar image.  If none is found then whatever is set in your WordPress settings for Default Avatar will be used. You can include what you want the dimensions of the gravatar to be by including params in the folowing format: "d=40".  So [GRAVATAR_* d=40] will parse to a gravatar image that is 40 pixels wide by 40 pixels high.', 'event_espresso');
			$shortcodes['[BARCODE_*]'] = __('This shortcode is used to generate a custom barcode for the ticket instead of a qrcode.  There are a number of different options for the barcode:') . '<p></ul>' .
				'<li><strong>w</strong>:' . __('Used to set the width (default is 2). [BARCODE_* w=20]', 'event_espresso') . '</li>' .
				'<li><strong>h</strong>:' . __('Used to set the height (default is 70). [BARCODE_* h=50]', 'event_espresso') . '</li>' .
				'<li><strong>type</strong>:' . __('Used to set the barcode type (default is code93). [BARCODE_* type=code93].  There are 4 different types you can choose from:', 'event_espresso') . '<ul>' .
					'<li>code39</li>' .
					'<li>code93</li>' .
					'<li>code128</li>' .
					'<li>datamatrix</li>' .
					'</li></ul>' .
				'<li><strong>bgcolor</strong>:' . __('Used to set the background color of the barcode (default is #FFFFFF [white] ). [BARCODE_* bgcolor=#FFFFFF]', 'event_espresso') . '</li>' .
				'<li><strong>color</strong>:' . __('Used to set the foreground color of the barcode (default is #000000 [black] ). [BARCODE_* color=#FFFFFF]', 'event_espresso') . '</li>' .
				'<li><strong>fsize</strong>:' . __('Used to set the fontsize for the barcode (default is 10). [BARCODE_* fsize=10]', 'event_espresso') . '</li>' .
				'<li><strong>output_type</strong>:' .
					__('Used to set the output type for the generated barcode (default is svg).  Can be either svg, canvas, bmp, or css. <em>Note: Some output types don\'t print well depending on the browser.  Make sure you verify printability.</em> [BARC0DE_* output_type=bmp]', 'event_espresso' ). '</li>' .
				'<li><strong>generate_for</strong>:' . __('This allows you to set what gets used to generate the barcode. When the barcode is scanned this is the value that will be returned. There are two options: "long_code", which is the equivalent to <em>reg_url_lnk</em> value for the registration; or "short_code", which is the equivalent to the <em>reg_code</em> value for the registration.  The default is "short_code". <code>[BARCODE_* generate_for=short_code]</code>', 'event_espresso') .
				'<ul></p>';
		}

		if ( $lib instanceof EE_Attendee_Shortcodes ) {
			$shortcodes['[TICKET_URL]'] = __('This shortcode generates the url for accessing the ticket.', 'event_espresso');
		}

		if ( $lib instanceof EE_Recipient_Details_Shortcodes ) {
			$shortcodes['[RECIPIENT_TICKET_URL]'] = __('This shortcode generates the url for the ticket attached to the registration record for the recipient of a message.', 'event_espresso' );
		}

		if ( $lib instanceof EE_Transaction_Shortcodes ) {
			$shortcodes['[TXN_TICKETS_URL]'] = __('This shortcode generates the url for all tickets in a transaction.', 'event_espresso');
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

			//verify required data present
			if ( ! $ticket instanceof EE_Ticket || ! $registration instanceof EE_Registration  ) {
				return $parsed;
			}

			//require the shortcode file if necessary
			if ( ! function_exists( 'shortcode_parse_atts' ) ) {
				require_once( ABSPATH . WPINC . '/shortcodes.php');
			}

			//see if there are any atts on the shortcode.
			$shortcode_to_parse = str_replace( '[', '', str_replace( ']', '', $shortcode ) );
			$attrs = shortcode_parse_atts( $shortcode_to_parse );

			if ( strpos( $shortcode, '[QRCODE_*' ) !== FALSE  ) {
				//set custom dimension if present or default if not.
				$d = isset( $attrs['d'] ) ? intval( $attrs['d'] ) : 110;

				//color?
				$color = isset( $attrs['color'] ) ? $attrs['color'] : '#000';

				//mode?
				$mode = isset( $attrs['mode'] ) ? intval( $attrs['mode'] ) : 0;
				$mode = $mode > 2 || $mode < 0 ? 0 : $mode;

				//label?
				$label = isset( $attrs['label'] ) ? $attrs['label'] : '';

				//all the parsed qr code really does is setup some hidden values for the qrcode js to do its thing.
				$parsed = '<div class="ee-qr-code"><span class="ee-qrcode-dimensions" style="display:none;">' . $d . '</span>';
				$parsed .= '<span class="ee-qrcode-reg_url_link" style="display:none;">' . $registration->reg_url_link() . '</span>';
				$parsed .= '<span class="ee-qrcode-color" style="display:none;">' . $color . '</span>';
				$parsed .= '<span class="ee-qrcode-mode" style="display:none;">' . $mode . '</span>';
				$parsed .= '<span class="ee-qrcode-label" style="display:none;">' . $label . '</span>';
				$parsed .= '</div>';

			} elseif ( strpos( $shortcode, '[GRAVATAR_*' ) !== FALSE ) {
				$attendee = $aee->att_obj;
				$email = $attendee instanceof EE_Attendee  ? $attendee->email() : '';
				$size = isset( $attrs['d'] ) ? intval( $attrs['d'] ) : 110;
				$parsed = get_avatar( $email, $size );
			} elseif ( strpos( $shortcode, '[BARCODE_*' ) !== FALSE ) {

				//attributes
				$width = isset( $attrs['w'] ) ? (int) $attrs['w'] : 2;
				$height = isset( $attrs['h'] ) ? (int) $attrs['h'] : 70;
				$type = isset( $attrs['type'] ) ? $attrs['type'] : 'code93';
				$bgcolor = isset( $attrs['bgcolor'] ) ? $attrs['bgcolor'] : '#ffffff';
				$color = isset( $attrs['color'] ) ? $attrs['color'] : '#000000';
				$fsize = isset( $attrs['fsize'] ) ? (int) $attrs['fsize'] : 10;
				$code_value = isset( $attrs['generate_for'] ) ? trim( $attrs['generate_for'] ) : 'short_code';
				$reg_code = $code_value == 'long_code' ? $registration->reg_url_link() : $registration->reg_code();
				if ( isset( $attrs['output_type'] ) ) {
					$valid_output_types = array( 'css', 'svg', 'canvas', 'bmp' );
					$output_type = in_array( $attrs['output_type'], $valid_output_types ) ? $attrs['output_type'] : 'svg';
				} else {
					$output_type = 'svg';
				}

				//setup the barcode params in the dom
				$parsed = '<div class="ee-barcode"><span class="ee-barcode-width" style="display:none;">' . $width . '</span>';
				$parsed .= '<span class="ee-barcode-reg_url_link" style="display:none;">' . $reg_code . '</span>';
				$parsed .= '<span class="ee-barcode-color" style="display:none;">' . $color . '</span>';
				$parsed .= '<span class="ee-barcode-type" style="display:none;">' . $type . '</span>';
				$parsed .= '<span class="ee-barcode-height" style="display:none;">' . $height . '</span>';
				$parsed .= '<span class="ee-barcode-bgcolor" style="display:none;">' . $bgcolor . '</span>';
				$parsed .= '<span class="ee-barcode-fsize" style="display:none;">' . $fsize . '</span>';
				$parsed .= '<span class="ee-barcode-output-type" style="display:none;">' . $output_type . '</span>';
				$parsed .= '</div>';

			}
		} elseif ( $lib instanceof EE_Attendee_Shortcodes ) {
			if ( $shortcode == '[TICKET_URL]' ) {
				$extra = !empty( $extra_data ) && $extra_data['data'] instanceof EE_Messages_Addressee ?  $extra_data['data'] : NULL;

				//incoming object should only be a registration object.
				$registration = ! $data instanceof EE_Registration ? NULL : $data;

				if ( empty( $registration ) ) {
					return $parsed;
				}

				$parsed = self::_get_ticket_url( $registration );
			}
		} elseif ( $lib instanceof EE_Recipient_Details_Shortcodes ) {
			if ( $shortcode == '[RECIPIENT_TICKET_URL]' ) {
				$recipient = $lib->get_recipient();

				if ( ! $recipient instanceof EE_Messages_Addressee )
					return '';

				$registration = $recipient->reg_obj;

				if ( ! $registration instanceof EE_Registration ) {
					return $parsed;
				}

				$parsed = self::_get_ticket_url( $registration );
			}


		} elseif ($lib instanceof EE_Transaction_Shortcodes ) {
			if ( ! $data->txn instanceof EE_Transaction ) {
				return $parsed; //get out because don't have what we need!
			}

			if ( $shortcode == '[TXN_TICKETS_URL]' ) {
				$transaction = $data->txn;
				$reg = $data->reg_obj instanceof EE_Registration ? $data->reg_obj : $transaction->primary_registration();

				$reg_url_link = $reg instanceof EE_Registration ? $reg->reg_url_link() : 'http://dummyurlforpreview.com';

				$query_args = array(
					'ee' => 'ee-txn-tickets-url',
					'token' => $reg_url_link
					);
				$parsed = add_query_arg( $query_args, get_site_url() );
			}
		}
		return $parsed;
	}



	private static function _get_ticket_url( EE_Registration $registration ) {
		//we need to get the correct template ID for the given event
		$event = $registration->event();

		//get the assigned ticket template for this event
		$mtp = EEM_Message_Template_Group::instance()->get_one( array( array( 'Event.EVT_ID' => $event->ID(), 'MTP_message_type' => 'ticketing' ) ) );

		//if no $mtp then that means an existing event that hasn't been saved yet with the templates for the global ticketing template.  So let's just grab the global.
		$mtp = $mtp instanceof EE_Message_Template_Group ? $mtp : EEM_Message_Template_Group::instance()->get_one( array( array( 'MTP_is_global' => 1, 'MTP_message_type' => 'ticketing' ) ) );

		$query_args = array(
			'ee' => 'msg_url_trigger',
			'snd_msgr' => 'html',
			'gen_msgr' => 'html',
			'message_type' => 'ticketing',
			'context' => 'registrant',
			'token' => $registration->reg_url_link(),
			'GRP_ID' => $mtp->ID(),
			'id' => 0
			);
		return add_query_arg( $query_args, get_site_url() );
	}

}
// End of file EE_Ticketing.class.php
// Location: wp-content/plugins/espresso-new-addon/EE_Ticketing.class.php
