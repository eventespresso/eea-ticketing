<?php
/**
 * This file contains the EE_Ticketing_message_type class.
 * @package      EE Ticketing
 * @subpackage messages
 * @since           1.0.0
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * The message type for tickets.
 *
 * The ticket message type is used for generating and displaying tickets.
 *
 * @package        EE Ticketing
 * @subpackage  messages
 * @since            1.0.0
 * @author          Darren Ethier
 */
class EE_Ticketing_message_type extends EE_message_type {

    public function __construct() {
        $this->name = 'ticketing';
        $this->description = __('The ticket message type is used for generating and displaying tickets. The templates are triggered by url path.', 'event_espresso');
        $this->label = array(
            'singular' => __('ticket', 'event_espresso'),
            'plural' => __('tickets', 'event_espresso')
            );
        parent::__construct();
    }



    /**
     * This sets up any action/filter hooks this message type puts in place.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function _do_messenger_hooks() {
    	if ( $this->_active_messenger instanceof EE_messenger  && $this->_active_messenger->name == 'html' ) {
    		add_filter( 'FHEE__EE_Html_messenger__get_inline_css_template__css_url', array( $this, 'add_ticketing_css' ), 10, 3 );
    	}
    }



    /**
     * This is the callback for the FHEE__EE_Html_messenger__get_inline_css_template__css_url filter in the html messenger.
     * By default html messenger includes css for the invoice css and order-confirmation css.  We need to override that when ticketing message type is used so that it's css (for the default template pack) gets used.
     *
     * @since 1.0.0
     *
     * @param string $url   The initial url being swapped out.
     * @param string $type What "type" of css is being filtered.
     */
    public function add_ticketing_css( $url, $path_or_url, $type ) {
    	switch ( $type ) {

    		case 'base' :
    			$base = 'messages/assets/html/html-messenger-inline-base-css.template.css';
    			break;
    		case 'print' :
    			$base = 'messages/assets/html/html-messenger-inline-print-css.template.css';
    			break;
    		case 'wpeditor' :
    			$base = 'messages/assets/html/html-messenger-inline-wpeditor-css.template.css';
    			break;
    		default :
    			$base = 'messages/assets/html/html-messenger-inline-css.template.css';
    			break;
    	}

    	return $url ? apply_filters( 'FHEE__EE_Ticketing_message_type__add_ticketing_css__url', EE_TICKETING_URL . 'core/' . $base, $url, $type )  : apply_filters( 'FHEE__EE_Ticketing_message_type__add_ticketing_css__path',EE_LIBRARIES . $base, $url, $type );
    }



    protected function _set_admin_pages() {
        $this->admin_registered_pages = array(
            'events_edit' => TRUE
            );
    }



    protected function _set_data_handler() {
        $this->_data_handler = 'REG';
    }



    protected function _get_data_for_context( $context, EE_Registration $registration, $id ) {
        return $registration;
    }



    protected function _set_admin_settings_fields() {
        $this->_admin_settings_fields = array();
    }



    protected function _set_default_field_content() {
        $this->_default_field_content = array(
            'subject' => $this->_default_template_field_subject(), //this will be the title of the generated page.
            'content' => $this->_default_template_field_content()
            );
    }



    protected function _default_template_field_subject() {
        foreach ( $this->_contexts as $context => $details ) {
            $content[$context] = sprintf( __('Your Ticket For  %s', 'event_espresso'), '[EVENT_NAME]');
        }
        return $content;
    }



    protected function _default_template_field_content() {
        $content = file_get_contents( EE_TICKETING_PATH . 'core/messages/templates/ticketing-message-type-content.template.php', TRUE );
        $dttlist_content = file_get_contents( EE_TICKETING_PATH . 'core/messages/templates/ticketing-message-type-datetime-list-content.template.php', TRUE );

        foreach ( $this->_contexts as $context => $details ) {
            $tcontent[$context]['main'] = $content;
            $tcontent[$context]['question_list'] = '';
            $tcontent[$context]['datetime_list'] = $dttlist_content;
        }
        return $tcontent;
    }



    protected function _set_contexts() {
        $this->_context_label = array(
            'label' => __('recipient', 'event_espresso'),
            'plural' => __('recipients', 'event_espresso'),
            'description' => __('Recipient\'s are who will receive the ticket.', 'event_espresso')
            );

        $this->_contexts = array(
            'registrant' => array(
                'label' => __('Registrant', 'event_espresso'),
                'description' => __('This template goes to selected registrants.')
                )
            );
    }




    /**
     * used to set the valid shortcodes.
     *
     * For the newsletter message type we only have two valid shortcode libraries in use, recipient details and organization.  That's it!
     *
     * @since   1.0.0
     *
     * @return  void
     */
    protected function _set_valid_shortcodes() {
        parent::_set_valid_shortcodes();

        $included_shortcodes = array(
            'recipient_details', 'organization', 'event', 'ticket', 'venue', 'primary_registration_details', 'event_author', 'email','event_meta', 'recipient_list', 'transaction', 'datetime_list', 'question_list', 'datetime', 'question'
            );

        //add shortcodes to the single 'registrant' context we have for the ticketing message type
        $this->_valid_shortcodes['registrant'] = $included_shortcodes;

    }


    protected function _set_with_messengers() {
        $this->_with_messengers = array();
    }




    /**
     * Takes care of setting up the addresee object(s) for the registrations that are used in parsing the ticket templates.
     *
     * @since 1.0.0
     *
     * @return EE_Addressee[]
     */
    protected function _registrant_addressees() {
            $add = array();

            //just looping through the attendees to make sure that the attendees listed are JUST for this registration.
            foreach ( $this->_data->attendees[$this->_data->reg_obj->attendee_ID()] as $item => $value ) {
                $aee[$item] = $value;
            }

            $aee['events'] = $this->_data->events;
            $aee['reg_obj'] = $this->_data->reg_obj;
            $aee['attendees'] = $this->_data->attendees;
            $aee = array_merge( $this->_default_addressee_data, $aee );
            $add[] = new EE_Messages_Addressee( $aee );
            return $add;
    }


}
