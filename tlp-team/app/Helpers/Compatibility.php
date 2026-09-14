<?php
/**
 * Team Pro version compatibility gate.
 *
 * @package RT/Team
 */

namespace RT\Team\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Team Pro version compatibility gate.
 *
 * Team and Team Pro are released in lock-step, so a Pro build older than
 * TLP_TEAM_MIN_PRO_VERSION must not be allowed to hook into this release. This
 * class answers "is the active Team Pro new enough?" and stops the two entry
 * points Pro boots from when the answer is no:
 *
 * 1. `rttm_loaded` — withheld by RttlpTeam::initialize(), which keeps Pro's
 *    main half (Rttmp, HookFilter, ElementorFilters, AjaxController, Licensing)
 *    from ever loading.
 * 2. Pro's own `plugins_loaded` callback, which bootstraps the Elementor single
 *    page builder. Older Pro releases do not self-guard, so block_pro_hooks()
 *    unhooks it from the outside.
 *
 * Nothing here deactivates the Pro plugin — it stays active and simply does
 * nothing, so updating Pro restores it with no further action from the user.
 */
class Compatibility {

	/**
	 * Cached basename of the active Team Pro plugin.
	 *
	 * `null` = not resolved yet, `''` = Team Pro is not active.
	 *
	 * @var string|null
	 */
	private static $pro_basename = null;

	/**
	 * Cached Team Pro version.
	 *
	 * `null` = not resolved yet, `''` = unknown / Pro not active.
	 *
	 * @var string|null
	 */
	private static $pro_version = null;

	/**
	 * Oldest Team Pro release this version of Team supports.
	 *
	 * @return string
	 */
	public static function min_pro_version() {
		return defined( 'TLP_TEAM_MIN_PRO_VERSION' ) ? TLP_TEAM_MIN_PRO_VERSION : '0';
	}

	/**
	 * Basename of the active Team Pro plugin.
	 *
	 * Matched on the entry file name rather than the full `folder/file.php`
	 * path so a renamed plugin folder is still detected.
	 *
	 * @return string Empty string when Team Pro is not active.
	 */
	public static function pro_basename() {
		if ( null !== self::$pro_basename ) {
			return self::$pro_basename;
		}

		self::$pro_basename = '';

		$active = (array) apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) );

		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
		}

		foreach ( $active as $plugin ) {
			if ( 'tlp-team-pro.php' === basename( $plugin ) ) {
				self::$pro_basename = $plugin;
				break;
			}
		}

		return self::$pro_basename;
	}

	/**
	 * Absolute, normalised directory of the active Team Pro plugin.
	 *
	 * @return string Empty string when Team Pro is not active.
	 */
	public static function pro_plugin_dir() {
		$basename = self::pro_basename();

		if ( ! $basename ) {
			return '';
		}

		return trailingslashit( wp_normalize_path( WP_PLUGIN_DIR . '/' . dirname( $basename ) ) );
	}

	/**
	 * Is Team Pro active?
	 *
	 * @return boolean
	 */
	public static function is_pro_active() {
		return (bool) self::pro_basename();
	}

	/**
	 * Version of the active Team Pro plugin.
	 *
	 * Read from the plugin header rather than RTTMP_VERSION so that every Pro
	 * build reports correctly, including ones released before that constant
	 * existed and regardless of plugin load order.
	 *
	 * @return string Empty string when Team Pro is not active or has no header.
	 */
	public static function pro_version() {
		if ( null !== self::$pro_version ) {
			return self::$pro_version;
		}

		self::$pro_version = '';

		$basename = self::pro_basename();

		if ( ! $basename ) {
			return self::$pro_version;
		}

		$file = WP_PLUGIN_DIR . '/' . $basename;

		if ( is_readable( $file ) ) {
			// No context: this runs at `plugins_loaded` -1 and the Version header
			// is requested explicitly, so there is no reason to fire the
			// `extra_plugin_headers` filter this early in the request.
			$data              = get_file_data( $file, [ 'Version' => 'Version' ] );
			self::$pro_version = ! empty( $data['Version'] ) ? $data['Version'] : '';
		}

		if ( ! self::$pro_version && defined( 'RTTMP_VERSION' ) ) {
			self::$pro_version = RTTMP_VERSION;
		}

		return self::$pro_version;
	}

	/**
	 * Is the active Team Pro new enough to boot against this release?
	 *
	 * Fails open: when Pro is absent, or its version cannot be read at all,
	 * nothing is blocked. Only a Pro that positively reports a version below
	 * the minimum is refused.
	 *
	 * @return boolean
	 */
	public static function is_pro_compatible() {
		if ( ! self::is_pro_active() ) {
			return true;
		}

		$version = self::pro_version();

		if ( ! $version ) {
			return true;
		}

		return version_compare( $version, self::min_pro_version(), '>=' );
	}

	/**
	 * Unhook an incompatible Team Pro from `plugins_loaded`.
	 *
	 * Withholding `rttm_loaded` stops Pro's main half, but Pro bootstraps its
	 * Elementor single page builder from its own `plugins_loaded` callback,
	 * which releases older than TLP_TEAM_MIN_PRO_VERSION do not guard. This
	 * runs at `plugins_loaded` priority -1 — before Pro's own priority 15 — and
	 * drops every callback whose code lives inside the Pro plugin directory.
	 *
	 * @return void
	 */
	public static function block_pro_hooks() {
		if ( self::is_pro_compatible() ) {
			return;
		}

		$pro_dir = self::pro_plugin_dir();

		if ( ! $pro_dir ) {
			return;
		}

		global $wp_filter;

		if ( empty( $wp_filter['plugins_loaded'] ) || empty( $wp_filter['plugins_loaded']->callbacks ) ) {
			return;
		}

		foreach ( $wp_filter['plugins_loaded']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( self::callback_lives_in( $callback['function'], $pro_dir ) ) {
					// remove_action() keeps WP_Hook's iteration state consistent; a
					// direct unset() on ->callbacks during do_action() would not.
					remove_action( 'plugins_loaded', $callback['function'], $priority );
				}
			}
		}
	}

	/**
	 * Is a hook callback defined inside the given directory?
	 *
	 * @param callable $callback Hook callback.
	 * @param string   $dir      Absolute, normalised, trailing-slashed directory.
	 *
	 * @return boolean
	 */
	private static function callback_lives_in( $callback, $dir ) {
		try {
			if ( is_array( $callback ) && 2 === count( $callback ) ) {
				$reflection = new \ReflectionMethod( $callback[0], $callback[1] );
			} elseif ( is_object( $callback ) && ! $callback instanceof \Closure ) {
				$reflection = new \ReflectionMethod( $callback, '__invoke' );
			} elseif ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
				$reflection = new \ReflectionMethod( $callback );
			} else {
				$reflection = new \ReflectionFunction( $callback );
			}
		} catch ( \ReflectionException $e ) {
			return false;
		}

		$file = $reflection->getFileName();

		return $file && 0 === strpos( wp_normalize_path( $file ), $dir );
	}
}
