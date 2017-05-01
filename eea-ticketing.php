<?php
/*
  Plugin Name: Event Espresso - Ticketing (EE 4+)
  Plugin URI: http://www.eventespresso.com
  Description: This adds the ticketing message type to Event Espresso 4 which includes the ability to customize tickets that users will receive for the events.  Using the messages system you can create templates for various styles of tickets and assign them to whatever event you want to use that template for.  Also includes multiple bar code types for handling check-in scans at the door.  Users can either print out their tickets or display them on their mobile devices.
  Version: 1.0.5.p
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2014 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			http://www.eventespresso.com
 * @ version	 	EE4
 *
 * ------------------------------------------------------------------------
 */
define( 'EE_TICKETING_VERSION', '1.0.5.p' );
define( 'EE_TICKETING_PLUGIN_FILE',  __FILE__ );
function load_ee_core_ticketing() {
if ( class_exists( 'EE_Addon' )) {
	// new_addon version
	require_once ( plugin_dir_path( __FILE__ ) . 'EE_Ticketing.class.php' );
	EE_Ticketing::register_addon();
}
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_ticketing' );

// End of file eea-ticketing.php
// Location: wp-content/plugins/espresso-new-addon/eea-ticketing.php
