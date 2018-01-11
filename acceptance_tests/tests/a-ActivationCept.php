<?php

/**
 * This test run covers the `Add-on activation` seciont for the critical-functionality.md checklist
 */

use Page\CoreAdmin;
use Page\MessagesAdmin;
use Page\TicketingGeneral;
use Page\TicketingMessageTemplate;

$I = new EventEspressoAddonAcceptanceTester(
    $scenario,
    TicketingGeneral::ADDON_SLUG_FOR_WP_PLUGIN_PAGE
);
$I->wantTo('Test "Add-on Activation" section for the critical-functionality.md checklist');
$I->loginAsAdmin();

//check that default templates are available in the Global Message Template list table.
$I->amOnDefaultMessageTemplateListTablePage();
$I->click(CoreAdmin::ADMIN_LIST_TABLE_NEXT_PAGE_CLASS);
$I->waitForText('Ticket Notice');
$I->see('The ticket message type is used');

//verify both templates can be accessed without error!
$I->clickToEditMessageTemplateByMessageType(
    TicketingMessageTemplate::MESSAGE_TYPE_SLUG_TICKET_NOTICE,
    MessagesAdmin::ATTENDEE_CONTEXT_SLUG
);
$I->waitForText('The template for Registrant Recipient is currently active');
$I->amOnDefaultMessageTemplateListTablePage();
$I->click(CoreAdmin::ADMIN_LIST_TABLE_NEXT_PAGE_CLASS);
$I->clickToEditMessageTemplateByMessageType(
    TicketingMessageTemplate::MESSAGE_TYPE_SLUG_TICKETING,
    TicketingMessageTemplate::CONTEXT_SLUG_REGISTRANT
);
$I->waitForText('The template for Registrant Recipient is currently active');

//go to message settings page and make sure all messages are sent on same request for testing
$I->amGoingTo('Set all messages to be sent on same request for tests.');
$I->amOnMessageSettingsPage();
$I->selectOption(MessagesAdmin::GLOBAL_MESSAGES_SETTINGS_ON_REQUEST_SELECTION_SELECTOR, '1');
$I->click(MessagesAdmin::GLOBAL_MESSAGES_SETTINGS_SUBMIT_SELECTOR);
