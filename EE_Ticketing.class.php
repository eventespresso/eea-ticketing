<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/**
 * ------------------------------------------------------------------------
 *
 * Class  EE_Ticketing
 *
 * @package			EE Ticketing
 * @subpackage		core
 * @author			Darren Ethier
 * @since		 	1.0.0
 *
 * ------------------------------------------------------------------------
 */
// define the plugin directory path and URL
define( 'EE_TICKETING_PATH', plugin_dir_path( __FILE__ ));
define( 'EE_TICKETING_URL', plugin_dir_url( __FILE__ ));
Class  EE_Ticketing extends EE_Addon {

	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'EE_Ticketing',
			array(
				'version' 					=> EE_TICKETING_VERSION,
				'min_core_version' => '4.3.0',
				'main_file_path' 				=> EE_TICKETING_PLUGIN_FILE,
				'autoloader_paths' => array(
					'EE_Ticketing' 						=> EE_TICKETING_PATH . 'EE_Ticketing.class.php'
				),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'ee-addon-ticketing',
					'plugin_basename' => EE_TICKETING_PLUGIN_FILE,
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
					)
			)
		);

		add_action( 'EE_Brewing_Regular___messages_caf', array( 'EE_Ticketing', 'register_ticketing_message_type' ) );
	}



	public static function register_ticketing_message_type() {
		$setup_args = array(
			'mtfilename' => 'EE_Ticketing_message_type.class.php',
			'autoloadpaths' => array(
				EE_TICKETING_PATH . 'core/messages/'
				),
			'messengers_to_activate_with' => array( 'html' ),
			'messengers_to_validate_with' => array( 'html' )
			);
		EE_Register_Message_Type::register_message_type( 'ticketing', $setup_args );
	}



}
// End of file EE_Ticketing.class.php
// Location: wp-content/plugins/espresso-new-addon/EE_Ticketing.class.php
