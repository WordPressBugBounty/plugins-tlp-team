<?php
/**
 * Upgrade routines.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Admin;

use RT\Team\Helpers\Fns;
use RT\Team\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * One-off maintenance that has to run after the plugin version changes.
 */
class Upgrade {
	use SingletonTrait;

	/**
	 * Option holding the plugin version the generated CSS was last built with.
	 *
	 * @var string
	 */
	const CSS_VERSION_OPTION = 'tlp_team_sc_css_version';

	/**
	 * Class init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'maybe_rebuild_shortcode_css' ] );
	}

	/**
	 * Rebuild every shortcode's generated CSS once per plugin version.
	 *
	 * uploads/tlp-team/team-sc.css is only ever written when a shortcode is saved
	 * (ShortcodeMeta::save_team_sc_meta_data), so any change to templates/sc-css.php
	 * would otherwise never reach a site that already has shortcodes — its file
	 * would keep serving rules built by the previous version.
	 *
	 * This does NOT touch any setting. Every value lives in post meta on the
	 * `team-sc` post; the CSS file is only a derived cache, so re-deriving it from
	 * the same meta is lossless.
	 *
	 * @return void
	 */
	public function maybe_rebuild_shortcode_css() {
		if ( get_option( self::CSS_VERSION_OPTION ) === TLP_TEAM_VERSION ) {
			return;
		}

		$sc_ids = get_posts(
			[
				'post_type'              => rttlp_team()->shortCodePT,
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			]
		);

		foreach ( $sc_ids as $sc_id ) {
			Fns::generatorShortcodeCss( $sc_id );
		}

		update_option( self::CSS_VERSION_OPTION, TLP_TEAM_VERSION );

		/**
		 * Fires after every shortcode's generated CSS has been rebuilt.
		 *
		 * @param array $sc_ids Shortcode post IDs that were rebuilt.
		 */
		do_action( 'rttm_shortcode_css_rebuilt', $sc_ids );
	}
}
