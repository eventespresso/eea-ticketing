<?php
defined('EVENT_ESPRESSO_VERSION') || exit;

/**
 * The message type for notifications to users about available tickets.
 *
 * @package          EE Ticketing
 * @subpackage       messages
 * @since            1.0.0
 * @author           Darren Ethier
 */
class EE_Ticket_Notice_message_type extends EE_Registration_Base_message_type
{

    public function __construct()
    {
        $this->name              = 'ticket_notice';
        $this->description       = __('This message type is for messages sent to attendees when they have tickets available',
            'event_espresso');
        $this->label             = array(
            'singular' => __('ticket notice', 'event_espresso'),
            'plural'   => __('ticket notices', 'event_espresso'),
        );
        $this->_master_templates = array(
            'email' => 'registration',
        );
        parent::__construct();
    }


    protected function _set_contexts()
    {
        $this->_context_label = array(
            'label'       => __('recipient', 'event_espresso'),
            'plural'      => __('recipients', 'event_espresso'),
            'description' => __('Recipient\'s are who will receive the template.  You may want different ticket notice details sent out depending on who the recipient is',
                'event_espresso'),
        );

        $this->_contexts = array(
            'primary_attendee' => array(
                'label'       => __('Primary Registrant', 'event_espresso'),
                'description' => __('This template is what the primary registrant (the person who completed the initial transaction) with a ticket notice.',
                    'event_espresso'),
            ),
            'attendee'         => array(
                'label'       => __('Registrant', 'event_espresso'),
                'description' => __('This template is what each registrant for the event will receive with a ticket notice.',
                    'event_espresso'),
            ),
        );
    }


    protected function _primary_attendee_addressees()
    {
        $this->_single_message = false;
        return parent::_primary_attendee_addressees();
    }
} //end class EE_Ticket_Notice_message_type
