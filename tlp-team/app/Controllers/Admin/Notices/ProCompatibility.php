<?php
/**
 * Team Pro Compatibility Notice Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Admin\Notices;

use RT\Team\Helpers\Compatibility;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Warns that the active Team Pro is older than this release of Team supports.
 *
 * Shown only while Pro is actually being blocked — once Pro is updated to
 * TLP_TEAM_MIN_PRO_VERSION or newer the notice disappears on its own, and it
 * never appears for a site running a matched pair.
 */
class ProCompatibility {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		/**
		 * Team's own settings screens (Admin\Settings) and Pro's export/import
		 * screen both call remove_all_actions( 'admin_notices' ) from
		 * `in_admin_header` at priority 1000. Registering at 1001 keeps this
		 * notice visible on exactly the screens the user is most likely to be
		 * on when Pro has stopped working.
		 */
		add_action( 'in_admin_header', [ $this, 'register' ], 1001 );
	}

	/**
	 * Register the notice when an incompatible Pro is active.
	 *
	 * @return void
	 */
	public function register() {
		if ( Compatibility::is_pro_compatible() ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'notice' ] );
	}

	/**
	 * Print the notice.
	 *
	 * @return void
	 */
	public function notice() {
		$message = sprintf(
			/* translators: 1: installed Team version, 2: required Team Pro version, 3: installed Team Pro version. */
			esc_html__( 'Team %1$s requires Team Pro %2$s or later. You are running Team Pro %3$s, so all Pro features are turned off. Please update Team Pro for it to work properly.', 'tlp-team' ),
			esc_html( TLP_TEAM_VERSION ),
			esc_html( Compatibility::min_pro_version() ),
			esc_html( Compatibility::pro_version() )
		);

		$notice = sprintf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
			esc_html__( 'Team Pro update required.', 'tlp-team' ),
			$message,
			esc_url( self_admin_url( 'plugins.php' ) ),
			esc_html__( 'Go to Plugins', 'tlp-team' )
		);

		echo wp_kses_post( $notice );
	}
}
