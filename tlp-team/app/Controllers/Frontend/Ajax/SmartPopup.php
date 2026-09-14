<?php
/**
 * Smart Popup Ajax Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Frontend\Ajax;

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Smart Popup Ajax Class.
 */
class SmartPopup {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_tlp_team_smart_popup', [ $this, 'response' ] );
		add_action( 'wp_ajax_nopriv_tlp_team_smart_popup', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {
		$html    = null;
		$success = false;
		$error   = true;

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error(
				[
					'data'  => __( 'Security Issue', 'tlp-team' ),
					'error' => $error,
				]
			);
		}

		$member_post = get_post( absint( $_REQUEST['id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $member_post || $member_post->post_type !== rttlp_team()->post_type || 'publish' !== $member_post->post_status ) {
			wp_send_json_error( [ 'error' => __( 'Unauthorized or member not found', 'tlp-team' ) ], 403 );
		}

		global $post;
		$post = $member_post;
		setup_postdata( $post );

		$settings = get_option( rttlp_team()->options['settings'] );
		$fields   = isset( $settings['detail_page_fields'] ) ? $settings['detail_page_fields'] : [ 'name', 'designation', 'short_bio', 'email', 'web_url', 'telephone', 'mobile', 'fax', 'location', 'social' ];

		// Built by Fns so the Elementor handler (pro AjaxController::smartPopup) cannot
		// drift from this one.
		$html    = Fns::smartPopupMarkup( $post->ID, $fields, $settings, false );
		$success = true;
		$error   = false;

		wp_reset_postdata();

		wp_send_json(
			[
				'data'    => $html,
				'error'   => $error,
				'success' => $success,
			]
		);
	}
}
