<?php
/**
 * This file contains the EE_Messages_Html_Ticketing_Validator class.
 * @package      EE Ticketing
 * @subpackage messages
 * @since           1.0.0
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * Holds any special validation rules for template fields with Email messenger and Newsletter
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
        return; //nothing needed to change currently.
    }
}
