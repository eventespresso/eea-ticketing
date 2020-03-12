<?php

/**
 * EED_Ticketing_WPUser_Integration
 * This module is used for any integrations with the EE WPUser Integration add-on.
 *
 * @author  Darren Ethier
 * @since   1.0.6.rc.006
 */
class EED_Ticketing_WPUser_Integration extends EED_Module
{
    /**
     * Set hooks on frontend.
     */
    public static function set_hooks()
    {
        self::setHooksEveryWhere();
    }

    /**
     * Set hooks in admin and ajax requests.
     */
    public static function set_hooks_admin()
    {
        self::setHooksEveryWhere();
    }


    /**
     * Setting the global hooks for integrating with the WPUser add-on.
     * Hooks are set in this way for things that we have no idea where they might be loaded.  Keep in mind that there
     * are some things (such as the WP_User `[ESPRESSO_MY_EVENTS]` shortcode) that generate things on an ajax request.
     * Hence the need to set the hook in admin as well (because ajax requests run in the context of the admin).
     */
    protected static function setHooksEveryWhere()
    {
        add_filter(
            'FHEE__EES_Espresso_My_Events__actions',
            array('EED_Ticketing_WPUser_Integration', 'includeTicketLinkWithMyEventsShortcode'),
            10,
            2
        );
        add_filter(
            'FHEE__status-legend-espresso_my_events__legend_items',
            array('EED_Ticketing_WPUser_Integration', 'includeTicketLinkInLegendForMyEventsShortcode')
        );
    }


    /**
     * Callback for FHEE__EES_Espresso_My_Events__actions that implements the ticket link as an action in the
     * `[ESPRESSO_MY_EVENTS]` output.
     *
     * @param array           $actions Existing actions
     * @param EE_Registration $registration
     * @return array
     * @throws EE_Error
     * @throws \EventEspresso\core\exceptions\EntityNotFoundException
     */
    public static function includeTicketLinkWithMyEventsShortcode($actions, $registration)
    {
        if (! $registration instanceof EE_Registration
            || $registration->status_ID() !== EEM_Registration::status_id_approved
            || ! EEH_MSG_Template::is_mt_active('ticketing')
        ) {
            return $actions;
        }
        $actions = (array) $actions;
        $link_to_view_ticket_text = esc_html__('Link to view ticket', 'event_espresso');
        $actions['ticket'] = '<a aria-label="' . $link_to_view_ticket_text
                . '" title="' . $link_to_view_ticket_text
                . '" href="' . EED_Ticketing::getTicketUrl($registration) . '">'
                . '<span class="dashicons dashicons-tickets-alt ee-icon-size-18"></span></a>';
        return $actions;
    }



    /**
     * Callback for FHEE__status-legend-espresso_my_events__legend_items that implements including the icon for ticket
     * links in the legend listed with the output of the `[ESPRESSO_MY_EVENTS]` shortcode.
     *
     * @param array $legend_items
     * @return array
     */
    public static function includeTicketLinkInLegendForMyEventsShortcode($legend_items)
    {
        if (! EEH_MSG_Template::is_mt_active('ticketing')) {
            return $legend_items;
        }
        $legend_items = (array) $legend_items;
        $legend_items['ticket'] = array(
            'class' => 'dashicons dashicons-tickets-alt',
            'desc' => esc_html__('View Ticket', 'event_espresso')
        );
        return $legend_items;
    }

    /**
     * @param       WP $WP
     */
    public function run($WP)
    {
        // not implemented for this module.
    }
}
