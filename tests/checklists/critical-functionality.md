A checklist covering all the critical functionality for the Ticketing Add-on.

### On Fresh Activation

* [ ] Ensure Ticket Notice and Ticketing message types are active by default and that their message templates were generated.

### Ticket Notice Message Type

* [ ] Ensure that on an approved registration, the ticket notice is sent out.
* [ ] Ensure that if a registration is done that is not approved that no ticket notice is sent out.

### Ticket URL Shortcodes

* [ ] Verify the behaviour of the `[TICKET_URL]` shortcode is as expected.  It should generate a single ticket for the recipient in the browser when clicked.
* [ ] Verify the behaviour of the `[RECIPIENT_TICKET_URL]` shortcode.  It's behaviour is the same as the `[TICKET_URL]` shortcode except that its usable in more fields.
* [ ] Verify the behaviour of the `[TXN_TICKETS_URL]`.  If viewed by the primary registrant it will generate all the tickets for the transaction the primary registrant belongs to.  If viewed by a non primary registrant, it will only generate the ticket for the registration viewing the ticket.
* [ ] Verify the behaviour of the `[TXN_TICKETS_APPROVED_URL]`.  It works similarly to the `[TXN_TICKETS_URL]` shortcode except it will only generate tickets for approved registrations.

### Other shortcodes.

* [ ] Verify that the `[QRCODE_*]` shortcode works as expected.
* [ ] Verify that the `[GRAVATAR_*]` shortcode works as expected.
* [ ] Verify that the `[BARCODE_*]` shortcode works as expected.

### Admin triggers

* [ ] Ticket notice should automatically get sent along with notifications triggered for approved registrations (if the ticket notice message type is active).
* [ ] There should be a specific resend ticket notice trigger in the registration list table for each registration.  Verify that when you click that action the ticket notice is regenerated and sent. Note it will be re-generated sent for all registrations in the same transaction.
* [ ] There should be a view ticket icon in the actions column for each registration in the registration list table.  Verify that you are able to view the generated ticket for that registration when clicking the link.
* [ ] There should be a view ticket icon in the actions column for each transaction in the transaction list table.  When you click this icon it should lead to a generated list of tickets for every registration in that transaction.