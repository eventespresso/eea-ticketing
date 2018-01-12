This checklist verifies that the ticket notice sends from all the triggers it should send and not from triggers it should not send.

The following triggers _should_ send a ticket notice:

* [ ] When an approved registration is processed as a result of completing a registration on the frontend registration form (spco).
* [ ] When an approved registration is processed as a result of completing a registration using the admin side registration (spco) and send notifications flag is checked.
* [ ] When a registration status is changed to approved via the registration details page and the send notifications flag is checked.
* [ ] When the "resend ticket notice" for a individual registration in the registration list table is checked.

Also verify:

* [ ] If the registration is not approved for any of the above triggers, the registrant should NOT receive a ticket notice.