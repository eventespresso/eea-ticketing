<?php

use EventEspresso\core\services\loaders\LoaderInterface;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\services\loaders\Loader;
use EventEspresso\core\services\loaders\LoaderFactory;

// define the plugin directory path and URL
define('EE_TICKETING_PATH', plugin_dir_path(__FILE__));
define('EE_TICKETING_URL', plugin_dir_url(__FILE__));



/**
 * Class  EE_Ticketing
 *
 * @package            EE Ticketing
 * @subpackage         core
 * @author             Darren Ethier
 * @since              1.0.0
 */
class EE_Ticketing extends EE_Addon
{

    /**
     * @var LoaderInterface $loader
     */
    private static $loader;


    /**
     * EE_Ticketing constructor.
     *
     * @param LoaderInterface|null $loader
     */
    public function __construct(LoaderInterface $loader = null)
    {
        EE_Ticketing::$loader = $loader;
        parent::__construct();
    }


    /**
     * @throws EE_Error
     */
    public static function register_addon()
    {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Ticketing',
            array(
                'version'          => EE_TICKETING_VERSION,
                'min_core_version' => '4.9.26.rc.000',
                'main_file_path'   => EE_TICKETING_PLUGIN_FILE,
                'autoloader_paths' => array(
                    'EE_Ticketing' => EE_TICKETING_PATH . 'EE_Ticketing.class.php',
                ),
                'module_paths'     => array(
                    EE_TICKETING_PATH . 'EED_Ticketing.module.php',
                    EE_TICKETING_PATH . 'EED_Ticketing_WPUser_Integration.module.php'
                ),
                // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options'      => array(
                    'pue_plugin_slug' => 'eea-ticketing',
                    'checkPeriod'     => '24',
                    'use_wp_update'   => false,
                ),
                'message_types'    => array(
                    'ticketing'     => self::get_ticket_message_type_arguments(),
                    'ticket_notice' => self::get_ticket_notice_message_type_arguments(),
                ),
                'namespace' => array(
                    'FQNS' => 'EventEspresso\Ticketing',
                    'DIR' => __DIR__,
                ),
            )
        );
    }



    /**
     * This is a method third party devs can use to grab the loader set on this class.
     *
     * @return LoaderInterface
     * @throws InvalidArgumentException
     * @throws InvalidInterfaceException
     * @throws InvalidDataTypeException
     */
    public static function loader()
    {
        if (! EE_Ticketing::$loader instanceof LoaderInterface) {
            EE_Ticketing::$loader = LoaderFactory::getLoader();
        }
        return EE_Ticketing::$loader;
    }



    /**
     * a safe space for addons to add additional logic like setting hooks
     * that will run immediately after addon registration
     * making this a great place for code that needs to be "omnipresent"
     */
    public function after_registration()
    {
        EE_Ticketing::loader()->getShared(
            'EventEspresso\Ticketing\domain\services\messages\RegisterCustomShortcodes'
        );
    }


    /**
     * Return the arguments for registering the ticket message type.
     * @return array
     */
    protected static function get_ticket_message_type_arguments()
    {
        $setup_args = array(
            'mtfilename'                  => 'EE_Ticketing_message_type.class.php',
            'autoloadpaths'               => array(
                EE_TICKETING_PATH . 'core/messages/',
            ),
            'messengers_to_activate_with' => array('html'),
            'messengers_to_validate_with' => array('html'),
            'messengers_supporting_default_template_pack_with' => array('html'),
            'base_path_for_default_templates' => EE_TICKETING_PATH . 'core/messages/templates/',
            'base_path_for_default_variation' => EE_TICKETING_PATH . 'core/messages/templates/',
            'base_url_for_default_variation' => EE_TICKETING_URL . 'core/messages/templates/',
            'force_activation'            => true,
        );
        return $setup_args;
    }


    /**
     * Return ticket notice message type arguments
     * @return array
     */
    protected static function get_ticket_notice_message_type_arguments()
    {
        $setup_args = array(
            'mtfilename'                  => 'EE_Ticket_Notice_message_type.class.php',
            'autoloadpaths'               => array(
                EE_TICKETING_PATH . 'core/messages/',
            ),
            'messengers_to_activate_with' => array('email'),
            'messengers_to_validate_with' => array('email'),
            'messengers_supporting_default_template_pack_with' => array('email'),
            'base_path_for_default_templates' => EE_TICKETING_PATH . 'core/messages/templates/',
            'base_path_for_default_variation' => EE_TICKETING_PATH . 'core/messages/templates/',
            'base_url_for_default_variation' => EE_TICKETING_URL . 'core/messages/templates/',
            'force_activation'            => true,
        );
        return $setup_args;
    }
}
