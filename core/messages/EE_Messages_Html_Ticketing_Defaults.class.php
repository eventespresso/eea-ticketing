<?php
/**
 * This file contains the EE_Messages_Html_Ticketing_Defaults class.
 * @package      EE Ticketing
 * @subpackage messages
 * @since           1.0.0
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * Class for setting up defaults for the Ticketing Html Messages combo.
 *
 * Handles all the defaults for Email messenger, Newsletter message type templates
 *
 * @package        EE Ticketing
 * @subpackage  messages
 * @since            1.0.0
 * @author          Darren Ethier
 */
class EE_Messages_Html_Ticketing_Defaults extends EE_Message_Template_Defaults {
    protected function _set_props() {
        $this->_m_name = 'html';
        $this->_mt_name = 'ticketing';
    }


    protected function _change_templates() {
        return array();
    }
}
