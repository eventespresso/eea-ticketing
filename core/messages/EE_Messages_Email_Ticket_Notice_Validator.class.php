<?php
/**
 * This file contains the EE_Messages_Email_Ticket_Notice_Validator class.
 * @package      EE Ticketing
 * @subpackage messages
 * @since           1.0.0
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * Holds any special validation rules for template fields with Email messenger and Ticket Notice
 * message type.
 *
 *
 * @package        EE Ticketing
 * @subpackage  messages
 * @since            1.0.0
 * @author          Darren Ethier
 */
class EE_Messages_Email_Ticket_Notice_Validator extends EE_Messages_Validator {

	public function __construct( $fields, $context ) {
		$this->_m_name = 'email';
		$this->_mt_name = 'ticket_notice';
		parent::__construct( $fields, $context );
	}


	protected function _modify_validator() {
		$new_config = $this->_MSGR->get_validator_config();
		//modify just event_list
		$new_config['event_list'] = array(
			'shortcodes' => array('event', 'attendee_list', 'ticket_list', 'datetime_list', 'venue', 'organization', 'event_author', 'primary_registration_details', 'primary_registration_list', 'recipient_details', 'recipient_list'),
			'required' => array('[EVENT_LIST]')
			);
		$this->_MSGR->set_validator_config( $new_config );

		if ( $this->_context != 'admin' )
			$this->_valid_shortcodes_modifier[$this->_context]['event_list'] = array('event', 'attendee_list', 'ticket_list', 'datetime_list', 'venue', 'organization', 'event_author', 'primary_registration_details', 'primary_registration_list', 'recipient_details', 'recipient_list');

		$this->_specific_shortcode_excludes['content'] = array('[DISPLAY_PDF_URL]', '[DISPLAY_PDF_BUTTON]');
	}
} //end EE_Messages_Email_Ticket_Notice_Validator
