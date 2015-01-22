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
		//add trigger for ticket notice EE4.6+
		add_action( 'AHEE__EE_Registration_Processor__trigger_registration_update_notifications', array( 'EED_Ticketing', 'maybe_ticket_notice' ), 10, 2 );

		//add trigger  for ticket notice EE4.5-
		if ( version_compare( '4.6.0.rc.000', EVENT_ESPRESSO_VERSION ) !== 1 ) {
			add_action( 'AHEE__EE_Transaction__finalize__all_transaction', array( 'EED_Ticketing', 'maybe_ticket_notice_old' ), 10, 3 );
		}

		add_action( 'process_resend_ticket_notice', array( 'EED_Ticketing', 'process_resend_ticket_notice' ) );

		self::_register_routes();
	}


	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
		add_action( 'AHEE__EE_Registration_Processor__trigger_registration_update_notifications', array( 'EED_Ticketing', 'maybe_ticket_notice' ), 10, 2 );
		add_action( 'process_resend_ticket_notice', array( 'EED_Ticketing', 'process_resend_ticket_notice' ) );

		//add trigger  for ticket notice EE4.5-
		if ( version_compare( '4.6.0.rc.000', EVENT_ESPRESSO_VERSION ) !== 1 ) {
			add_action( 'AHEE__EE_Transaction__finalize__all_transaction', array( 'EED_Ticketing', 'maybe_ticket_notice_old' ), 10, 3 );
		}
	}




	/**
	 * This is the trigger for the ticket notice message type.  Decides whether to send a ticket
	 * notice message or not.
	 * Note: this method is only called when Event Espresso version 4.6.0+ is in use.
	 *
	 * @param EE_Registration $registration
	 * @param array           $extra_details extra details coming from the transaction
	 *
	 * @return void
	 */
	public static function maybe_ticket_notice( EE_Registration $registration, $extra_details = array()  ) {
		$do_send = self::_verify_registration_notification_send( $registration, $extra_details );
		if ( ! $do_send ) {
			//no messages please
			return;
		}


		EE_Registry::instance()->load_helper( 'MSG_Template' );
		if ( EEH_MSG_Template::is_mt_active( 'ticket_notice' ) && $registration->status_ID() == EEM_Registration::status_id_approved ) {
			self::_load_controller();
			self::$_EEMSG->send_message(
				'ticket_notice',
				array( $registration->transaction(), NULL )
				);
		}
		return;
	}



	/**
	 * Trigger for Ticket Notice messages in pre 4.6.0 versions of EE core. (this method will be
	 * deprecated at some point in the future when ticketing no longer supports 4.5.0.
	 * Note that what registration message type is sent depends on what the reg status is for
	 * the registrations on the incoming transaction.
	 * @param  EE_Transaction $transaction
	 * @param  array $reg_msg
	 * @param  bool $from_admin
	 * @return void
	 */
	public static function maybe_ticket_notice_old( EE_Transaction $transaction, $reg_msg, $from_admin ) {

		//for now we're ONLY doing this from frontend UNLESS we have the toggle to send.
		if ( $from_admin ) {
			$messages_toggle = !empty( $_REQUEST['txn_reg_status_change']['send_notifications'] ) && $_REQUEST['txn_reg_status_change']['send_notifications'] ? TRUE : FALSE;
			if ( ! $messages_toggle )
				return; //no messages sent please.
		}
		//next let's only send out notifications if a registration was created OR if the registration status was updated to approved
		if ( ! $reg_msg['new_reg'] && ! $reg_msg['to_approved'] )
			return;

		$data = array( $transaction, NULL );

		//let's get the first related reg on the transaction since we can use its status to determine what message type gets sent.
		$registration = $transaction->get_first_related('Registration');

		self::_load_controller();

		$active_mts = self::$_EEMSG->get_active_message_types();

		if ( in_array( 'ticket_notice', $active_mts ) && $registration->status_ID() == EEM_Registration::status_id_approved )
			self::$_EEMSG->send_message( 'ticket_notice', $data );

		return; //if we get here then there is no active message type for this status.
	}



	public static function process_resend_ticket_notice( EE_Admin_Page $admin_page ) {
		$success = true;
		if ( ! isset( $_REQUEST['_REG_ID'] ) ) {
			EE_Error::add_error( __('Something went wrong because there was no registration ID in the request.  Unable to resend the ticket notice.', 'event_espresso' ), __FILE__, __FUNCTION__, __LINE__ );
			$success = false;
		}

		//get reg_object from reg_id
		$reg = EE_Registry::instance()->load_model( 'Registration' )->get_one_by_ID( $_REQUEST['_REG_ID'] );

		//if no reg object then error
		if ( ! $reg instanceof EE_Registration ) {
			EE_Error::add_error( sprintf( __('Unable to retrieve a registration object for the given reg id (%s)', 'event_espresso'), $req_data['_REG_ID'] ), __FILE__, __FUNCTION__, __LINE__ );
			$success = false;
		}

		if ( $success ) {
			self::_load_controller();
			$active_mts = self::$_EEMSG->get_active_message_types();
			if ( ! in_array( 'ticket_notice', $active_mts ) ) {
				$success = false;
				EE_Error::add_error( sprintf( __('Cannot resend the ticket notice for this registration because the corresponding message type is not active.  If you wish to send messages for this message type then please activate it by %sgoing here%s.', 'event_espresso'), '<a href="' . admin_url('admin.php?page=espresso_messages&action=settings') . '">', '</a>' ), __FILE__, __FUNCTION__, __LINE__ );
			}

			if ( $success ) {
				$success = self::$_EEMSG->send_message( 'ticket_notice', $reg );
			}

		}

		if ( $success ) {
			EE_Error::overwrite_success();
			EE_Error::add_success( __( 'The message for this registration has been re-sent', 'event_espresso' ) );
		}

		//DONT' use the below code... just for reference on what to redirect to after things are done.
		$query_args = isset($_REQUEST['redirect_to'] ) ? array('action' => $_REQUEST['redirect_to'], '_REG_ID' => $_REQUEST['_REG_ID'] ) : array(
			'action' => 'default'
			);
		$admin_page->redirect_after_action(FALSE, '', '', $query_args, TRUE );
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
			$final_msg = new stdClass();
			foreach ( $messages as $message ) {
				foreach ( $message as $msg ) {
					$final_msg->template_pack = ! empty( $msg->template_pack ) ? $msg->template_pack : null;
					$final_msg->vairation = ! empty( $msg->variation ) ? $msg->variation : null;
					$content .= $msg->content;
				}
			}

			$final_msg->subject = sprintf( __( 'All tickets for the transaction: %d', 'event_espresso' ), $transaction->ID() );
			$final_msg->content = $content;
			$final_msg->template_pack =  ! $final_msg->template_pack instanceof EE_Messages_Template_Pack ? EED_Messages::get_template_pack( 'default' ) : $final_msg->template_pack;
			$final_msg->variation = empty( $final_msg->variation ) ? 'default' : $final_msg->variation;

			//now we can trigger that message setup
			self::$_EEMSG->send_message_with_messenger_only( 'html', 'ticketing', $final_msg );
		}

	}
}
