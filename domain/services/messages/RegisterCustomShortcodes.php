<?php
namespace EventEspresso\Ticketing\domain\services\messages;

use EE_Shortcodes;
use EE_Ticket_Shortcodes;
use EE_Attendee_Shortcodes;
use EE_Recipient_Details_Shortcodes;
use EE_Transaction_Shortcodes;
use EE_Messages_Addressee;
use EE_Error;
use EE_Registration;
use EE_Ticket;
use EE_Attendee;
use EE_Transaction;
use EE_Messages_Validator;
use EE_Messages_Html_Ticketing_Validator;
use EED_Ticketing;

class RegisterCustomShortcodes
{
    /**
     * RegisterCustomShortcodes constructor.
     */
    public function __construct()
    {
        // register new shortcodes with existing libraries.
        add_filter(
            'FHEE__EE_Shortcodes__shortcodes',
            array($this, 'registerNewShortcodes'),
            10,
            2
        );
        add_filter(
            'FHEE__EE_Shortcodes__parser_after',
            array($this, 'registerNewShortcodeParsers'),
            10,
            5
        );
        add_filter(
            'FHEE__EE_Messages_Validator__get_specific_shortcode_excludes',
            array($this, 'excludeNewShortcodes'),
            10,
            3
        );
    }


    /**
     * Callback for FHEE__EE_Shortcodes__shortcodes
     *
     * @since 1.0.6.rc.001
     * @param array         $shortcodes
     * @param EE_Shortcodes $shortcode_library
     * @return array
     */
    public function registerNewShortcodes($shortcodes, EE_Shortcodes $shortcode_library)
    {
        // shortcodes to add to EE_Ticket_Shortcodes
        if ($shortcode_library instanceof EE_Ticket_Shortcodes) {
            $shortcodes['[QRCODE_*]'] = esc_html__(
                'This is a shortcode used for generating a qrcode for the registration.  The only thing stored via this code is the unique reg_url_link code attached to a registration record.',
                'event_espresso'
            )
            . '<p>'
            . esc_html__(
                'Note: there are a number of different parameters you can use for the qrcode generated.  We have the defaults at the recommended settings, however, you can change these:',
                'event_espresso'
            )
            . '<ul>'
            . '<li><strong>d</strong>:'
            . esc_html__(
                'You can add a extra param for setting the dimensions of the qr code using "d=20" format.  So [QRCODE_* d=40] will parse to a qrcode that is 40 pixels wide by 40 pixels high.',
                'event_espresso'
            )
            . '</li>'
            . '<li><strong>color</strong>:'
            . esc_html__(
                'Use a hexadecimal color for the qr code color.  So you can do [QRCODE_* color=#f00] to have the code printed in red.',
                'event_espresso'
            )
            . '</li>'
            . '<li><strong>mode</strong>:'
            . esc_html__(
                'This parameter is used to indicate what mode the code is generated in.  0 = normal, 1 = label strip, 2 = label box.  Use in the format [QRCODE_* mode=2].',
                'event_espresso'
            )
            . '</li>'
            . '<li><strong>label</strong>:'
            . esc_html__(
                'This allows you to set a custom label that will appear over the code. [QRCODE_* label="My QR Code"]',
                'event_espresso'
            )
            . '</li>'
            . '</ul></p>';
            $shortcodes['[GRAVATAR_*]'] = esc_html__(
                'This shortcode will grab the email address attached to the registration and use that to attempt to grab a gravatar image.  If none is found then whatever is set in your WordPress settings for Default Avatar will be used. You can include what you want the dimensions of the gravatar to be by including params in the folowing format: "d=40".  So [GRAVATAR_* d=40] will parse to a gravatar image that is 40 pixels wide by 40 pixels high.',
                'event_espresso'
            );
            $shortcodes['[BARCODE_*]'] = esc_html__(
                'This shortcode is used to generate a custom barcode for the ticket instead of a qrcode.  There are a number of different options for the barcode:',
                'event_espresso'
            )
             . '<p><ul>'
             . '<li><strong>w</strong>:'
             . esc_html__(
                 'Used to set the width (default is 1). [BARCODE_* w=20]',
                 'event_espresso'
             )
             . '</li>'
             . '<li><strong>h</strong>:'
             . esc_html__(
                 'Used to set the height (default is 70). [BARCODE_* h=50]',
                 'event_espresso'
             )
             . '</li>'
             . '<li><strong>type</strong>:'
             . esc_html__(
                 'Used to set the barcode type (default is code93). [BARCODE_* type=code93].  There are 4 different types you can choose from:',
                 'event_espresso'
             )
             . '<ul>'
             . '<li>code39</li>'
             . '<li>code93</li>'
             . '<li>code128</li>'
             . '<li>datamatrix</li>'
             . '</li></ul>'
             . '<li><strong>bgcolor</strong>:'
             . esc_html__(
                 'Used to set the background color of the barcode (default is #FFFFF [white] ). [BARCODE_* bgcolor=#FFFFFF]',
                 'event_espresso'
             )
             . '</li>'
             . '<li><strong>color</strong>:'
             . esc_html__(
                 'Used to set the foreground color of the barcode (default is #000000 [black] ). [BARCODE_* color=#000000]',
                 'event_espresso'
             )
             . '</li>'
             . '<li><strong>fsize</strong>:'
             . esc_html__(
                 'Used to set the fontsize for the barcode (default is 10). [BARCODE_* fsize=10]',
                 'event_espresso'
             )
             . '</li>'
             . '<li><strong>output_type</strong>:'
             . sprintf(
                 /* translators: 1: emphasis tag, 2: close emphasis tag, 3: shortcode example */
                 esc_html__(
                     'Used to set the output type for the generated barcode (default is svg). Can be either svg, canvas, bmp, or css. %1$sNote: Some output types don\'t print well depending on the browser. Make sure you verify printability.%2$s %3$s',
                     'event_espresso'
                 ),
                 '<em>',
                 '</em>',
                 '<code>[BARC0DE_* output_type=bmp]</code>'
             )
             . '</li>'
             . '<li><strong>generate_for</strong>:'
             . sprintf(
                 /* translators: 1: emphasis tag, 2: close emphasis tag, 3: shortcode example */
                 esc_html__(
                     'This allows you to set what gets used to generate the barcode. When the barcode is scanned this is the value that will be returned. There are two options: "long_code", which is the equivalent to %1$sreg_url_link%2$s value for the registration; or "short_code", which is the equivalent to the %1$sreg_code%2$s value for the registration. The default is "short_code". %3$s',
                     'event_espresso'
                 ),
                 '<em>',
                 '</em>',
                 '<code>[BARCODE_* generate_for=short_code]</code>'
             )
             . '</ul></p>';
        }
        if ($shortcode_library instanceof EE_Attendee_Shortcodes) {
            $shortcodes['[TICKET_URL]'] = esc_html__(
                'This shortcode generates the url for accessing the ticket.',
                'event_espresso'
            );
        }
        if ($shortcode_library instanceof EE_Recipient_Details_Shortcodes) {
            $shortcodes['[RECIPIENT_TICKET_URL]'] = esc_html__(
                'This shortcode generates the url for the ticket attached to the registration record for the recipient of a message.',
                'event_espresso'
            );
        }
        if ($shortcode_library instanceof EE_Transaction_Shortcodes) {
            $shortcodes['[TXN_TICKETS_URL]'] = esc_html__(
                'This shortcode generates the url for all tickets in a transaction.',
                'event_espresso'
            );
            $shortcodes['[TXN_TICKETS_APPROVED_URL]'] = esc_html__(
                'This shortcode generates the url for all tickets in a transaction. However, only tickets for approved registrations are generated via the url on this shortcode.',
                'event_espresso'
            );
        }
        return $shortcodes;
    }


