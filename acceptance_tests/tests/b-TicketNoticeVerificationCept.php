<?php

/**
 * This test covers the ticket-notice-sends.md testing checklist.
 */

use Page\EventsAdmin;
use Page\MessagesAdmin;
use Page\RegistrationsAdmin;
use Page\TicketingGeneral;
use Page\TicketSelector;

$event_approved_title = 'Ticket Notice Approved Tests';
$ticket_notice_frontend_recipient = [
    'fname' => 'TN',
    'lname' => 'A',
    'email' => 'tna@example.com'
];
$ticket_notice_backend_recipient = [
    'fname' => 'TN',
    'lname' => 'B',
    'email' => 'tnb@example.com'
];

$I = new EventEspressoAddonAcceptanceTester(
    $scenario,
    TicketingGeneral::ADDON_SLUG_FOR_WP_PLUGIN_PAGE,
    false
);
$I->wantTo('Test that Ticket Notices send in the various contexts as expected and described in the ticket-notice-sends.md testing checklist.');
$I->amGoingTo('Create an event for testing approved registrations on.');
$I->loginAsAdmin();
$I->amOnDefaultEventsListTablePage();
$I->click(EventsAdmin::ADD_NEW_EVENT_BUTTON_SELECTOR);
$I->see('Enter event title here');
$I->fillField(EventsAdmin::EVENT_EDITOR_TITLE_FIELD_SELECTOR, $event_approved_title);
$I->publishEvent();
$event_approved_link = $I->observeLinkUrlAt(EventsAdmin::EVENT_EDITOR_VIEW_LINK_AFTER_PUBLISH_SELECTOR);
$event_approved_id = $I->observeValueFromInputAt(EventsAdmin::EVENT_EDITOR_EVT_ID_SELECTOR);

//logout and do a registration to verify ticket notice is sent.
$I->amGoingTo('Test ticket notice sent for approved registration from frontend spco');
$I->logOut();
$I->amOnUrl($event_approved_link);
$I->see($event_approved_title);
$I->selectOption(TicketSelector::ticketOptionByEventIdSelector($event_approved_id), '1');
$I->click(TicketSelector::ticketSelectionSubmitSelectorByEventId($event_approved_id));
$I->waitForText('Personal Information');
$I->fillOutFirstNameFieldForAttendee($ticket_notice_frontend_recipient['fname']);
$I->fillOutLastNameFieldForAttendee($ticket_notice_frontend_recipient['lname']);
$I->fillOutEmailFieldForAttendee($ticket_notice_frontend_recipient['email']);
$I->goToNextRegistrationStep();
$I->waitForText('Congratulations', 15);

$I->loginAsAdmin();
$I->amOnMessagesActivityListTablePage();
$I->see(
    $ticket_notice_frontend_recipient['email'],
    MessagesAdmin::messagesActivityListTableCellSelectorFor(
        'to',
        'Ticket Notice',
        MessagesAdmin::MESSAGE_STATUS_SENT,
        '',
        'Registrant'
    )
);

$I->amGoingTo('Test ticket notice sent for approved registration from admin spco.');
$I->amOnAdminRegistrationPageForEvent($event_approved_id);
$I->selectOption(TicketSelector::ticketOptionByEventIdSelector($event_approved_id), '1');
$I->click(TicketSelector::ticketSelectionSubmitSelectorByEventId($event_approved_id, true));
$I->waitForText('Step Two');
$I->fillOutFirstNameFieldForAttendee($ticket_notice_backend_recipient['fname'], 1, true);
$I->fillOutLastNameFieldForAttendee($ticket_notice_backend_recipient['lname'], 1, true);
$I->fillOutEmailFieldForAttendee($ticket_notice_backend_recipient['email'], 1, true);
$I->click(TicketSelector::ticketSelectionSubmitSelectorByEventId($event_approved_id, true));
$I->waitForText('Event Espresso - Transactions');
$I->amOnMessagesActivityListTablePage();
$I->see(
    $ticket_notice_backend_recipient['email'],
    MessagesAdmin::messagesActivityListTableCellSelectorFor(
        'to',
        'Ticket Notice',
        MessagesAdmin::MESSAGE_STATUS_SENT,
        '',
        'Registrant'
    )
);

$I->amGoingTo('Test that ticket notice is sent when bulk approving a registration from registration list table');
$I->amOnDefaultRegistrationsListTableAdminPage();
$I->selectBulkActionCheckboxesForRegistrationIds([1]);
$I->submitBulkActionOnListTable('pending_registrations');
$I->waitForText('Registration status has been set to pending payment');
$I->selectBulkActionCheckboxesForRegistrationIds([1]);
$I->submitBulkActionOnListTable('approve_and_notify_registrations');
$I->waitForText('Registration status has been set to approved');
$I->amOnMessagesActivityListTablePage();
$I->verifyMatchingCountofTextInMessageActivityListTableFor(
    2,
    $ticket_notice_frontend_recipient['email'],
    'to',
    'Ticket Notice',
    MessagesAdmin::MESSAGE_STATUS_SENT,
    'Email',
    'Registrant'
);

$I->amGoingTo('Test that ticket notice is sent when approving a registration from registration details page');
$I->amOnDefaultRegistrationsListTableAdminPage();
$I->selectBulkActionCheckboxesForRegistrationIds([2]);
$I->submitBulkActionOnListTable('pending_registrations');
$I->waitForText('Registration status has been set to pending payment');
$I->clickViewDetailsLinkForRegistrationWithId(2);
$I->changeRegistrationStatusOnRegistrationDetailsPageTo(RegistrationsAdmin::REGISTRATION_STATUS_APPROVED);
$I->amOnMessagesActivityListTablePage();
$I->verifyMatchingCountofTextInMessageActivityListTableFor(
    2,
    $ticket_notice_backend_recipient['email'],
    'to',
    'Ticket Notice',
    MessagesAdmin::MESSAGE_STATUS_SENT,
    'Email',
    'Registrant'
);

