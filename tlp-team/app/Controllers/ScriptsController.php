<?php
/**
 * Scripts Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Scripts Class.
 */
class ScriptsController {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Scripts.
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		$this->get_assets();

		if ( empty( $this->styles ) ) {
			return;
		}

		if ( empty( $this->scripts ) ) {
			return;
		}

		$version    = rttlp_team()->version;
		$upload_dir = wp_upload_dir();
		$css_file   = $upload_dir['basedir'] . '/tlp-team/team-sc.css';

		foreach ( $this->styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $version );
		}

		foreach ( $this->scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $version, $script['footer'] );
		}

		if ( file_exists( $css_file ) ) {
			$version = filemtime( $css_file );
			wp_register_style( 'rt-team-sc', set_url_scheme( $upload_dir['baseurl'] ) . '/tlp-team/team-sc.css', [ 'rt-team-css' ], $version );
		}

		wp_localize_script(
			'tlp-team-admin-js',
			'rttm',
			[
				'is_pro' => rttlp_team()->has_pro(),
			]
		);

		add_action( 'wp_enqueue_scripts', [ $this, 'tlp_script' ] );
		add_filter( 'style_loader_tag', [ $this, 'maybe_skip_font_awesome' ], 10, 2 );
	}

	/**
	 * Frontend scripts scripts.
	 *
	 * @return void
	 */
	public function tlp_script() {
		$settings = get_option( rttlp_team()->options['settings'] );
		$settings = isset( $settings['tlp_team_block_type'] ) ? esc_html( $settings['tlp_team_block_type'] ) : 'default';

		if ( in_array( $settings, [ 'default', 'shortcode' ], true ) || is_singular( 'team' ) ) {
			wp_enqueue_style( 'rt-team-css' );
			wp_enqueue_style( 'rt-team-sc' );
			wp_enqueue_script( 'rttm-iso-filter' );
		}

		if ( did_action( 'elementor/loaded' ) && in_array( $settings, [ 'default', 'elementor' ], true ) && ! is_singular( 'team' ) ) {
			wp_enqueue_style( 'tlp-el-team-css' );
			wp_enqueue_script( 'rttm-iso-filter' );
		}
	}

	/**
	 * Handle the bundled Font Awesome is registered under.
	 */
	const FA_HANDLE = 'tlp-fontawsome';

	/**
	 * Lowest Font Awesome major release that can render every icon this plugin emits.
	 *
	 * The templates use the `fab` / `fas` / `far` family prefixes, which do not exist
	 * before Font Awesome 5, and two brand glyphs that only shipped later still:
	 * `fa-x-twitter` (6.4) and `fa-bluesky` (6.6).
	 *
	 * So the floor is 6.6, not merely "version 6": a theme on 6.0-6.5 passes a
	 * major-only check and still renders those two as blank boxes.
	 *
	 * A site that does not use X or Bluesky can lower it and save the extra request:
	 *
	 *     add_filter( 'tlp_team_font_awesome_min_version', fn() => '5.0' );
	 *
	 * @return string
	 */
	private function font_awesome_min_version() {
		return (string) apply_filters( 'tlp_team_font_awesome_min_version', '6.6' );
	}

	/**
	 * Does this handle/src pair look like a Font Awesome stylesheet?
	 *
	 * @param string $handle Style handle.
	 * @param string $src    Style source URL.
	 *
	 * @return bool
	 */
	private function looks_like_font_awesome( $handle, $src ) {
		return (bool) preg_match( '#font[-_]?awesome#i', $handle . ' ' . (string) $src );
	}

	/**
	 * Does this stylesheet provide the WHOLE icon set, rather than one family?
	 *
	 * Font Awesome ships per-family files — `solid.min.css`, `brands.min.css`,
	 * `regular.min.css` — and Elementor's icon library registers exactly those, one
	 * handle per family. They carry a full version number but define only their own
	 * glyphs, and not even the `@font-face` base, so deferring to one leaves every
	 * icon on the page blank. Only a combined build may suppress the bundled copy.
	 *
	 * The list is an allowlist on purpose: an unrecognised filename is treated as
	 * partial, so an unusual bundle costs a duplicate request rather than breaking
	 * icons. `tlp_team_load_font_awesome` is the override for that case.
	 *
	 * @param string $src Style source URL.
	 *
	 * @return bool
	 */
	private function is_complete_font_awesome( $src ) {
		$file = strtolower( basename( strtok( (string) $src, '?' ) ) );
		$file = preg_replace( '#\.css$#', '', $file );
		$file = preg_replace( '#\.min$#', '', $file );

		return in_array( $file, [ 'all', 'fontawesome', 'font-awesome' ], true );
	}

	/**
	 * Best-effort local path for an asset URL, so its version banner can be read.
	 *
	 * @param string $src Style source URL.
	 *
	 * @return string Empty string when the URL is remote or cannot be mapped.
	 */
	private function local_path_for_src( $src ) {
		$src = strtok( (string) $src, '?' );

		if ( ! $src ) {
			return '';
		}

		if ( 0 === strpos( $src, '//' ) ) {
			$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
		}

		$content_url = set_url_scheme( content_url() );
		$src         = set_url_scheme( $src );

		if ( 0 !== strpos( $src, $content_url ) ) {
			return '';
		}

		$path = WP_CONTENT_DIR . substr( $src, strlen( $content_url ) );
		$path = wp_normalize_path( $path );

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * Work out which Font Awesome major a registered stylesheet provides.
	 *
	 * The registered version string is not trustworthy on its own — Elementor, for
	 * instance, registers its `font-awesome` handle as `4.7.0` while shipping a 5.x
	 * build elsewhere — so the file's own banner is preferred when it can be read,
	 * then the version string, then a version number embedded in the path.
	 *
	 * @param object $style Registered style object from WP_Styles.
	 *
	 * @return string `major.minor`, or an empty string when it cannot be determined.
	 */
	private function font_awesome_version( $style ) {
		$src  = isset( $style->src ) ? (string) $style->src : '';
		$path = $this->local_path_for_src( $src );

		if ( $path ) {
			$key    = 'tlp_team_fa_ver_' . md5( $path . '|' . filemtime( $path ) );
			$cached = get_transient( $key );

			if ( false !== $cached ) {
				return (string) $cached;
			}

			$ver    = '';
			$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

			if ( $handle ) {
				$head = fread( $handle, 1024 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

				if ( preg_match( '#Font\s*Awesome[^0-9]{0,40}?(\d+\.\d+)#i', (string) $head, $m ) ) {
					$ver = $m[1];
				}
			}

			set_transient( $key, $ver, DAY_IN_SECONDS );

			if ( $ver ) {
				return $ver;
			}
		}

		if ( ! empty( $style->ver ) && preg_match( '#^(\d+(?:\.\d+)?)#', (string) $style->ver, $m ) ) {
			return $m[1];
		}

		if ( preg_match( '#font[-_]?awesome[^0-9]{0,20}(\d+\.\d+)#i', $src, $m ) ) {
			return $m[1];
		}

		return '';
	}

	/**
	 * Find a Font Awesome already on its way to the page from somewhere else.
	 *
	 * Only styles that are actually enqueued or already printed count — a theme that
	 * merely registers Font Awesome without using it must not suppress ours. Resolved
	 * once per request; the answer is most accurate at print time, which is when the
	 * filter below asks for it, because by then everything in the head is done.
	 *
	 * @return array{handle:string,version:string}
	 */
	public function detect_foreign_font_awesome() {
		static $found = null;

		if ( null !== $found ) {
			return $found;
		}

		$found     = [
			'handle'  => '',
			'version' => '',
		];
		$wp_styles = wp_styles();

		if ( ! $wp_styles instanceof \WP_Styles ) {
			return $found;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( self::FA_HANDLE === $handle ) {
				continue;
			}

			if ( ! $this->looks_like_font_awesome( $handle, isset( $style->src ) ? $style->src : '' ) ) {
				continue;
			}

			if ( ! wp_style_is( $handle, 'enqueued' ) && ! wp_style_is( $handle, 'done' ) ) {
				continue;
			}

			if ( ! $this->is_complete_font_awesome( isset( $style->src ) ? $style->src : '' ) ) {
				continue;
			}

			$version = $this->font_awesome_version( $style );

			// Keep the newest one on the page: a site may carry both an old theme copy
			// and a modern one, and only the newest decides whether ours is needed.
			if ( $version && ( ! $found['version'] || version_compare( $version, $found['version'], '>' ) ) ) {
				$found = [
					'handle'  => $handle,
					'version' => $version,
				];
			} elseif ( ! $found['handle'] ) {
				// Present but unversioned — remembered so the decision below can still
				// name it, though an unknown version never suppresses our copy.
				$found['handle'] = $handle;
			}
		}

		return $found;
	}

	/**
	 * Should the bundled Font Awesome be printed?
	 *
	 * @return bool
	 */
	public function should_load_font_awesome() {
		$forced = apply_filters( 'tlp_team_load_font_awesome', null );

		if ( is_bool( $forced ) ) {
			return $forced;
		}

		$found = $this->detect_foreign_font_awesome();

		// Nothing else provides it, or what does is too old for this plugin's icons.
		// An undetectable version reads as 0 and therefore also falls back to ours,
		// which is the safe direction: a duplicate request costs a little, missing
		// glyphs are a visible bug.
		return ! $found['handle']
			|| ! $found['version']
			|| version_compare( $found['version'], $this->font_awesome_min_version(), '<' );
	}

	/**
	 * Drop the bundled Font Awesome tag when the page already has a usable one.
	 *
	 * Filtering the printed tag rather than the enqueue keeps every caller unchanged
	 * and defers the decision to the last possible moment.
	 *
	 * @param string $tag    Complete link tag.
	 * @param string $handle Style handle.
	 *
	 * @return string
	 */
	public function maybe_skip_font_awesome( $tag, $handle ) {
		if ( self::FA_HANDLE !== $handle || $this->should_load_font_awesome() ) {
			return $tag;
		}

		$found = $this->detect_foreign_font_awesome();

		return sprintf(
			"<!-- tlp-team: reusing Font Awesome %s from '%s'; bundled copy not loaded. -->\n",
			esc_attr( $found['version'] ),
			esc_attr( $found['handle'] )
		);
	}

	/**
	 * Get all scripts.
	 *
	 * @return void
	 */
	private function get_assets() {
		$this->get_styles()->get_scripts();
	}

	/**
	 * Get styles.
	 *
	 * @return object
	 */
	private function get_styles() {
		$this->styles[] = [
			'handle' => 'tlp-fontawsome',
			'src'    => rttlp_team()->assets_url() . 'vendor/font-awesome/css/all.min.css',
		];

		$this->styles[] = [
			'handle' => 'rt-pagination',
			'src'    => rttlp_team()->assets_url() . 'vendor/pagination/pagination.css',
		];

		$this->styles[] = [
			'handle' => 'tlp-scrollbar',
			'src'    => rttlp_team()->assets_url() . 'vendor/scrollbar/jquery.mCustomScrollbar.min.css',
		];

		$this->styles[] = [
			'handle' => 'tlp-swiper',
			'src'    => rttlp_team()->assets_url() . 'vendor/swiper/swiper.min.css',
		];

		$this->styles[] = [
			'handle' => 'rt-team-css',
			'src'    => rttlp_team()->assets_url() . 'css/tlpteam.css',
		];

		$this->styles[] = [
			'handle' => 'tlp-el-team-css',
			'src'    => rttlp_team()->assets_url() . 'css/tlp-el-team.min.css',
		];

		/**
		 * Admin Styles.
		 */
		if ( is_admin() ) {
			$this->styles[] = [
				'handle' => 'tlp-team-admin-css',
				'src'    => rttlp_team()->assets_url() . 'css/settings.css',
			];

			$this->styles[] = [
				'handle' => 'select2',
				'src'    => rttlp_team()->assets_url() . 'vendor/select2/select2.min.css',
			];
		}

		return $this;
	}

	/**
	 * Get scripts.
	 *
	 * @return object
	 */
	private function get_scripts() {
		$this->scripts[] = [
			'handle' => 'tlp-scrollbar',
			'src'    => rttlp_team()->assets_url() . 'vendor/scrollbar/jquery.mCustomScrollbar.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$default_swiper_handle = 'swiper';
        $default_swiper_path = rttlp_team()->assets_url() . 'vendor/swiper/swiper.min.js';

        if ( defined( 'ELEMENTOR_ASSETS_PATH' ) ) {
            $is_swiper8_enable = get_option( 'elementor_experiment-e_swiper_latest' );

            if ( $is_swiper8_enable == 'active' ) {
                $el_swiper_path = 'lib/swiper/v8/swiper.min.js';
            } else {
                $el_swiper_path = 'lib/swiper/swiper.min.js';
            }

            $elementor_swiper_path = ELEMENTOR_ASSETS_PATH . $el_swiper_path;

            if ( file_exists( $elementor_swiper_path ) ) {
                $default_swiper_path = ELEMENTOR_ASSETS_URL . $el_swiper_path;
            }
        }

        $this->scripts[] = [
            'handle' => $default_swiper_handle,
            'src'    => $default_swiper_path,
            'deps'   => [ 'jquery' ],
            'footer' => false,
        ];

		$this->scripts[] = [
			'handle' => 'tlp-swiper',
			'src'    => $default_swiper_path,
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'tlp-image-load-js',
			'src'    => rttlp_team()->assets_url() . 'vendor/isotope/imagesloaded.pkgd.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'rt-pagination',
			'src'    => rttlp_team()->assets_url() . 'vendor/pagination/pagination.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'tlp-isotope-js',
			'src'    => rttlp_team()->assets_url() . 'vendor/isotope/isotope.pkgd.min.js',
			'deps'   => [ 'jquery', 'tlp-image-load-js' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'rt-tooltip',
			'src'    => rttlp_team()->assets_url() . 'js/rt-tooltip.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'tlp-actual-height-js',
			'src'    => rttlp_team()->assets_url() . 'vendor/actual-height/jquery.actual.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'rt-scrollbox',
			'src'    => rttlp_team()->assets_url() . 'vendor/scrollbar/jquery.scrollbar.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'tlp-team-js',
			'src'    => rttlp_team()->assets_url() . 'js/tlpteam.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'tlp-el-team-js',
			'src'    => rttlp_team()->assets_url() . 'js/tlp-el-team.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		// Isotope filter-bar enhancement (count badges + sliding glider). Standalone so it
		// loads on BOTH the shortcode and Elementor paths; self-guards on .ttp-isotope-buttons.
		$this->scripts[] = [
			'handle' => 'rttm-iso-filter',
			'src'    => rttlp_team()->assets_url() . 'js/iso-filter.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		/**
		 * Admin Scripts.
		 */
		if ( is_admin() ) {
			$this->scripts[] = [
				'handle' => 'ace-code-highlighter-js',
				'src'    => rttlp_team()->assets_url() . 'vendor/ace/ace.js',
				'deps'   => null,
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'ace-mode-js',
				'src'    => rttlp_team()->assets_url() . 'vendor/ace/mode-css.js',
				'deps'   => [ 'ace-code-highlighter-js' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'tlp-admin-taxonomy',
				'src'    => rttlp_team()->assets_url() . 'js/admin-taxonomy.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'select2',
				'src'    => rttlp_team()->assets_url() . 'vendor/select2/select2.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'tlp-team-admin-js',
				'src'    => rttlp_team()->assets_url() . 'js/settings.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'tlp-sc-preview',
				'src'    => rttlp_team()->assets_url() . 'js/sc-preview.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
		}

		return $this;
	}
}