    /**
     * Call back for the FHEE__EE_Shortcodes__parser_after filter.
     * This contains the logic for parsing the new shortcodes introduced by this addon.
     *
     * @since 1.0.6.rc.001
     * @param string                      $parsed     The current parsed template string.
     * @param string                      $shortcode  The incoming shortcode being setup for parsing.
     * @param array|EE_Messages_Addressee $data       Depending on the shortcode parser the filter is called in, this
     *                                                will represent either an array of data objects or a specific data
     *                                                object.
     * @param array|EE_Messages_Addressee $extra_data Depending on the shortcode parser the filter is called in, this
     *                                                will either represent an array with an array of templates being
     *                                                parsed, and a EE_Addressee_Data object OR just an
     *                                                EE_Addressee_Data object.
     * @param EE_Shortcodes               $shortcode_library
     * @return string The parsed string
     * @throws EE_Error
     * @throws \EventEspresso\core\exceptions\EntityNotFoundException
     */
    public function registerNewShortcodeParsers(
        $parsed,
        $shortcode,
        $data,
        $extra_data,
        EE_Shortcodes $shortcode_library
    ) {
        // only do this parsing on the EE_Ticket_Shortcodes parser
        if ($shortcode_library instanceof EE_Ticket_Shortcodes) {
            $ticket = $shortcode_library->get_ticket_set();
            $ee_messages_addressee = $data instanceof EE_Messages_Addressee ? $data : null;
            $ee_messages_addressee = $extra_data instanceof EE_Messages_Addressee
                ? $extra_data
                : $ee_messages_addressee;
            $registration = $ee_messages_addressee instanceof EE_Messages_Addressee
                            && $ee_messages_addressee->reg_obj instanceof EE_Registration
                ? $ee_messages_addressee->reg_obj
                : null;
            // verify required data present
            if (! $ticket instanceof EE_Ticket || ! $registration instanceof EE_Registration) {
                return $parsed;
            }

            // require the shortcode file if necessary
            if (! function_exists('shortcode_parse_atts')) {
                require_once(ABSPATH . WPINC . '/shortcodes.php');
            }

            // see if there are any atts on the shortcode.
            $shortcode_to_parse = str_replace(array('[', ']'), '', $shortcode);
            $attrs = shortcode_parse_atts($shortcode_to_parse);
            if (strpos($shortcode, '[QRCODE_*') !== false) {
                // set custom dimension if present or default if not.
                $dimensions = isset($attrs['d']) ? (int) $attrs['d'] : 110;
                // color?
                $color = isset($attrs['color']) ? $attrs['color'] : '#000';
                // mode?
                $mode = isset($attrs['mode']) ? (int) $attrs['mode'] : 0;
                $mode = $mode > 2 || $mode < 0 ? 0 : $mode;
                // label?
                $label = isset($attrs['label']) ? $attrs['label'] : '';
                // all the parsed qr code really does is setup some hidden values for the qrcode js to do its thing.
                $parsed = '<div class="ee-qr-code"><span class="ee-qrcode-dimensions" style="display:none;">'
                          . $dimensions
                          . '</span>';
                $parsed .= '<span class="ee-qrcode-reg_url_link" style="display:none;">'
                           . $registration->reg_url_link()
                           . '</span>';
                $parsed .= '<span class="ee-qrcode-color" style="display:none;">' . $color . '</span>';
                $parsed .= '<span class="ee-qrcode-mode" style="display:none;">' . $mode . '</span>';
                $parsed .= '<span class="ee-qrcode-label" style="display:none;">' . $label . '</span>';
                $parsed .= '</div>';
            } elseif (strpos($shortcode, '[GRAVATAR_*') !== false) {
                $attendee = $ee_messages_addressee->att_obj;
                $email = $attendee instanceof EE_Attendee ? $attendee->email() : '';
                $size = isset($attrs['d']) ? (int) $attrs['d'] : 110;
                $parsed = get_avatar($email, $size);
            } elseif (strpos($shortcode, '[BARCODE_*') !== false) {
                // attributes
                $width = isset($attrs['w']) ? (int) $attrs['w'] : 1;
                $height = isset($attrs['h']) ? (int) $attrs['h'] : 70;
                $type = isset($attrs['type']) ? $attrs['type'] : 'code93';
                $bgcolor = isset($attrs['bgcolor']) ? $attrs['bgcolor'] : '#ffffff';
                $color = isset($attrs['color']) ? $attrs['color'] : '#000000';
                $fsize = isset($attrs['fsize']) ? (int) $attrs['fsize'] : 10;
                $code_value = isset($attrs['generate_for']) ? trim($attrs['generate_for']) : 'short_code';
                $reg_code = $code_value === 'long_code' ? $registration->reg_url_link() : $registration->reg_code();
                if (isset($attrs['output_type'])) {
                    $valid_output_types = array('css', 'svg', 'canvas', 'bmp');
                    $output_type = in_array(
                        $attrs['output_type'],
                        $valid_output_types,
                        true
                    )
                        ? $attrs['output_type']
                        : 'svg';
                } else {
                    $output_type = 'svg';
                }
                $container_type = $output_type === 'canvas' ? 'canvas' : 'div';
                // setup the barcode params in the dom
                $parsed = '<'
                          . $container_type
                          . ' class="ee-barcode"><span class="ee-barcode-width" style="display:none;">'
                          . $width
                          . '</span>';
                $parsed .= '<span class="ee-barcode-reg_url_link" style="display:none;">' . $reg_code . '</span>';
                $parsed .= '<span class="ee-barcode-color" style="display:none;">' . $color . '</span>';
                $parsed .= '<span class="ee-barcode-type" style="display:none;">' . $type . '</span>';
                $parsed .= '<span class="ee-barcode-height" style="display:none;">' . $height . '</span>';
                $parsed .= '<span class="ee-barcode-bgcolor" style="display:none;">' . $bgcolor . '</span>';
                $parsed .= '<span class="ee-barcode-fsize" style="display:none;">' . $fsize . '</span>';
                $parsed .= '<span class="ee-barcode-output-type" style="display:none;">' . $output_type . '</span>';
                $parsed .= '</' . $container_type . '>';
            }
        } elseif ($shortcode_library instanceof EE_Attendee_Shortcodes) {
            if ($shortcode === '[TICKET_URL]') {
                // $extra = ! empty($extra_data) && $extra_data['data'] instanceof EE_Messages_Addressee
                //     ? $extra_data['data'] : null;
                // incoming object should only be a registration object.
                $registration = ! $data instanceof EE_Registration ? null : $data;
                if (empty($registration)) {
                    return $parsed;
                }
                $parsed = EED_Ticketing::getTicketUrl($registration);
            }
        } elseif ($shortcode_library instanceof EE_Recipient_Details_Shortcodes) {
            if ($shortcode === '[RECIPIENT_TICKET_URL]') {
                $recipient = $shortcode_library->get_recipient();
                if (! $recipient instanceof EE_Messages_Addressee) {
                    return '';
                }
                $registration = $recipient->reg_obj;
                if (! $registration instanceof EE_Registration) {
                    return $parsed;
                }
                $parsed = EED_Ticketing::getTicketUrl($registration);
            }
        } elseif ($shortcode_library instanceof EE_Transaction_Shortcodes) {
            if (! $data->txn instanceof EE_Transaction) {
                return $parsed; // get out because don't have what we need!
            }
            if ($shortcode === '[TXN_TICKETS_URL]') {
                $transaction = $data->txn;
                $registration = $data->reg_obj instanceof EE_Registration
                    ? $data->reg_obj
                    : $transaction->primary_registration();
                $registration_url_link = $registration instanceof EE_Registration
                    ? EED_Ticketing::getTransactionTicketsUrl($registration)
                    : 'http://dummyurlforpreview.com';
                return $registration_url_link;
            }
            if ($shortcode === '[TXN_TICKETS_APPROVED_URL]') {
                $transaction = $data->txn;
                $registration = $data->reg_obj instanceof EE_Registration
                    ? $data->reg_obj
                    : $transaction->primary_registration();
                $registration_url_link = $registration instanceof EE_Registration
                    ? EED_Ticketing::getTransactionTicketsUrl($registration, true)
                    : 'http://dummyurlforpreview.com';
                return $registration_url_link;
            }
        }
        return $parsed;
    }


    /**
     * Callback for FHEE__EE_Messages_Validator__get_specific_shortcode_excludes.  Taking care of excluding
     * certain new shortcodes from any non HTML messenger template.
     *
     * @param                       $shortcode_excludes
     * @param                       $context
     * @param EE_Messages_Validator $validator
     * @return mixed
     */
    public function excludeNewShortcodes($shortcode_excludes, $context, EE_Messages_Validator $validator)
    {
        // we exclude ALL qrcode and barcode shortcodes from non ticketing message types.
        if (! $validator instanceof EE_Messages_Html_Ticketing_Validator) {
            $fields = array_keys($validator->get_validators());
            foreach ($fields as $field) {
                $shortcode_excludes[ $field ][] = '[QRCODE_*]';
                $shortcode_excludes[ $field ][] = '[BARCODE_*]';
            }
        }
        return $shortcode_excludes;
    }
}
