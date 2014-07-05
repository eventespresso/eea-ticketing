<?php
/**
 * This file contains the module for the EE Ticketing addon
 *
 * @since 1.0.0
 * @package  EE Ticketing
 * @subpackage modules, messages
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 *
 * EE_Ticketing module.  Takes care of registering the url trigger for the special [TXN_TICKETS_URL] messages shortcode.
 *
 * @since 1.0.0
 *
 * @package		EE Ticketing
 * @subpackage	modules, messages
 * @author 		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EED_Ticketing  extends EED_Messages {

	/**
	 *  set_hooks - for hooking into EE Core, other modules, etc
	 *
	 *  @since 4.5.0
	 *
	 *  @return 	void
	 */
	public static function set_hooks() {
		self::_register_routes();
	}


	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
	}


	/**
	 * All the message triggers done by route go in here.
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	protected static function _register_routes() {
		EE_Config::register_route( __('ee-txn-tickets-url', 'event_espresso'), 'Ticketing', 'run' );
		do_action( 'AHEE__EED_Ticketing___register_routes' );
	}



	public function run( $WP ) {
		//declare vars
		$registrations = array();
		$messages = array();

		//get the params from the request
		$token = EE_Registry::instance()->REQ->is_set( 'token' ) ? EE_Registry::instance()->REQ->get('token') : '';

		//verify the needed params are present.
		if ( empty( $token ) ) {
			throw new EE_Error( __('The request for the "ee-txn-tickets-url" route has a malformed url.', 'event_espresso') );
		}

		self::_load_controller();


		$registration = EEM_Registration::instance()->get_one( array( array( 'REG_url_link' => $token ) ) );

		//valid registration?
		if ( ! $registration instanceof EE_Registration ) {
			return; //get out we need a valid registration.
		}


		//if primary registration then we grab all registrations and loop through to generate the html.  If not primary, then we just use the existing registration and throw that ticket up.

		if ( ! $registration->is_primary_registrant() ) {
			self::$_EEMSG->send_message( 'ticketing', $registration, 'html', '', 'registrant' );
		} else {
			//get all registrations for transaction
			$transaction = $registration->transaction();

			$registrations = $transaction instanceof EE_Transaction ? $transaction->registrations() : array();

			foreach ( $registrations as $registration ) {
				$message = self::$_EEMSG->send_message( 'ticketing', $registration, 'html', '', 'registrant', FALSE );
				if ( $message ) {
					$messages[] = $message;
				}
			}


			//now let's consolidate the $message objects into one message object for the actual displayed template
			$content = '';
			foreach ( $messages as $message ) {
				foreach ( $message as $msg ) {
					$content .= $msg->content;
				}
			}

			$final_msg = new stdClass();
			$final_msg->subject = sprintf( __( 'All tickets for the transaction: %d', 'event_espresso' ), $transaction->ID() );
			$final_msg->content = $content;

			//now we can trigger that message setup
			self::$_EEMSG->send_message_with_messenger_only( 'html', $final_msg );
		}

	}
}
