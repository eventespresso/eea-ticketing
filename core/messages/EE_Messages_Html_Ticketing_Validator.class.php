<?php
/**
 * This file contains the EE_Messages_Html_Ticketing_Validator class.
 * @package      EE Ticketing
 * @subpackage messages
 * @since           1.0.0
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * Holds any special validation rules for template fields with HTML messenger and Ticketing
 * message type.
 *
 *
 * @package        EE Ticketing
 * @subpackage  messages
 * @since            1.0.0
 * @author          Darren Ethier
 */
class EE_Messages_Html_Ticketing_Validator extends EE_Messages_Validator {
    public function __construct( $fields, $context ) {
        $this->_m_name = 'html';
        $this->_mt_name = 'ticketing';

        parent::__construct( $fields, $context );
    }

    /**
     * custom validator (restricting what was originally set by the messenger)
     */
    protected function _modify_validator() {
        $new_config = $this->_MSGR->get_validator_config();
        $new_config['datetime_list']['shortcodes'] =  array('datetime');
        $new_config['content']['shortcodes'] = array( 'organization',  'primary_registration_list', 'primary_registration_details',  'email', 'transaction', 'payment_list', 'venue', 'event', 'messenger', 'ticket', 'recipient_details', 'datetime_list', 'question_list' );
        $new_config['subject']['shortcodes'] = array('organization', 'primary_registration_details', 'email', 'event', 'transaction' );

        $this->_MSGR->set_validator_config( $new_config );

        //specific shortcode excludes
        $this->_specific_shortcode_excludes['subject'] = array( '[QRCODE_*]', '[GRAVATAR_*]', '[BARCODE_*]', '[TICKET_URL]' );
        $this->_specific_shortcode_excludes['main'] = array( '[TICKET_URL]' );
    }
}
