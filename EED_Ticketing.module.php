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
	 *  @since 1.0.0
	 *
	 *  @return 	void
	 */
	public static function set_hooks() {
		//add trigger for ticket notice
		add_action( 'AHEE__EE_Registration_Processor__trigger_registration_update_notifications', array( 'EED_Ticketing', 'maybe_ticket_notice' ), 10, 2 );
		add_action( 'AHEE__EE_Ticketing__resend_ticket_notice', array( 'EED_Ticketing', 'process_resend_ticket_notice' ), 10, 2 );

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
		add_action( 'AHEE__EE_Ticketing__resend_ticket_notice', array( 'EED_Ticketing', 'process_resend_ticket_notice' ), 10, 2 );
		add_action( 'AHEE__EE_Admin_Page___process_resend_registration', array( 'EED_Ticketing', 'process_resend_ticket_notice_from_registration_trigger' ), 10, 2);
	}


	/**
	 * Simply returns whether Ticketing should use the new 4.9+ messages system for sending or pre 4.9 messages system.
	 * @since 1.0.4.rc.005
	 * @return bool
	 */
	protected static function _use_new_system() {
		return property_exists( 'EED_Messages', '_message_resource_manager' );
	}




	/**
	 * This is the trigger for the ticket notice message type.  Decides whether to send a ticket
	 * notice message or not.
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


		//check to determine what method used to setup the trigger
		//I'm using a check for a method that only existed in a certain core branch being used for necessary
		//MER dependencies, that also contains changes in messages system that do not exist in the master
		//branch at the time of this work.
		$registration_processor = EE_Registry::instance()->load_class( 'Registration_Processor' );
		$data = method_exists( $registration_processor, 'generate_ONE_registration_from_line_item' ) ? array( $registration->transaction(), NULL, EEM_Registration::status_id_approved ) : array( $registration->transaction(), NULL );

		//if we're not in the MER branch then we also consider the status of the  primary registration on
		//whether to continue or not.
		if ( ! method_exists( $registration_processor, 'generate_ONE_registration_from_line_item' ) && $registration->status_ID() != EEM_Registration::status_id_approved ) {
			return;
		}

		EE_Registry::instance()->load_helper( 'MSG_Template' );
		if ( EEH_MSG_Template::is_mt_active( 'ticket_notice' ) ) {
			self::_load_controller();
			if ( self::_use_new_system() ) {
				try {
					$messages_to_generate = self::$_MSG_PROCESSOR->setup_mtgs_for_all_active_messengers( 'ticket_notice', $data );
					//batch queue and initiate_request
					self::$_MSG_PROCESSOR->batch_queue_for_generation_and_persist( $messages_to_generate );
					self::$_MSG_PROCESSOR->get_queue()->initiate_request_by_priority();
				} catch( EE_Error $e ) {
					EE_Error::add_error( $e->getMessage(), __FILE__, __FUNCTION__, __LINE__ );
				}
			} else {
				self::$_EEMSG->send_message(
					'ticket_notice',
					$data
				);
			}
		}
		return;
	}


	/**
	 * Callback for AHEE__EE_Admin_Page___process_resend_registration action hook
	 *
	 * @param bool  $success This indicates whether prior processing was successful (true) or not (false).
	 * @param array $request The request data at time of execution.
	 * @return bool Whether successful or not.
	 */
	public static function process_resend_ticket_notice_from_registration_trigger( $success, $request ) {
		if (
			$success
			&& isset( $request['action'] )
			&& (
				$request['action'] === 'approve_and_notify_registration'
				|| $request['action'] === 'change_reg_status'
			)
		) {
			$original_request = $_REQUEST;
			$reg_ids = isset( $request['_REG_ID'] ) ? $request['_REG_ID'] : null;
			$reg_ids = is_array( $reg_ids ) ? $reg_ids : array( $reg_ids );
			foreach ( $reg_ids as $reg_id ) {
				$_REQUEST['_REG_ID'] = $reg_id;
				self::process_resend_ticket_notice( null, false );
			}
			//restore $_REQUEST to original for any other plugins hooking in later.
			$_REQUEST = $original_request;
		}
		return $success;
	}

	public static function process_resend_ticket_notice( $admin_page, $redirect = true ) {
		$success = true;
		if ( ! isset( $_REQUEST['_REG_ID'] ) ) {
			EE_Error::add_error( __( 'Something went wrong because there was no registration ID in the request.  Unable to resend the ticket notice.', 'event_espresso' ), __FILE__, __FUNCTION__, __LINE__ );
			$success = false;
		}

		//get reg_object from reg_id
		$reg = EE_Registry::instance()->load_model( 'Registration' )->get_one_by_ID( $_REQUEST['_REG_ID'] );

		//if no reg object then error.
		if ( ! $reg instanceof EE_Registration ) {
			EE_Error::add_error( sprintf( __( 'Unable to retrieve a registration object for the given reg id (%s)', 'event_espresso' ), absint( $_REQUEST['_REG_ID'] ) ), __FILE__, __FUNCTION__, __LINE__ );
			$success = false;
		}

		//if reg object is not approved then let's just skip now to avoid processing.
		if ( ! $reg->is_approved() ) {
			$success = false;
		}

		if ( $success ) {
			self::_load_controller();
			$active_mts = self::_use_new_system() ? self::$_message_resource_manager->list_of_active_message_types() : self::$_EEMSG->get_active_message_types();
			if ( ! in_array( 'ticket_notice', $active_mts ) ) {
				$success = false;
				EE_Error::add_error( sprintf( __( 'Cannot resend the ticket notice for this registration because the corresponding message type is not active.  If you wish to send messages for this message type then please activate it by %sgoing here%s.', 'event_espresso' ), '<a href="' . admin_url( 'admin.php?page=espresso_messages&action=settings' ) . '">', '</a>' ), __FILE__, __FUNCTION__, __LINE__ );
			}

			if ( $success ) {
				//check to determine what method used to setup the trigger
				//I'm using a check for a method that only existed in a certain core branch being used for necessary
				//MER dependencies, that also contains changes in messages system that do not exist in the master
				//branch at the time of this work.
				$registration_processor = EE_Registry::instance()->load_class( 'Registration_Processor' );
				$data = method_exists( $registration_processor, 'generate_ONE_registration_from_line_item' ) ? array( $reg, EEM_Registration::status_id_approved ) :$reg;
				if ( self::_use_new_system() ) {
					try {
						$messages_to_generate = self::$_MSG_PROCESSOR->setup_mtgs_for_all_active_messengers( 'ticket_notice', $data );
						self::$_MSG_PROCESSOR->batch_queue_for_generation_and_persist( $messages_to_generate );
						self::$_MSG_PROCESSOR->get_queue()->initiate_request_by_priority();
					} catch( EE_Error $e ) {
						EE_Error::add_error( $e->getMessage(), __FILE__, __FUNCTION__, __LINE__ );
						$success = false;
					}
				} else {
					$success = self::$_EEMSG->send_message( 'ticket_notice', $data );
				}
			}
		}

		if ( $success ) {
			EE_Error::overwrite_success();
			EE_Error::add_success( __( 'The message for this registration has been re-sent', 'event_espresso' ) );
		}

		if ( $redirect && $admin_page instanceof EE_Admin_Page ) {
			$query_args = isset( $_REQUEST['redirect_to'] ) ?
				array(
					'action'  => esc_url_raw( $_REQUEST['redirect_to'] ),
					'_REG_ID' => esc_url( $_REQUEST['_REG_ID'] )
				)
				: array(
					'action' => 'default'
				);
			$admin_page->redirect_after_action( false, '', '', $query_args, true );
		} else {
			return $success;
		}
	}




	/**
	 * All the message triggers done by route go in here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected static function _register_routes() {
		EE_Config::register_route( 'ee-txn-tickets-url', 'Ticketing', 'run' );
		EE_Config::register_route( 'ee-txn-tickets-approved-url', 'Ticketing', 'run_approved' );
		do_action( 'AHEE__EED_Ticketing___register_routes' );
	}



	/**
	 * The callback for the ee-txn-tickets-approved-url route.
	 *
	 * @param WP $WP
	 *
	 * @return void
	 */
	public function run_approved( $WP ) {
		$this->_generate_tickets( true );
	}




	/**
	 * Callback for the ee-txn-tickets-url route.
	 *
	 * @param WP $WP
	 *
	 * @return void
	 */
	public function run( $WP ) {
		$this->_generate_tickets();
	}


	/**
	 * protected method for generating an aggregate ticket list for all tickets belonging to a transaction.
	 * @param bool $approved_only
	 *
	 * @throws EE_Error
	 */
	protected function _generate_tickets( $approved_only = false ) {
		//get the params from the request
		$token = EE_Registry::instance()->REQ->is_set( 'token' ) ? EE_Registry::instance()->REQ->get( 'token' ) : '';

		//verify the needed params are present.
		if ( empty( $token ) ) {
			throw new EE_Error( __( 'The request for the "ee-txn-tickets-url" route has a malformed url.', 'event_espresso' ) );
		}

		self::_load_controller();

		$registration = EEM_Registration::instance()->get_one( array( array( 'REG_url_link' => $token ) ) );

		//valid registration?
		if ( ! $registration instanceof EE_Registration ) {
			return; //get out we need a valid registration.
		}

		$transaction = $registration->transaction();
		//if primary registration then we grab all registrations and loop through to generate the html.  If not primary,
		// then we just use the existing registration and throw that ticket up.  Note this is also conditional on the
		// approved_only flag.  If that is true and there are no approved registrations for the requested route, then we
		// throw up error screen.
		if ( ! $registration->is_primary_registrant() ) {
			//need to get all registrations attached to the contact for this registrant that are for this transaction.
			$registrations = $transaction instanceof EE_Transaction ? $transaction->registrations( array( array( 'ATT_ID' => $registration->attendee() ) ) ) : array();
		} else {
			//get all registrations for transaction
			$registrations = $transaction instanceof EE_Transaction ? $transaction->registrations() : array();
		}

		if ( self::_use_new_system() ) {
			$success = self::_display_all_tickets_new_system( $approved_only, $transaction, $registrations );
		} else {
			$success = self::_display_all_tickets_old_system( $approved_only, $transaction, $registrations );
		}

		if ( ! $success && $approved_only ) {
			//no tickets generated due to approved status requirement so let's show an appropriate error
			//screen.
			EE_Registry::instance()->load_helper( 'Template' );
			EEH_Template::locate_template(
				array( EE_TICKETING_PATH . 'templates/eea-ticketing-no-generated-tickets.template.php' ),
				array(),
				true,
				false
			);
			exit;
		}
	}


	/**
	 * Generates and displays the generated html for all tickets using the new system (or returns false if unsuccessful)
	 *
	 * @param bool              $approved_only  Used to indicate if only approved registrations are to be used.
	 * @param EE_Transaction    $transaction
	 * @param EE_Registration[] $registrations
	 * @return bool|void
	 */
	protected static function _display_all_tickets_new_system( $approved_only, EE_Transaction $transaction, $registrations = array() ) {
		$messages_to_generate = array();
		foreach ( $registrations as $registration ) {
			if ( $approved_only && $registration->status_ID() !== EEM_Registration::status_id_approved ) {
				continue;
			}
			$messages_to_generate[] = new EE_Message_To_Generate( 'html', 'ticketing', $registration );
		}

		//generate and get the queue so we can get all the generated messages and use that for the content.
		try {
			/** @var EE_Messages_Queue $generated_queue */
			$generated_queue = self::$_MSG_PROCESSOR->generate_and_return( $messages_to_generate );
			return self::send_message_with_messenger_only(
				'html',
				'ticketing',
				$generated_queue,
				sprintf(
					__( 'All tickets for the transaction: %d', 'event_espresso' ),
					$transaction->ID()
				)
			);
		} catch ( EE_Error $e ) {
			return false;
		}
	}


	/**
	 * Displays all generated tickets using old messages system (or returns false if unable to do so).
	 *
	 * @deprecated 1.0.4.rc.006
	 * @since 1.0.4.rc.006
	 * @param bool  $approved_only  Used to indicate if only approved registrations are to be used.
	 * @param EE_Transaction $transaction
	 * @param array $registrations
	 * @return bool|void
	 */
	protected function _display_all_tickets_old_system( $approved_only, EE_Transaction $transaction, $registrations = array() ) {
		$success = true;
		foreach ( $registrations as $reg ) {
			if ( $approved_only && $reg->status_ID() != EEM_Registration::status_id_approved ) {
				continue;
			}

			$message = self::$_EEMSG->send_message( 'ticketing', $reg, 'html', '', 'registrant', false );
			if ( $message ) {
				$messages[] = $message;
			}
		}

		//now let's consolidate the $message objects into one message object for the actual displayed template
		$content = '';
		if ( ! empty( $messages ) ) {
			$final_msg = new stdClass();
			foreach ( $messages as $message ) {
				foreach ( $message as $msg ) {
					$final_msg->template_pack = ! empty( $msg->template_pack ) ? $msg->template_pack : null;
					$final_msg->variation = ! empty( $msg->variation ) ? $msg->variation : null;
					$content .= $msg->content;
				}
			}

			$final_msg->subject = sprintf( __( 'All tickets for the transaction: %d', 'event_espresso' ), $transaction->ID() );
			$final_msg->content = $content;
			$final_msg->template_pack = ! $final_msg->template_pack instanceof EE_Messages_Template_Pack ? EED_Messages::get_template_pack( 'default' ) : $final_msg->template_pack;
			$final_msg->variation = empty( $final_msg->variation ) ? 'default' : $final_msg->variation;

			//now we can trigger that message setup
			self::$_EEMSG->send_message_with_messenger_only( 'html', 'ticketing', $final_msg );
		} else {
			$success = false;
		}
		return $success;
	}
}
