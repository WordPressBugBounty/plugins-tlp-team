<?php
/**
 * Special Layout Ajax Class.
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
 * Special Layout Ajax Class.
 */
class SpecialLayout {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_rtGetSpecialLayoutData', [ $this, 'response' ] );
		add_action( 'wp_ajax_nopriv_rtGetSpecialLayoutData', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {

		$memberId = ! empty( $_REQUEST['memberId'] ) ? absint( $_REQUEST['memberId'] ) : null;
		$toggleId = ! empty( $_REQUEST['toggleId'] ) ? absint( $_REQUEST['toggleId'] ) : null;
		$scID     = ! empty( $_REQUEST['scID'] ) ? absint( $_REQUEST['scID'] ) : null;
		$html     = $toggle_image_src = null;
		$error    = true;

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [
				'data'  => __('Security Issue','tlp-team'),
				'error' => $error,
			] );

		}
        $post = get_post( $memberId );
		if ( $memberId && $post && $post->post_status == 'publish' ) {
			$name        = get_the_title( $memberId );
			$designation = wp_strip_all_tags(
				get_the_term_list(
					$memberId,
					rttlp_team()->taxonomies['designation'],
					null,
					', '
				)
			);
			$short_bio   = get_post_meta( $memberId, 'short_bio', true );
			$scMeta      = $scID ? get_post_meta( $scID ) : [];

			/*
			 * Read straight from the shortcode, the way the grid does. Both calls below used to
			 * be made with the member ID alone, so the spotlight panel ignored three shortcode
			 * settings the thumbnails beside it honour: Default preview image (a member with no
			 * photo fell through to getFeatureImageHtml()'s built-in demo.jpg placeholder),
			 * Image size, and the custom size that goes with it -- the panel always served
			 * 'medium'. Pro's Elementor twin (AjaxController) has always passed all three.
			 */
			$fImgSize      = ! empty( $scMeta['ttp_image_size'][0] ) ? $scMeta['ttp_image_size'][0] : 'medium';
			$defaultImgId  = ! empty( $scMeta['default_preview_image'][0] ) ? absint( $scMeta['default_preview_image'][0] ) : null;
			$customImgSize = ! empty( $scMeta['ttp_custom_image_size'][0] ) ? maybe_unserialize( $scMeta['ttp_custom_image_size'][0] ) : [];

			$imgHtml     = Fns::getFeatureImageHtml( $memberId, $fImgSize, $defaultImgId, $customImgSize );
			$imgHtml     = apply_filters( 'rttm_loop_img_html', $imgHtml, $memberId, $scMeta );

			if ( $toggleId ) {
				$toggle_image_src = Fns::getFeatureImageSrc( $toggleId, $fImgSize, $defaultImgId, $customImgSize );
			}

            $settings     = get_option( rttlp_team()->options['settings'] );
            $resume_btn_text = isset( $settings['resume_btn_text'] ) ? $settings['resume_btn_text'] : "Resume";
            $hire_btn_text = isset( $settings['hire_me_text'] ) ? $settings['hire_me_text'] : "Hire Me";
            $resume_url      = get_post_meta( $memberId, 'ttp_my_resume', true );
            $hire_me_url     = get_post_meta( $memberId, 'ttp_hire_me', true );

			$read_more_btn_text = ! empty( $scMeta['ttp_read_more_btn_text'][0] )
				? $scMeta['ttp_read_more_btn_text'][0]
				: esc_html__( 'Read More', 'tlp-team' );
			$target             = ! empty( $scMeta['ttp_link_target'][0] ) ? $scMeta['ttp_link_target'][0] : '_self';

			$fields         = get_post_meta( $scID, 'ttp_selected_field' );
			$htmlName       = $htmlDesignation = $htmlDepartment = $htmlShortBio = $htmlCInfo = $anchorClass = null;
			$email          = get_post_meta( $memberId, 'email', true );
			$web_url        = get_post_meta( $memberId, 'web_url', true );
			$telephone      = get_post_meta( $memberId, 'telephone', true );
			$mobile         = get_post_meta( $memberId, 'mobile', true );
			$fax            = get_post_meta( $memberId, 'fax', true );
			$location       = get_post_meta( $memberId, 'location', true );
			$social         = get_post_meta( $memberId, 'social', true );
			$sLink          = $social ? $social : [];
			$tax_department = wp_strip_all_tags(
				get_the_term_list(
					$memberId,
					rttlp_team()->taxonomies['department'],
					null,
					', '
				)
			);
			$link           = get_post_meta( $scID, 'ttp_detail_page_link', true );
			$linkType       = get_post_meta( $scID, 'ttp_detail_page_link_type', true );
			$linkType       = ! empty( $linkType ) ? $linkType : 'popup';
			$pLink          = get_permalink( $memberId );

			if ( $link && $linkType == 'popup' ) {
				$popupType = get_post_meta( $scID, 'ttp_popup_type', true );
				$popupType = ! empty( $popupType ) ? $popupType : 'single';
				if ( $popupType == 'single' ) {
					$anchorClass .= ' ttp-single-md-popup';
				} elseif ( $popupType == 'multiple' ) {
					$anchorClass .= ' ttp-multi-popup';
				} elseif ( $popupType == 'smart' ) {
					$anchorClass .= ' ttp-smart-popup';
				}
			}

			if ( $name && in_array( 'name', $fields ) ) {
				if ( $link ) {
					$htmlName = '<h3><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $memberId ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $name ) . '</a></h3>';
				} else {
					$htmlName = '<h3>' . esc_html( $name ) . '</h3>';
				}
			}

			if ( $designation && in_array( 'designation', $fields ) ) {
				if ( $link ) {
					$htmlDesignation = '<div class="tlp-position"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $memberId ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $designation ) . '</a></div>';
				} else {
					$htmlDesignation = '<div class="tlp-position">' . esc_html( $designation ) . '</div>';
				}
			}

			if ( $tax_department && in_array( 'tax_department', $fields ) ) {
				$htmlDepartment = '<div class="tlp-department">' . esc_html( $tax_department ) . '</div>';
			}

			if ( $short_bio && in_array( 'short_bio', $fields ) ) {
				$htmlShortBio = '<div class="special-selected-short-bio short-bio"><p>' . Fns::htmlKses( $short_bio, 'basic' ) . '</p></div>';
			}

			// Contact rows + social icons — the same builders the Elementor path uses,
			// so one stylesheet can serve both.
			$htmlCInfo .= Fns::get_formatted_contact_info(
				[
					'email'     => $email,
					'telephone' => $telephone,
					'mobile'    => $mobile,
					'fax'       => $fax,
					'location'  => $location,
					'web_url'   => $web_url,
				],
				$fields
			);
			$htmlCInfo .= Fns::get_formatted_social_link( $sLink, $fields );

			// Buttons use the shared helpers so the button style controls hit them.
			// Order for this layout is Resume, Hire Me, then Read More last.
			$readmore_btn = Fns::get_formatted_readmore_text( $fields, $read_more_btn_text, $anchorClass, $memberId, $target, $name, $pLink );
			$resume_btn   = Fns::get_formatted_resume( $fields, $resume_url, $resume_btn_text );
			$hire_me_btn  = Fns::get_formatted_hire_me( $fields, $hire_me_url, $hire_btn_text );

			if ( $readmore_btn || $resume_btn || $hire_me_btn ) {
				$htmlCInfo .= '<div class="rt-team-container"><div class="readmore-btn">';
				$htmlCInfo .= $resume_btn . $hire_me_btn . $readmore_btn;
				$htmlCInfo .= '</div></div>';
			}

			$html .= "<div class='special-selected-top-wrap'><div class='rt-col-xs-6 img'>" . $imgHtml . '</div>';
			$html .= '<div class="rt-col-xs-6 ttp-label"> <div class="ttp-label-inner">' . $htmlName . $htmlDesignation . $htmlDepartment . '</div></div></div>';
			$html .= '<div class="rt-col-sm-12">' . $htmlShortBio . $htmlCInfo . '</div>';
			$error = false;
		}

		wp_send_json(
			[
				'data'             => wp_kses_post( $html ),
				'toggle_image_src' => $toggle_image_src,
				'error'            => $error,
				'field'            => $fields,
			]
		);

		die();
	}
}
