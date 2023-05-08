<?php
/**
 * GPTEverest
 *
 * @package       GPTE
 * @author        Jannis Thuemmig
 * @license       gplv3-or-later
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   GPTEverest
 * Plugin URI:    https://gpteverest.com
 * Description:   First operational and AI-powered WordPress assistant
 * Version:       1.0.0
 * Author:        Jannis Thuemmig
 * Author URI:    https://thuemmig.com
 * Text Domain:   gpteverest
 * Domain Path:   /languages
 * License:       GPLv3 or later
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with GPTEverest. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

 //todo  remove this
 //define( 'GPTE_DEV', true );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'GPTE_NAME',		'GPTEverest' );

//Plugin slug
define( 'GPTE_SLUG',		'gpteverest' );

// Plugin version
define( 'GPTE_VERSION',		'1.0.0' );

// Plugin Root File
define( 'GPTE_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'GPTE_PLUGIN_BASE',	plugin_basename( GPTE_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'GPTE_PLUGIN_DIR',	plugin_dir_path( GPTE_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'GPTE_PLUGIN_URL',	plugin_dir_url( GPTE_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once GPTE_PLUGIN_DIR . 'core/class-gpteverest.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Jannis Thuemmig
 * @since   1.0.0
 * @return  object|Gpteverest
 */
function GPTE() {
	return Gpteverest::instance();
}

GPTE();
